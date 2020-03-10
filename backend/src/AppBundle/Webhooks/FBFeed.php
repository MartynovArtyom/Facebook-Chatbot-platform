<?php


namespace AppBundle\Webhooks;


use AppBundle\Entity\CommentDelay;
use AppBundle\Entity\CommentReplies;
use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\Widget;
use AppBundle\Flows\Util\TextVarReplacement;
use AppBundle\Helper\MyFbBotApp;
use Doctrine\ORM\EntityManager;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\GraphNodes\GraphNode;
use pimax\UserProfile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class FBFeed implements FBFeedInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Facebook
     */
    protected $fb;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Page|null
     */
    protected $page = null;

    /**
     * @var UserProfile|Subscribers|null $user
     */
    protected $user;

    /**
     * FBFeed constructor.
     * @param EntityManager $em
     * @param ContainerInterface $container
     * @param array $data
     * @throws FacebookSDKException
     */
    public function __construct(EntityManager $em, ContainerInterface $container, array $data = array())
    {
        $this->em = $em;
        $this->container = $container;
        $this->data = $data;

        $this->fb = new Facebook([
            'app_id' => $container->getParameter('facebook_id'),
            'app_secret' => $container->getParameter('facebook_secret'),
            'default_graph_version' => 'v3.3'
        ]);
    }

    /**
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function handler(){
        if (isset($this->data['entry']) && isset($this->data['entry'][0]) && isset($this->data['entry'][0]['changes']) && !empty($this->data['entry'][0]['changes'])) {
            $page = $this->em->getRepository("AppBundle:Page")->findOneBy(['page_id' => $this->data['entry'][0]['id'], 'status'=>true]);
            if ($page instanceof Page && $page->getUser()->getProduct() instanceof DigistoreProduct && $page->getUser()->getProduct()->getComments() == true) {
                //SET PAGE
                $this->setPage($page);

                foreach ($this->data['entry'][0]['changes'] as $item){
                    if(
                        array_key_exists('field', $item) && $item['field'] == 'feed'
                        && array_key_exists('value', $item) && is_array($item['value'])
                        && array_key_exists('item', $item['value']) && $item['value']['item'] == 'comment'
                        && array_key_exists('comment_id', $item['value']) && !empty($item['value']['comment_id'])
                        && array_key_exists('verb', $item['value']) && $item['value']['verb'] == 'add'
                        && array_key_exists('post_id', $item['value']) && !empty($item['value']['post_id'])
                        && array_key_exists('from', $item['value']) && array_key_exists('id', $item['value']['from']) && $item['value']['from']['id'] != $page->getPageId()
                        && array_key_exists('post', $item['value']) && array_key_exists('is_published', $item['value']['post']) && $item['value']['post']['is_published'] == true
                    ){
                        $widget = $this->em->getRepository("AppBundle:Widget")->findOneBy(['page_id'=>$page->getPageId(), 'postId'=>$item['value']['post_id'], 'status'=>true]);
                        if($widget instanceof Widget){
                            $options = $widget->getOptions();
                            if(array_key_exists('message', $options) && !empty($options['message'])){
                                //CHECK ONLY FIRST LEVEL COMMENT
                                $resultFirstLevel = $this->checkFirstLevelComment($widget, $item, $options);
                                if($resultFirstLevel == true){
                                    return null;
                                }

                                $commentText = array_key_exists('message', $item['value']) ? $item['value']['message'] : null;
                                //Check EXCLUDE KEYWORDS
                                $resultExcludeKeywords = $this->checkExcludeKeywords($options, $commentText);
                                if($resultExcludeKeywords == true){
                                    return null;
                                }
                                //CHECK INCLUDE KEYWORDS
                                $resultIncludeKeywords = $this->checkIncludeKeywords($options, $commentText);
                                if($resultIncludeKeywords == true){
                                    return null;
                                }

                                //CHECK DELAY
                                if(
                                    array_key_exists('delay', $options) && is_array($options['delay'])
                                    && array_key_exists('type', $options['delay']) && in_array($options['delay']['type'], ['minutes', 'seconds'])
                                    && array_key_exists('value', $options['delay']) && !empty($options['delay']['value'])
                                ){
                                    if($options['delay']['type'] == 'minutes'){
                                        $commentDelay = new CommentDelay($widget, $item['value']['comment_id'], $options['delay']['value'], $item['value']['from']['id']);
                                        $this->em->persist($commentDelay);
                                        $this->em->flush();

                                        return null;
                                    }
                                    else{
                                        sleep($options['delay']['value']);
                                        //send message
                                        return $this->sendPrivateReply($widget, $item['value']['comment_id'], $options, $item['value']['from']['id']);
                                    }
                                }
                                else{
                                    //send message
                                    return $this->sendPrivateReply($widget, $item['value']['comment_id'], $options, $item['value']['from']['id']);
                                }
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param Widget $widget
     * @param $commentId
     * @param $options
     * @param $recipient
     * @return null
     * @throws FacebookSDKException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendPrivateReply(Widget $widget, $commentId, $options, $recipient){
        //Try get user
        $subscriber = $this->em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$this->page->getPageId(), 'subscriber_id'=>$recipient]);
        if($subscriber instanceof Subscribers){
            $this->setUser($subscriber);
        }
        else{
            $bot = new MyFbBotApp($this->page->getAccessToken());
            $user = $bot->userProfile($recipient, 'first_name,last_name,profile_pic,locale,timezone,gender');
            if($user instanceof UserProfile){
                $this->setUser($user);
            }
            else{
                $this->setUser(null);
            }
        }

        $textReplace = new TextVarReplacement();
        $message = $textReplace->replaceTextVar($this->em, $options['message'], $this->page, $this->user);
        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $this->fb->post(
                "/".$commentId."/private_replies",
                [
                    'message' => $message
                ],
                $this->page->getAccessToken()
            );
        } catch(FacebookResponseException $e) {
            $fs = new Filesystem();
            $fs->appendToFile('webhook_feed_request.txt', json_encode($e->getMessage())."\n\n");
            return null;
        } catch(FacebookSDKException $e) {
            $fs = new Filesystem();
            $fs->appendToFile('webhook_feed_request.txt', json_encode($e->getMessage())."\n\n");
            return null;
        }
        $resultSend = $response->getGraphNode();

        if($resultSend instanceof GraphNode){
            if(!empty($resultSend->getField('id'))){
                if(array_key_exists('sending_options', $options) && in_array($options['sending_options'], [2,3])){
                    $commentReply = new CommentReplies(
                        $widget,
                        $this->page->getPageId(),
                        $resultSend->getField('id'),
                        !empty($resultSend->getField('user_id')) ? $resultSend->getField('user_id') : null
                    );

                    $this->em->persist($commentReply);
                    $this->em->flush();
                }

                $widget->setShows($widget->getShows()+1);
                $this->em->persist($widget);
                $this->em->flush();
            }
        }

        return null;
    }

    /**
     * @param Widget $widget
     * @param $item
     * @param $options
     * @return bool
     */
    private function checkFirstLevelComment(Widget $widget, $item, $options){
        if(array_key_exists('first_level', $options) && $options['first_level'] == true){
            if(array_key_exists('parent_id',$item['value']) && $widget->getPostId() == $item['value']['parent_id']){
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * @param $options
     * @param $commentText
     * @return bool
     */
    private function checkExcludeKeywords($options, $commentText){
        if(array_key_exists('exclude_keywords', $options) && !empty($options['exclude_keywords'])){
            $excludeKeywords = array_map('trim', explode(',', $options['exclude_keywords']));
            if(!empty($excludeKeywords)){
                foreach ($excludeKeywords as $excludeKeyword){
                    if (strpos($commentText, $excludeKeyword) !== false) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $options
     * @param $commentText
     * @return bool
     */
    private function checkIncludeKeywords($options, $commentText){
        if(array_key_exists('include_keywords', $options) && !empty($options['include_keywords'])){
            $result = true;
            $includeKeywords = array_map('trim', explode(',', $options['include_keywords']));
            if(!empty($includeKeywords)){
                foreach ($includeKeywords as $includeKeyword){
                    if (strpos($commentText, $includeKeyword) !== false) {
                        $result = false;
                    }
                }
            }

            return $result;
        }

        return false;
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @param EntityManager $em
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return Facebook
     */
    public function getFb()
    {
        return $this->fb;
    }

    /**
     * @param Facebook $fb
     */
    public function setFb($fb)
    {
        $this->fb = $fb;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return Page|null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page|null $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return Subscribers|UserProfile|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Subscribers|UserProfile|null $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
