<?php
/**
 * Created by PhpStorm.
 * Date: 12.03.19
 * Time: 16:11
 */

namespace AppBundle\Webhooks;


use AppBundle\Entity\CommentReplies;
use AppBundle\Entity\Conversation;
use AppBundle\Entity\ConversationMessages;
use AppBundle\Entity\CustomFields;
use AppBundle\Entity\CustomRefParameter;
use AppBundle\Entity\DefaultReply;
use AppBundle\Entity\DefaultReplyLastSend;
use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\InsightsMessage;
use AppBundle\Entity\Keywords;
use AppBundle\Entity\MainMenu;
use AppBundle\Entity\MainMenuItems;
use AppBundle\Entity\Page;
use AppBundle\Entity\SaveImages;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\SubscribersCustomFields;
use AppBundle\Entity\SubscribersTags;
use AppBundle\Entity\SubscribersWidgets;
use AppBundle\Entity\UploadAttachments;
use AppBundle\Entity\UserInputDelay;
use AppBundle\Entity\UserInputResponse;
use AppBundle\Entity\UserRef;
use AppBundle\Entity\WelcomeMessage;
use AppBundle\Entity\Widget;
use AppBundle\Flows\Flow;
use AppBundle\Flows\FlowsItem;
use AppBundle\Flows\Type\SendMessage\SendMessageUserInput;
use AppBundle\Helper\MyFbBotApp;
use AppBundle\Helper\OtherHelper;
use AppBundle\Helper\Subscriber\SubscriberActionHelper;
use AppBundle\Helper\Webhook\ZapierHelper;
use Doctrine\ORM\EntityManager;
use MailchimpAPI\Mailchimp;
use pimax\Messages\Message;
use pimax\Messages\MessageButton;
use pimax\Messages\MessageElement;
use pimax\Messages\QuickReply;
use pimax\Messages\QuickReplyButton;
use pimax\Messages\StructuredMessage;
use pimax\UserProfile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Class FB
 * @package AppBundle\Webhooks
 */
class FB implements FBInterface
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
     * @var 
     */
    protected $pusher;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Page|null
     */
    protected $page = null;

    /**
     * @var MyFbBotApp|null
     */
    protected $bot = null;

    /**
     * @var Subscribers|null
     */
    protected $subscriber;

    /**
     * FB constructor.
     * @param EntityManager $em
     * @param ContainerInterface $container
     * @param $pusher
     * @param array $data
     */
    public function __construct(EntityManager $em, ContainerInterface $container, $pusher, array $data)
    {
        $this->em = $em;
        $this->container = $container;
        $this->pusher = $pusher;
        $this->data = $data;
    }

    /**
     * @return bool|mixed|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handler(){
        if (isset($this->data['entry']) && isset($this->data['entry'][0]) && isset($this->data['entry'][0]['messaging']) && !empty($this->data['entry'][0]['messaging'])){
            $page = $this->em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$this->data['entry'][0]['id'], 'status'=>true]);
            if($page instanceof Page && $page->getUser()->getProduct() instanceof DigistoreProduct) {
                //SET PAGE
                $this->setPage($page);
                //SET BOT
                $this->setBot(new MyFbBotApp($this->page->getAccessToken()));
                //PARSE MESSAGING
                foreach($this->data['entry'][0]['messaging'] as $message) {
                    //ACCEPT MESSAGE
                    if(isset($message['message_request']) && $message['message_request'] == "accept"){
                        return $this->acceptMessage($message);
                    }

                    // ECHO MESSAGE
                    if(isset($message['message']['is_echo']) && $message['message']['is_echo'] == true) {
                        return $this->echoMessage($message);
                    }

                    // WIDGET SEND MESSAGE USER REF
                    if(!isset($message['sender']['id']) || empty($message['sender']['id'])) {
                        return $this->widgetSendMessageUserRef($message);
                    }

                    // DELIVERED MESSAGE
                    if(!empty($message['delivery'])) {
                        return $this->deliveredMessage($message);
                    }

                    // READ MESSAGE
                    if(!empty($message['read'])) {
                        return $this->readMessage($message);
                    }

                    //GET USER PROFILE
                    $user = $this->bot->userProfile($message['sender']['id'], 'first_name,last_name,profile_pic,locale,timezone,gender');
                    
                    ///CREATE SUBSCRIBER IF NEED
                    $resultCreateSubscriber = $this->createSubscriber($message, $user);
                    if(!$resultCreateSubscriber){
                        return null;
                    }
                    //CHECK SUBSCRIBER
                    if($this->subscriber instanceof Subscribers){

                        //UPDATE SUBSCRIBER INFO
                        $this->updateSubscriberInfo($user);

                        // CHECKBOX_PLUGIN STATS
                        if(!empty($message['prior_message']['source']) && $message['prior_message']['source'] == 'checkbox_plugin' && !empty($message['prior_message']['identifier'])){
                            $this->checkboxPluginStatistics($message);
                        }
                        //Plugin Send To messanger
                        if(!empty($message['optin'])) {
                            return $this->pluginSendToMessenger($message);
                        }

                        //m.me ref
                        if(!empty($message['referral'])){
                            return $this->pluginRefUrl($message);
                        }

                        //PAYLOAD POSTBACK
                        if(!empty($message['postback'])) {
                            return $this->payloadPostback($message);
                        }

                        //QUICK REPLY
                        if(!empty($message['message']['quick_reply'])) {
                            $resultQuickReply = $this->payloadQuickReply($message);
                            if(is_null($resultQuickReply)){
                                return $resultQuickReply;
                            }
                        }

                        //TEXT Reply
                        if(!empty($message['message'])){
                            return $this->textReply($message);
                        }

                    }
                }
            }
        }

        return null;
    }

    /**
     * @param $message
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function acceptMessage($message){
        if(isset($message['sender']) && isset($message['sender']['id']) && !empty($message['sender']['id'])){
            $subscriberID = $message['sender']['id'];
            $subscriberAccept = $this->em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$this->page->getPageId(), 'subscriber_id'=>$subscriberID]);
            if($subscriberAccept instanceof Subscribers){
                $subscriberAccept->setStatus(true);
                $subscriberAccept->setLastInteraction(new \DateTime());
                $this->em->persist($subscriberAccept);
                $this->em->flush();
            }
        }

        return null;
    }

    /**
     * @param $message
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function echoMessage($message){
        //SAVE ECHO MESSAGE
        $pageEchoID = $message['sender']['id'];
        $subscriberEchoID = $message['recipient']['id'];
        $subscriberEcho = $this->em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$subscriberEchoID]);
        if($subscriberEcho instanceof Subscribers){
            //GENERATE Message Conversation Items
            $messageConversationItems = [];
            if(!empty($message['message']['text'])){
                $messageConversationItems['text'] = $message['message']['text'];
            }
            if(!empty($message['message']['attachments'])){
                foreach ($message['message']['attachments'] as $attachment){
                    if(!empty($attachment['type'])){
                        if($attachment['type'] == 'template'){
                            if($attachment['payload']['template_type'] == 'button'){
                                $messageConversationItems['buttons'] = [];
                                foreach ($attachment['payload']['buttons'] as $button){
                                    $messageConversationItems['buttons'][] = $button['title'];
                                }
                            }
                            elseif ($attachment['payload']['template_type'] == 'media'){
                                if(isset($attachment['payload']['elements']) && !empty($attachment['payload']['elements'])){
                                    foreach ($attachment['payload']['elements'] as $element){
                                        if(isset($element['media_type']) && !empty($element['media_type'])){
                                            if(isset($element['attachment_id']) && !empty($element['attachment_id'])){
                                                $attachmentItem = $this->em->getRepository("AppBundle:UploadAttachments")->findOneBy(['attachmentId'=>$element['attachment_id']]);
                                                if($attachmentItem instanceof UploadAttachments){
                                                    $messageConversationItems[$element['media_type']] = $attachmentItem->getUrl();
                                                }
                                            }
                                        }
                                        if(isset($element['buttons']) && !empty($element['buttons'])){
                                            $messageConversationItems['buttons'] = [];
                                            foreach ($element['buttons'] as $button){
                                                if(isset($button['title'])){
                                                    $messageConversationItems['buttons'][] = $button['title'];
                                                }
                                            }
                                        }
                                    }

                                }
                            }
                            elseif($attachment['payload']['template_type'] == 'generic'){
                                $messageConversationItems['gallery'] = [];
                                if(isset($attachment['payload']['elements']) && !empty($attachment['payload']['elements'])){
                                    foreach ($attachment['payload']['elements'] as $galeryItem){
                                        $title = (isset($galeryItem['title'])) ? $galeryItem['title'] : "";
                                        $subtitle = (isset($galeryItem['subtitle'])) ? $galeryItem['subtitle'] : "";
                                        $imageUrl = (isset($galeryItem['image_url'])) ? $galeryItem['image_url'] : "";
                                        $itemUrl = (isset($galeryItem['item_url'])) ? $galeryItem['item_url'] : "";
                                        $buttons = [];
                                        if(isset($galeryItem['buttons']) && !empty($galeryItem['buttons'])){
                                            foreach ($galeryItem['buttons'] as $button){
                                                $buttons[] = $button['title'];
                                            }
                                        }
                                        $messageConversationItems['gallery'][] = [
                                            'title'=>$title,
                                            'subtitle'=>$subtitle,
                                            'imageUrl' => $imageUrl,
                                            'itemUrl' => $itemUrl,
                                            'buttons' => $buttons
                                        ];
                                    }
                                }
                                $messageConversationItems['gallery'] = array_reverse($messageConversationItems['gallery']);
                            }
                            elseif($attachment['payload']['template_type'] == 'list'){
                                $messageConversationItems['list'] = [];
                                if(isset($attachment['payload']['elements']) && !empty($attachment['payload']['elements'])) {
                                    foreach ($attachment['payload']['elements'] as $element) {
                                        $title = (isset($element['title'])) ? $element['title'] : "";
                                        $subtitle = (isset($element['subtitle'])) ? $element['subtitle'] : "";
                                        $imageUrl = (isset($element['image_url'])) ? $element['image_url'] : "";
                                        $itemUrl = (isset($element['item_url'])) ? $element['item_url'] : "";
                                        $buttons = [];
                                        if(isset($element['buttons']) && !empty($element['buttons'])){
                                            foreach ($element['buttons'] as $button){
                                                $buttons[] = $button['title'];
                                            }
                                        }
                                        $messageConversationItems['list'][] = [
                                            'title'=>$title,
                                            'subtitle'=>$subtitle,
                                            'imageUrl' => $imageUrl,
                                            'itemUrl' => $itemUrl,
                                            'buttons' => $buttons
                                        ];
                                    }
                                }

                                if(isset($attachment['payload']['buttons']) && !empty($attachment['payload']['buttons'])){
                                    $messageConversationItems['buttons'] = [];
                                    foreach ($attachment['payload']['buttons'] as $button){
                                        $messageConversationItems['buttons'][] = $button['title'];
                                    }
                                }
                            }
                        }
                        elseif ($attachment['type'] == 'image'){
                            $messageConversationItems['image'] = $attachment['payload']['url'];
                        }
                        elseif ($attachment['type'] == 'video'){
                            $messageConversationItems['video'] = $attachment['payload']['url'];
                        }
                        elseif ($attachment['type'] == 'file'){
                            $filePath = explode('/', $attachment['payload']['url']);
                            $fileName = explode('?', $filePath[count($filePath)-1]);
                            $messageConversationItems['file'] = $fileName[0];
                        }
                        elseif($attachment['type'] == 'audio'){
                            $messageConversationItems['audio'] = $attachment['payload']['url'];
                        }
                        elseif ($attachment['type'] == 'location'){
                            $coordinates = '';
                            if(isset($attachment['payload']['coordinates']['lat'])){
                                $coordinates .= $attachment['payload']['coordinates']['lat'].',';
                            }
                            if(isset($attachment['payload']['coordinates']['long'])){
                                $coordinates .= $attachment['payload']['coordinates']['long'];
                            }
                            $messageConversationItems['location'] = [
                                'url' => isset($attachment['url']) ? $attachment['url'] : '',
                                'coordinates' => $coordinates
                            ];
                        }
                    }

                }
            }
            //SAVE CONVERSATION
            $this->saveConversation($pageEchoID, $subscriberEcho, $messageConversationItems, 2);
        }

        return null;
    }

    /**
     * @param $message
     * @return mixed|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function widgetSendMessageUserRef($message){
        $result = null;
        if (!empty($message['optin'])) {
            if (!empty($message['optin']['ref']) && !empty($message['optin']['user_ref'])) {
                $widget = $this->em->getRepository("AppBundle:Widget")->find($message['optin']['ref']);
                if($widget instanceof Widget){
                    $result = $this->sendWidgetFlowByUserRef($widget, $message['optin']['user_ref']);
                }
            }
        }

        return $result;
    }

    /**
     * @param $message
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deliveredMessage($message){
        if(!empty($message['sender']['id'])){
            if(!empty($message['delivery']['watermark'])){
                $insightMessages = $this->em->getRepository("AppBundle:InsightsMessage")->findInsightMessageDelivery($message['sender']['id']);
                if(!empty($insightMessages)){
                    foreach ($insightMessages as $insightMessage){
                        if($insightMessage instanceof InsightsMessage){
                            if($insightMessage->getDelivery() == false){
                                $insightMessage->setDelivery(true);
                                if(!empty($insightMessage->getWatermark())){
                                    $insightMessage->setWatermark($message['delivery']['watermark']);
                                }
                                $this->em->persist($insightMessage);
                                $this->em->flush();
                                $insightMessage->getFlowItem()->setDelivered($insightMessage->getFlowItem()->getDelivered()+1);
                                $this->em->persist($insightMessage->getFlowItem());
                                $this->em->flush();
                            }
                        }
                    }
                }
            }
            //CHECK FOR COMMENTS
            if(isset($message['delivery']['mids']) && !empty($message['delivery']['mids'])){
                foreach ($message['delivery']['mids'] as $mid){
                    $commentReplies = $this->em->getRepository('AppBundle:CommentReplies')->findOneBy(['page_id'=>$this->page->getPageId(), 'messageId'=>$mid]);
                    if(!$commentReplies instanceof CommentReplies){
                        $commentReplies = $this->em->getRepository('AppBundle:CommentReplies')->findOneBy(['page_id'=>$this->page->getPageId(), 'messageId'=>"m_".$mid]);
                    }
                    if($commentReplies instanceof CommentReplies){
                        $commentReplies->setSubscriberId($message['sender']['id']);
                        $this->em->persist($commentReplies);
                        $this->em->flush();
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param $message
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function readMessage($message){
        if(!empty($message['read']['watermark'])) {
            $insightMessages = $this->em->getRepository("AppBundle:InsightsMessage")->findInsightMessage($message['read']['watermark'], $message['sender']['id']);
            if(!empty($insightMessages)){
                foreach ($insightMessages as $insightMessage){
                    if($insightMessage instanceof InsightsMessage){
                        if($insightMessage->getDelivery() == false){
                            $insightMessage->getFlowItem()->setDelivered($insightMessage->getFlowItem()->getDelivered()+1);
                        }
                        $insightMessage->getFlowItem()->setOpened($insightMessage->getFlowItem()->getOpened()+1);
                        $this->em->persist($insightMessage->getFlowItem());
                        $this->em->flush();
                        $this->em->remove($insightMessage);
                        $this->em->flush();
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param $message
     * @param UserProfile $user
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function createSubscriber($message, UserProfile $user){
        //CREATE SUBSCRIBER IF NEED
        $subscriber = $this->em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$message['sender']['id']]);
        if(!$subscriber instanceof Subscribers){
            //Get Count User Subscribers
            $userCountSubscribers = $this->em->getRepository("AppBundle:Subscribers")->countAllByUserId($this->page->getUser()->getId());
            //Check limit subscribers
            if($this->page->getUser()->getLimitSubscribers() > $userCountSubscribers){
                //Check send request for upgrade product
                if(
                    $this->page->getUser()->getProduct() instanceof DigistoreProduct
                    && !empty($this->page->getUser()->getProduct()->getQuentnUrl())
                    && $this->page->getUser()->getProduct()->getLimitedQuentn() == $userCountSubscribers)
                {
                    $this->sendQuentn();
                }
                try{
                    $subscriber = new Subscribers($this->page->getPageId(),$message['sender']['id'],$user->getFirstName(),$user->getLastName(),$user->getGender(),$user->getLocale(),$user->getTimezone(),$user->getPicture());
                    $this->em->persist($subscriber);
                    $this->em->flush();
                    //SAVE AVATAR
                    if(!empty($subscriber->getAvatar())){
                        $saveImage = new SaveImages($subscriber->getAvatar(), "uploads/".$this->page->getPageId()."/subscribers/".$subscriber->getSubscriberId()."jpg", $subscriber->getId(), 'subscriber');
                        $this->em->persist($saveImage);
                        $this->em->flush();
                    }
                    
                    //ZAPIER TRIGGER
                    ZapierHelper::triggerNewSubscriber($this->em, $this->page, $subscriber);
                }
                catch (\Exception $e){
                   throw new \Exception($e->getMessage());
                }
            }
            else {
                //Check send request for upgrade product
                if($this->page->getUser()->getProduct() instanceof DigistoreProduct && !empty($this->page->getUser()->getProduct()->getQuentnUrl())){
                    $this->sendQuentn();
                }

                return false;
            }
        }
        $this->setSubscriber($subscriber);

        return true;
    }

    /**
     * @param UserProfile $user
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateSubscriberInfo(UserProfile $user){
        //SAVE AVATAR
        if($this->subscriber->getLastSaveAvatar() instanceof \DateTime){
            if($this->subscriber->getLastSaveAvatar()->diff(new \DateTime())->days >= 1){
                if(!empty($user->getPicture())){
                    $saveImage = new SaveImages($user->getPicture(), "uploads/".$this->page->getPageId()."/subscribers/".$this->subscriber->getSubscriberId()."jpg", $this->subscriber->getId(), 'subscriber');
                    $this->em->persist($saveImage);
                    $this->em->flush();
                }
            }
        }
        //UPDATE LAST INTERACTION
        $this->subscriber->setLastInteraction(new \DateTime());
        //UPDATE STATUS
        $this->subscriber->setStatus(true);
        $this->em->persist($this->subscriber);
        $this->em->flush();
    }

    /**
     * @param $message
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function checkboxPluginStatistics($message){
        $userRefs = $this->em->getRepository("AppBundle:UserRef")->findBy(['user_ref'=>$message['prior_message']['identifier']]);
        if(!empty($userRefs)){
            foreach ($userRefs as $userRef){
                if($userRef instanceof UserRef){
                    if($userRef->getWidget() instanceof Widget){
                        $flow = $userRef->getWidget()->getFlow();
                        if($flow instanceof Flows){
                            $flowItemStartStep = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flow, 'startStep'=>true]);
                            if($flowItemStartStep instanceof FlowItems){
                                $flowItemStartStep->setDelivered($flowItemStartStep->getDelivered()+1);
                                $flowItemStartStep->setOpened($flowItemStartStep->getOpened()+1);
                                $this->em->persist($flowItemStartStep);
                                $this->em->flush();
                            }
                        }
                        $subscriberWidget = $this->em->getRepository("AppBundle:SubscribersWidgets")->findOneBy(['subscriber'=>$this->subscriber, 'widget'=>$userRef->getWidget()]);
                        if(!$subscriberWidget instanceof SubscribersWidgets){
                            $subscriberWidget = new SubscribersWidgets($this->subscriber, $userRef->getWidget());
                            $this->em->persist($subscriberWidget);
                            $this->em->flush();
                        }
                        if($userRef->getWidget()->getSequence() instanceof Sequences){
                            SubscriberActionHelper::subscribeSequence($this->em, $this->page, $userRef->getWidget()->getSequence()->getId(), [$this->subscriber->getId()]);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $message
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function pluginSendToMessenger($message){
        if (!empty($message['optin']['ref'])) {
            $refArr = explode(':', $message['optin']['ref']);
            //CHECK PREVIEW
            if(count($refArr) == 3 && $refArr[0] == 'CHATBO_NEW' && $refArr[1] == 'PREVIEW_FLOW' && !empty($refArr[2])){
                $flow = $this->em->getRepository('AppBundle:Flows')->findOneBy(['id'=>$refArr[2], 'page_id'=>$this->page->getPageId()]);
                if($flow instanceof Flows){
                    $flowsSend = new Flow($this->em, $flow, $this->subscriber);
                    $flowsSend->sendStartStep();
                }
                return null;
            }
            else{
                $widget = $this->em->getRepository("AppBundle:Widget")->find($message['optin']['ref']);
                if($widget instanceof Widget){
                    $this->sendWidgetFlow($widget);
                }
            }
        }

        return null;
    }

    /**
     * @param $message
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function pluginRefUrl($message){
        if(!empty($message['referral']['ref'])){
            $widget = $this->em->getRepository("AppBundle:Widget")->find($message['referral']['ref']);
            if(!$widget instanceof Widget) {
                $widgetCustomRefParameter = $this->em->getRepository("AppBundle:CustomRefParameter")->findOneBy(['page_id'=>$this->page->getPageId(),'parameter'=>$message['referral']['ref']]);
                if($widgetCustomRefParameter instanceof CustomRefParameter){
                    $widget = $widgetCustomRefParameter->getWidget();
                }
            }
            if($widget instanceof Widget) {
                $this->sendWidgetFlow($widget);
            }
        }

        return null;
    }

    /**
     * @param $message
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function payloadPostback($message){
        if(isset($message['postback']['payload']) && !empty($message['postback']['payload'])){
            if(isset($message['postback']['title']) && !empty($message['postback']['title'])) {
                //SAVE CONVERSATION
                $this->saveConversation($this->page->getPageId(), $this->subscriber, ['text' => $message['postback']['title']], 1);
            }
            //PROCESS PAYLOAD
            $payload = $message['postback']['payload'];
            //WELCOME MESSAGE
            if($payload == "WELCOME_MESSAGE"){
                $this->payloadWelcomeMessage($message);
            }
            else{
                $this->payloadParse($payload);
            }
        }

        return null;
    }

    /**
     * @param $message
     * @return bool|mixed|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function payloadQuickReply($message){
        if(isset($message['message']['text']) && !empty($message['message']['text'])){
            //SAVE CONVERSATION
            $this->saveConversation($this->page->getPageId(), $this->subscriber, ['text' => $message['message']['text']], 1);
        }

        //PARSE PAYLOAD
        if(isset($message['message']['quick_reply']['payload']) && !empty($message['message']['quick_reply']['payload'])){
            $payload = $message['message']['quick_reply']['payload'];
            $arrayPostback = explode(':', $payload);
            if(isset($arrayPostback[0]) && $arrayPostback[0] == 'CHATBO_NEW'){
                if(isset($arrayPostback[1]) && !empty($arrayPostback[1])){
                    if($arrayPostback[1] == 'QUICK_REPLY'){
                        //PARSE QUICK REPLY PAYLOAD
                        $this->payloadParseQuickReply($arrayPostback);
                    }
                    //PARSE QR USER INPUT SELECT
                    elseif ($arrayPostback[1] == 'QUICK_REPLY_USER_INPUT'){
                        $this->payloadParseQuickReplyUserInput($arrayPostback, $message);
                    }
                    //PARSE QR USER INPUT SKIP
                    elseif ($arrayPostback[1] == 'QUICK_REPLY_USER_INPUT_SKIP'){
                        $this->payloadParseQuickReplyUserInputSkip($arrayPostback);
                    }
                }
            }
            else{
                return false;
            }
        }

        return null;
    }

    /**
     * @param $message
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function textReply($message){
        //GENERATE Message Conversation Items
        $messageConversationItems = [];
        if(isset($message['message']['text']) && !empty($message['message']['text'])){
            $messageConversationItems['text'] = $message['message']['text'];
        }
        if(isset($message['message']['attachments']) && !empty($message['message']['attachments'])){
            foreach ($message['message']['attachments'] as $attachment){
                if(!empty($attachment['type'])){
                    if ($attachment['type'] == 'image'){
                        $messageConversationItems['image'] = $attachment['payload']['url'];
                    }
                    elseif ($attachment['type'] == 'video'){
                        $messageConversationItems['video'] = $attachment['payload']['url'];
                    }
                    elseif ($attachment['type'] == 'file'){
                        $filePath = explode('/', $attachment['payload']['url']);
                        $fileName = explode('?', $filePath[count($filePath)-1]);
                        $messageConversationItems['file'] = $fileName[0];
                    }
                    elseif ($attachment['type'] == 'location'){
                        $coordinates = '';
                        if(isset($attachment['payload']['coordinates']['lat'])){
                            $coordinates .= $attachment['payload']['coordinates']['lat'].',';
                        }
                        if(isset($attachment['payload']['coordinates']['long'])){
                            $coordinates .= $attachment['payload']['coordinates']['long'];
                        }
                        $messageConversationItems['location'] = [
                            'url' => isset($attachment['url']) ? $attachment['url'] : '',
                            'coordinates' => $coordinates
                        ];
                    }
                    elseif ($attachment['type'] == 'audio'){
                        $messageConversationItems['audio'] = $attachment['payload']['url'];
                    }
                }

            }
        }

        //SAVE CONVERSATION
        $this->saveConversation($this->page->getPageId(), $this->subscriber, $messageConversationItems, 1);

        //GET COMMAND
        $command = isset($message['message']['text']) ? trim($message['message']['text']) : '';

        //CheckMainData
        $resultCheckMainData = $this->textReplyCheckMainData($command);
        if($resultCheckMainData == true){
            return null;
        }

        //CHECK USER INPUT DELAY
        $resultCheckUserInputDelay = $this->textReplyCheckUserInputDelay($command, $message);
        if($resultCheckUserInputDelay == true){
            return null;
        }

        //CHECK COMMENT REPLY
        $resultCheckCommentReply = $this->textReplyCheckCommentReply($command);
        if($resultCheckCommentReply == true){
            return null;
        }

        //FIND KEYWORDS
        $resultFindKeywords = $this->textReplyFindKeywords($command);
        if($resultFindKeywords == true){
            return null;
        }
        //DEFAULT REPLY
        $this->textReplyDefaultReply();

        return null;
    }

    /**
     * @param $command
     * @return bool
     * @throws \Exception
     */
    public function textReplyCheckMainData($command){
        if(strtolower($command) == 'meine daten'){
            if($this->page->getMainData() == true){
                $title = $this->subscriber->getFirstName().' '.$this->subscriber->getLastName();
                $subtitle = "Geschlecht: ".ucfirst($this->subscriber->getGender())."; Sprache: ".locale_get_display_language($this->subscriber->getLocale()).";";
                $subtitle .= " Zeitzone: ".$this->subscriber->getTimezone()."; Letzte Interaktion: ".$this->subscriber->getLastInteraction()->format("d M Y H:i").";";
                $subtitle .= " Datum abonniert: ".$this->subscriber->getDateSubscribed()->format("d M Y H:i").";";
                $image = (!empty($this->subscriber->getAvatar())) ? $this->subscriber->getAvatar() : '';
                $buttons=[];
                $buttons[] = new MessageButton(
                    MessageButton::TYPE_POSTBACK,
                    "Details einsehen",
                    'CHATBO_NEW:BUTTON:MainDetailsShow'
                );
                $buttons[] = new MessageButton(
                    MessageButton::TYPE_POSTBACK,
                    "Daten löschen",
                    'CHATBO_NEW:BUTTON:MainDetailsRemove'
                );

                $elements[] = new MessageElement($title, $subtitle, $image, $buttons);

                if(!empty($elements)){
                    $sendobject = new StructuredMessage($this->subscriber->getSubscriberId(), StructuredMessage::TYPE_GENERIC, ['elements' => $elements]);
                    $this->bot->send($sendobject);
                }
                return true;
            }
        }

        return false;
    }


    /**
     * @param $command
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function textReplyCheckCommentReply($command){
        $checkCommentReply = $this->em->getRepository("AppBundle:CommentReplies")->findOneBy(['page_id'=>$this->page->getPageId(), 'subscriberId'=>$this->subscriber->getSubscriberId()]);
        if($checkCommentReply instanceof CommentReplies){
            $widget = $checkCommentReply->getWidget();
            $this->em->remove($checkCommentReply);
            $this->em->flush();
            if($widget instanceof Widget && $widget->getStatus() == true){
                $subscriberWidget = new SubscribersWidgets($this->subscriber, $widget);
                $this->em->persist($subscriberWidget);
                $this->em->flush();

                $options = $widget->getOptions();
                if(array_key_exists('sending_options', $options) && in_array($options['sending_options'], [2,3])){
                    if($options['sending_options'] == 3 && array_key_exists('repeat_keywords', $options) && !empty($options['repeat_keywords'])){
                        $repeatKeywords = array_map('trim', explode(',', $options['repeat_keywords']));
                        if(!empty($repeatKeywords)){
                            $result = true;
                            foreach ($repeatKeywords as $repeatKeyword){
                                if (strpos($command, $repeatKeyword) !== false) {
                                    $result = false;
                                }
                            }
                            if($result == true){
                                return $result;
                            }
                        }
                    }

                    if($widget->getFlow() instanceof Flows){
                        $widget->setOptIn($widget->getOptIn()+1);
                        $this->em->persist($widget);
                        $this->em->flush();

                        $flowsSend = new Flow($this->em, $widget->getFlow(), $this->subscriber);
                        $flowsSend->sendStartStep();
                    }
                    if($widget->getSequence() instanceof Sequences){
                        SubscriberActionHelper::subscribeSequence($this->em, $this->page, $widget->getSequence()->getId(), [$this->subscriber->getId()]);
                    }

                }

                $commentsReplies = $this->em->getRepository("AppBundle:CommentReplies")->findBy(['widget'=>$widget, 'subscriberId'=>$this->subscriber->getSubscriberId()]);
                if(!empty($commentsReplies)){
                    foreach ($commentsReplies as $commentsReply){
                        if($commentsReply instanceof CommentReplies){
                            $this->em->remove($commentsReply);
                            $this->em->flush();
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param FlowItems $flowItems
     * @param $nextItems
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function sendNextItemsAfterUserInput(FlowItems $flowItems, $nextItems){
        if(!empty($nextItems)){
            $newFlowItem = new FlowItems(
                $flowItems->getFlow(),
                $flowItems->getUuid(),
                'user-input-send',
                FlowItems::TYPE_SEND_MESSAGE,
                $nextItems,
                $flowItems->getQuickReply(),
                false,
                $flowItems->getNextStep(),
                100,
                100,
                []
            );
            $flowsItemSend = new FlowsItem($this->em, $newFlowItem, $this->subscriber);
            $flowsItemSend->send();
        }
        else{
            if(!empty($flowItems->getNextStep())){
                $nextFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flowItems->getFlow(), 'uuid'=>$flowItems->getNextStep()]);
                if($nextFlowItem instanceof FlowItems){
                    $flowsItemSend = new FlowsItem($this->em, $nextFlowItem, $this->subscriber);
                    $flowsItemSend->send();
                }
            }
        }
    }

    /**
     * @param $command
     * @param $message
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function textReplyCheckUserInputDelay($command, $message){
        $checkUserInputDelay = $this->em->getRepository("AppBundle:UserInputDelay")->findOneBy(['subscriber'=>$this->subscriber]);
        if($checkUserInputDelay instanceof UserInputDelay){
            $userInputItem = null;
            $userInputKey = null;
            $nextItems = [];
            $items = $checkUserInputDelay->getFlowItem()->getItems();
            foreach ($items as $key=>$item){
                if(isset($item['uuid']) && $item['uuid'] == $checkUserInputDelay->getItemUuid()){
                    $userInputItem = $item;
                    $userInputKey = $key;
                }
                elseif (!is_null($userInputKey) && !is_null($userInputItem)){
                    $nextItems[] = $item;
                }
            }

            if(!empty($userInputItem) && is_array($userInputItem)){
                if(
                    array_key_exists('type', $userInputItem) && $userInputItem['type'] == "user_input"
                    && array_key_exists('params', $userInputItem) && !empty($userInputItem['params'])
                    && array_key_exists('description', $userInputItem['params']) && !empty($userInputItem['params']['description'])
                    && array_key_exists('keyboardInput', $userInputItem['params']) && !empty($userInputItem['params']['keyboardInput'])
                    && array_key_exists('replyType', $userInputItem['params']['keyboardInput'])
                ){
                    //VALIDATION REPLY
                    $validation = false;
                    switch ($userInputItem['params']['keyboardInput']['replyType']){
                        case 0:
                            $validation = true;
                            break;
                        case 1:
                            if(array_key_exists('active', $userInputItem['params']['keyboardInput']) &&  $userInputItem['params']['keyboardInput']['active'] == true){
                                $validation = true;
                            }
                            break;
                        case 2:
                            if(is_numeric($command)){
                                $validation = true;
                            }
                            break;
                        case 3:
                            if (filter_var($command, FILTER_VALIDATE_EMAIL)) {
                                $validation = true;
                            }
                            break;
                        case 4:
                            if(preg_match('/^[+0-9]{6,15}+$/', $command)){
                                $validation = true;
                            }
                            break;
                        case 5:
                            if(preg_match('/(?:https?:\/\/)?(?:[a-zA-Z0-9.-]+?\.(?:[a-zA-Z])|\d+\.\d+\.\d+\.\d+)/', $command))
                            {
                                $validation = true;
                            }
                            break;
                        case 6:
                            $url = null;
                            if(isset($message['message']['attachments']) && !empty($message['message']['attachments'])){
                                foreach ($message['message']['attachments'] as $attachment){
                                    if(!empty($attachment['type'])){
                                        if ($attachment['type'] == 'file' && !empty($attachment['payload']['url'])){
                                            $url = $attachment['payload']['url'];
                                            break;
                                        }
                                    }
                                }
                            }
                            if(!empty($url)){
                                $parsePath = explode('/', $url);
                                $parseName = explode('?', $parsePath[count($parsePath)-1]);
                                $fileName = $parseName[0];
                                $filePath = "uploads/".$this->page->getPageId()."/user_input/".$checkUserInputDelay->getFlowItem()->getUuid()."/".$this->subscriber->getSubscriberId()."/".$fileName;
                                if(OtherHelper::saveImage($url, $filePath)){
                                    $request = Request::createFromGlobals();
                                    $url = $request->getSchemeAndHttpHost()."/".$filePath;
                                }
                                $command = $url;
                                $validation = true;
                            }
                            break;
                        case 7:
                            $url = null;
                            if(isset($message['message']['attachments']) && !empty($message['message']['attachments'])){
                                foreach ($message['message']['attachments'] as $attachment){
                                    if(!empty($attachment['type'])){
                                        if ($attachment['type'] == 'image' && !empty($attachment['payload']['url'])){
                                            $url = $attachment['payload']['url'];
                                            break;
                                        }
                                    }
                                }
                            }
                            if(!empty($url)){
                                $parsePath = explode('/', $url);
                                $parseName = explode('?', $parsePath[count($parsePath)-1]);
                                $fileName = $parseName[0];
                                $filePath = "uploads/".$this->page->getPageId()."/user_input/".$checkUserInputDelay->getFlowItem()->getUuid()."/".$this->subscriber->getSubscriberId()."/".$fileName;
                                if(OtherHelper::saveImage($url, $filePath)){
                                    $request = Request::createFromGlobals();
                                    $url = $request->getSchemeAndHttpHost()."/".$filePath;
                                }
                                $command = $url;
                                $validation = true;
                            }
                            break;
                        case 10:
                            $coordinates = null;
                            if(isset($message['message']['attachments']) && !empty($message['message']['attachments'])){
                                foreach ($message['message']['attachments'] as $attachment){
                                    if(!empty($attachment['type'])){
                                        if ($attachment['type'] == 'location' && !empty($attachment['payload']['coordinates']['lat']) && !empty($attachment['payload']['coordinates']['long'])){
                                            $coordinates = $attachment['payload']['coordinates']['lat'].','.$attachment['payload']['coordinates']['long'];
                                        }
                                    }
                                }
                            }
                            if(!empty($coordinates)){
                                $command = $coordinates;
                                $validation = true;
                            }
                            break;
                    }
                    if($validation == true){
                        //save user response
                        $userInputResponse = new UserInputResponse(
                            $this->page->getPageId(),
                            $this->subscriber,
                            $checkUserInputDelay->getFlowItem(),
                            $userInputItem['params']['description'],
                            $command,
                            array_key_exists('replyType', $userInputItem['params']['keyboardInput']) ? $userInputItem['params']['keyboardInput']['replyType'] : 0
                        );
                        $this->em->persist($userInputResponse);
                        $this->em->flush();
                        //save user response to custom_field
                        if(array_key_exists('id', $userInputItem['params']['keyboardInput']) && !empty($userInputItem['params']['keyboardInput']['id'])){
                            $customField = $this->em->getRepository("AppBundle:CustomFields")->findOneBy([
                                'page_id' => $this->page->getPageId(),
                                'id' => $userInputItem['params']['keyboardInput']['id']
                            ]);
                            if($customField instanceof CustomFields){
                                $subscriberCustomField = $this->em->getRepository("AppBundle:SubscribersCustomFields")->findOneBy([
                                    'subscriber' => $this->subscriber,
                                    'customField' => $customField
                                ]);
                                if($subscriberCustomField instanceof SubscribersCustomFields){
                                    $subscriberCustomField->setValue($command);
                                }
                                else{
                                    $subscriberCustomField = new SubscribersCustomFields($this->subscriber, $customField, $command);
                                }
                                $this->em->persist($subscriberCustomField);
                                $this->em->flush();

                                //ZAPIER TRIGGER
                                ZapierHelper::triggerSetCustomField($this->em, $this->page, $subscriberCustomField);
                            }
                        }
                        //SEND NEXT STEP
                        if(array_key_exists('buttons', $userInputItem['params']) && !empty($userInputItem['params']['buttons'])){
                            $nextStepID = null;
                            foreach ($userInputItem['params']['buttons'] as $button){
                                if(array_key_exists('next_step', $button) && !empty($button['next_step'])){
                                    $nextStepID = $button['next_step'];
                                }
                            }
                            if(!is_null($nextStepID)){
                                $nextFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$checkUserInputDelay->getFlowItem()->getFlow(), 'uuid'=>$nextStepID]);
                                if($nextFlowItem instanceof FlowItems){
                                    $flowsItemSend = new FlowsItem($this->em, $nextFlowItem, $this->subscriber);
                                    $flowsItemSend->send();
                                }
                            }
                        }
                    }
                    else{
                        if(array_key_exists('retry_message', $userInputItem['params']['keyboardInput'])
                            && !empty($userInputItem['params']['keyboardInput']['retry_message'])
                        ){
                            //SEND RETRY MESSAGE
                            $sendMessageUserInput = new SendMessageUserInput(
                                $this->em,
                                $this->page,
                                $checkUserInputDelay->getFlowItem(),
                                $userInputItem,
                                $this->subscriber,
                                Message::NOTIFY_REGULAR,
                                true
                            );
                            $sendItem = $sendMessageUserInput->gerRetrySendData();
                            if(!empty($sendItem)){
                                $this->bot->send($sendItem);
                            }
                            return true;
                        }
                        else{
                            $userInputResponse = new UserInputResponse(
                                $this->page->getPageId(),
                                $this->subscriber,
                                $checkUserInputDelay->getFlowItem(),
                                $userInputItem['params']['description'],
                                '',
                                array_key_exists('replyType', $userInputItem['params']['keyboardInput']) ? $userInputItem['params']['keyboardInput']['replyType'] : 0
                            );
                            $this->em->persist($userInputResponse);
                            $this->em->flush();
                        }
                    }

                    //remove input delay
                    $this->em->remove($checkUserInputDelay);
                    $this->em->flush();

                    if($validation == true || !array_key_exists('retry_message', $userInputItem['params']['keyboardInput'])
                        || empty($userInputItem['params']['keyboardInput']['retry_message'])
                    ){
                        //SEND NEXT ITEMS
                        $this->sendNextItemsAfterUserInput($checkUserInputDelay->getFlowItem(), $nextItems);
                    }

                    return true;
                }
                else{
                    $this->em->remove($checkUserInputDelay);
                    $this->em->flush();
                }
            }
            else{
                $this->em->remove($checkUserInputDelay);
                $this->em->flush();
            }
        }

        return false;
    }

    /**
     * @param $command
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function textReplyFindKeywords($command){
        //CHECK IS KEYWORDS
        $keywordIs = $this->em->getRepository("AppBundle:Keywords")->findIsByPageIdAndCommand($this->page->getPageId(), $command);
        if($keywordIs instanceof Keywords && $keywordIs->getStatus() == true){
            //SEND KEYWORD IS MESSAGE
            if($keywordIs->getFlow() instanceof Flows){
                $flowsSend = new Flow($this->em, $keywordIs->getFlow(), $this->subscriber);
                $flowsSend->sendStartStep();
            }
            //ADD KEYWORD ACTION
            SubscriberActionHelper::addActionForSubscriber($this->em, $this->page, $keywordIs->getActions(), $this->subscriber);

            return true;
        }

        //CHECK BEGINS WITH KEYWORDS
        $keywordBegin = $this->em->getRepository("AppBundle:Keywords")->findBeginsByPageIdAndCommand($this->page->getPageId(), $command);
        if($keywordBegin instanceof Keywords && $keywordBegin->getStatus() == true){
            //SEND KEYWORD BEGINS WITH MESSAGE
            if($keywordBegin->getFlow() instanceof Flows){
                $flowsSend = new Flow($this->em, $keywordBegin->getFlow(), $this->subscriber);
                $flowsSend->sendStartStep();
            }
            //ADD KEYWORD ACTION
            SubscriberActionHelper::addActionForSubscriber($this->em, $this->page, $keywordBegin->getActions(), $this->subscriber);

            return true;
        }

        //CHECK CONTAINS KEYWORDS
        $keywordContains = $this->em->getRepository("AppBundle:Keywords")->findContainsByPageIdAndCommand($this->page->getPageId(), $command);
        if($keywordContains instanceof Keywords && $keywordContains->getStatus() == true){
            //SEND KEYWORD CONTAINS MESSAGE
            if($keywordContains->getFlow() instanceof Flows){
                $flowsSend = new Flow($this->em, $keywordContains->getFlow(), $this->subscriber);
                $flowsSend->sendStartStep();
            }
            //ADD KEYWORD ACTION
            SubscriberActionHelper::addActionForSubscriber($this->em, $this->page, $keywordContains->getActions(), $this->subscriber);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function textReplyDefaultReply(){
        $defaultReply = $this->em->getRepository("AppBundle:DefaultReply")->findOneBy(['page_id'=>$this->page->getPageId()]);
        if($defaultReply instanceof DefaultReply && $defaultReply->getStatus() == true){
            if($defaultReply->getType() == 1){
                $defaultReplyLastSend = $this->em->getRepository("AppBundle:DefaultReplyLastSend")->findOneBy(['defaultReply'=>$defaultReply, 'subscriber'=>$this->subscriber]);
                if($defaultReplyLastSend instanceof DefaultReplyLastSend){
                    $defaultReplyPrevDate = new \DateTime('-24 hours');
                    if($defaultReplyLastSend->getLastSend()->format('Y-m-d H:i') > $defaultReplyPrevDate->format('Y-m-d H:i')){
                        return true;
                    }
                }
            }
            //SEND DEFAULT REPLY
            if($defaultReply->getFlow() instanceof Flows){
                $flowsSend = new Flow($this->em, $defaultReply->getFlow(), $this->subscriber);
                $flowsSend->sendStartStep();
            }
            //UPDATE LAST DEFAULT REPLY
            if($defaultReply->getType() == 1){
                $defaultReplyLastSend = $this->em->getRepository("AppBundle:DefaultReplyLastSend")->findOneBy(['defaultReply'=>$defaultReply, 'subscriber'=>$this->subscriber]);
                if($defaultReplyLastSend instanceof DefaultReplyLastSend){
                    $defaultReplyLastSend->setLastSend(new \DateTime());
                }
                else{
                    $defaultReplyLastSend = new DefaultReplyLastSend($defaultReply, $this->subscriber);
                }
                $this->em->persist($defaultReplyLastSend);
                $this->em->flush();
            }

            return true;
        }

        return true;
    }

    /**
     * @param $arrayPostback
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function payloadParseQuickReply($arrayPostback){
        if(isset($arrayPostback[2]) && !empty($arrayPostback[2])){
            if($arrayPostback[2] == 'MainDetailsRemoveNot'){
                $textMessage = "Es wurde nichts gelöscht. Du kannst deine Daten jederzeit nochmal einsehen. Schreibe mir dafür einfach \"Meine Daten\".";
                $sendobject = new Message($this->subscriber->getSubscriberId(), $textMessage);
                $this->bot->send($sendobject);
            }
            elseif ($arrayPostback[2] == 'MainDetailsRemoveYes'){
                $textMessage = "Ok, wir haben alle deine Informationen gelöscht. Solltest du uns wieder kontaktieren werden deine Daten neu angelegt.";
                $sendobject = new Message($this->subscriber->getSubscriberId(), $textMessage);
                $this->bot->send($sendobject);

                $this->em->remove($this->subscriber);
                $this->em->flush();
            }
            else{
                $flow = $this->em->getRepository("AppBundle:Flows")->find($arrayPostback[2]);
                if($flow instanceof Flows) {
                    if (isset($arrayPostback[3]) && !empty($arrayPostback[3])) {
                        $flowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flow, 'uuid'=>$arrayPostback[3]]);
                        if($flowItem instanceof FlowItems){
                            if(!empty($flowItem->getQuickReply())){
                                if(isset($arrayPostback[4]) && !empty($arrayPostback[4])){
                                    $quickReplyID = $arrayPostback[4];
                                    $quickReplies = $flowItem->getQuickReply();
                                    $nextStepID = null;
                                    //UPDATE CLICK
                                    foreach ($quickReplies as $key=>$quickReply){
                                        if(isset($quickReply['uuid']) && $quickReply['uuid'] == $quickReplyID){
                                            if(isset($quickReply['click']) && $quickReply['click']>0){
                                                $quickReplies[$key]['click'] = $quickReply['click'] + 1;
                                            }
                                            else{
                                                $quickReplies[$key]['click'] = 1;
                                            }
                                            $flowItem->setClicked($flowItem->getClicked() + 1);
                                            if(isset($quickReply['next_step']) && !empty($quickReply['next_step'])){
                                                $nextStepID = $quickReply['next_step'];
                                            }
                                        }
                                    }
                                    $flowItem->setQuickReply($quickReplies);
                                    $this->em->persist($flowItem);
                                    $this->em->flush();

                                    //NEXT STEP
                                    if(!is_null($nextStepID)){
                                        $nextFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flowItem->getFlow(), 'uuid'=>$nextStepID]);
                                        if($nextFlowItem instanceof FlowItems){
                                            $flowsItemSend = new FlowsItem($this->em, $nextFlowItem, $this->subscriber);
                                            $flowsItemSend->send();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $arrayPostback
     * @param $message
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function payloadParseQuickReplyUserInput($arrayPostback, $message){
        if(isset($arrayPostback[2]) && !empty($arrayPostback[2])) {
            $flow = $this->em->getRepository("AppBundle:Flows")->find($arrayPostback[2]);
            if ($flow instanceof Flows) {
                if (isset($arrayPostback[3]) && !empty($arrayPostback[3])) {
                    $flowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow' => $flow, 'uuid' => $arrayPostback[3]]);
                    if ($flowItem instanceof FlowItems) {
                        if(!empty($flowItem->getItems())){
                            if(isset($arrayPostback[4]) && !empty($arrayPostback[4])){
                                $itemUUID = $arrayPostback[4];
                                //remove user input delay
                                $userInputDelay = $this->em->getRepository("AppBundle:UserInputDelay")->findOneBy([
                                    'page_id' => $this->page->getPageId(),
                                    'subscriber' => $this->subscriber,
                                    'flowItem' => $flowItem,
                                    'itemUuid' => $itemUUID
                                ]);
                                if($userInputDelay instanceof UserInputDelay){
                                    $this->em->remove($userInputDelay);
                                    $this->em->flush();
                                }
                                //find user input item
                                $userInputItem = null;
                                $userInputKey = null;
                                $nextItems = [];
                                $items = $flowItem->getItems();
                                foreach ($items as $key=>$item){
                                    if(isset($item['uuid']) && $item['uuid'] == $itemUUID){
                                        $userInputItem = $item;
                                        $userInputKey = $key;
                                    }
                                    elseif (!is_null($userInputKey) && !is_null($userInputItem)){
                                        $nextItems[] = $item;
                                    }
                                }

                                if(!empty($userInputItem) && is_array($userInputItem)){
                                    if(
                                        array_key_exists('type', $userInputItem) && $userInputItem['type'] == "user_input"
                                        && array_key_exists('params', $userInputItem) && !empty($userInputItem['params'])
                                        && array_key_exists('description', $userInputItem['params']) && !empty($userInputItem['params']['description'])
                                    ){
                                        //find user response
                                        $responseUser = $message['message']['text'];
                                        if(isset($arrayPostback[5]) && !empty($arrayPostback[5])
                                            && array_key_exists('quick_reply', $userInputItem['params'])
                                            && !empty($userInputItem['params']['quick_reply'])
                                        ){
                                            $qrUUID = $arrayPostback[5];
                                            foreach ($userInputItem['params']['quick_reply'] as $key=>$quickReply){
                                                if(array_key_exists('uuid', $quickReply) && $quickReply['uuid'] == $qrUUID) {
                                                    if(array_key_exists('save_reply', $quickReply) && !empty($quickReply['save_reply'])){
                                                        $responseUser = $quickReply['save_reply'];
                                                    }
                                                    //Update click
                                                    if(isset($quickReply['click']) && $quickReply['click']>0){
                                                        $items[$userInputKey]['params']['quick_reply'][$key]['click'] = $quickReply['click'] + 1;
                                                    }
                                                    else{
                                                        $items[$userInputKey]['params']['quick_reply'][$key]['click'] = 1;
                                                    }
                                                    $flowItem->setClicked($flowItem->getClicked() + 1);
                                                }
                                            }

                                            $flowItem->setItems($items);
                                            $this->em->persist($flowItem);
                                            $this->em->flush();
                                        }
                                        //save user response
                                        $userInputResponse = new UserInputResponse(
                                            $this->page->getPageId(),
                                            $this->subscriber,
                                            $flowItem,
                                            $userInputItem['params']['description'],
                                            $responseUser,
                                            array_key_exists('replyType', $userInputItem['params']['keyboardInput']) ? $userInputItem['params']['keyboardInput']['replyType'] : 0
                                        );
                                        $this->em->persist($userInputResponse);
                                        $this->em->flush();
                                        //save user response to custom_field
                                        if(array_key_exists('keyboardInput', $userInputItem['params'])
                                            && array_key_exists('id', $userInputItem['params']['keyboardInput'])
                                            && !empty($userInputItem['params']['keyboardInput']['id'])
                                        ){
                                            $customField = $this->em->getRepository("AppBundle:CustomFields")->findOneBy([
                                                'page_id' => $this->page->getPageId(),
                                                'id' => $userInputItem['params']['keyboardInput']['id']
                                            ]);
                                            if($customField instanceof CustomFields){
                                                $subscriberCustomField = $this->em->getRepository("AppBundle:SubscribersCustomFields")->findOneBy([
                                                    'subscriber' => $this->subscriber,
                                                    'customField' => $customField
                                                ]);
                                                if($subscriberCustomField instanceof SubscribersCustomFields){
                                                    $subscriberCustomField->setValue($responseUser);
                                                }
                                                else{
                                                    $subscriberCustomField = new SubscribersCustomFields($this->subscriber, $customField, $responseUser);
                                                }
                                                $this->em->persist($subscriberCustomField);
                                                $this->em->flush();

                                                //ZAPIER TRIGGER
                                                ZapierHelper::triggerSetCustomField($this->em, $this->page, $subscriberCustomField);
                                            }
                                        }
                                        //SEND NEXT STEP
                                        if(array_key_exists('buttons', $userInputItem['params']) && !empty($userInputItem['params']['buttons'])){
                                            $nextStepID = null;
                                            foreach ($userInputItem['params']['buttons'] as $button){
                                                if(array_key_exists('next_step', $button) && !empty($button['next_step'])){
                                                    $nextStepID = $button['next_step'];
                                                }
                                            }
                                            if(!is_null($nextStepID)){
                                                $nextFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flowItem->getFlow(), 'uuid'=>$nextStepID]);
                                                if($nextFlowItem instanceof FlowItems){
                                                    $flowsItemSend = new FlowsItem($this->em, $nextFlowItem, $this->subscriber);
                                                    $flowsItemSend->send();
                                                }
                                            }
                                        }
                                        //SEND NEXT ITEMS
                                        $this->sendNextItemsAfterUserInput($flowItem, $nextItems);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $arrayPostback
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function payloadParseQuickReplyUserInputSkip($arrayPostback){
        if(isset($arrayPostback[2]) && !empty($arrayPostback[2])) {
            $flow = $this->em->getRepository("AppBundle:Flows")->find($arrayPostback[2]);
            if ($flow instanceof Flows) {
                if (isset($arrayPostback[3]) && !empty($arrayPostback[3])) {
                    $flowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow' => $flow, 'uuid' => $arrayPostback[3]]);
                    if ($flowItem instanceof FlowItems) {
                        if(!empty($flowItem->getItems())){
                            if(isset($arrayPostback[4]) && !empty($arrayPostback[4])){
                                $itemUUID = $arrayPostback[4];
                                //remove user input delay
                                $userInputDelay = $this->em->getRepository("AppBundle:UserInputDelay")->findOneBy([
                                    'page_id' => $this->page->getPageId(),
                                    'subscriber' => $this->subscriber,
                                    'flowItem' => $flowItem,
                                    'itemUuid' => $itemUUID
                                ]);
                                if($userInputDelay instanceof UserInputDelay){
                                    $this->em->remove($userInputDelay);
                                    $this->em->flush();
                                }
                                //find user input item
                                $userInputItem = null;
                                $userInputKey = null;
                                $nextItems = [];
                                foreach ($flowItem->getItems() as $key=>$item){
                                    if(isset($item['uuid']) && $item['uuid'] == $itemUUID){
                                        $userInputItem = $item;
                                        $userInputKey = $key;
                                    }
                                    elseif (!is_null($userInputKey) && !is_null($userInputItem)){
                                        $nextItems[] = $item;
                                    }
                                }

                                if(!empty($userInputItem) && is_array($userInputItem)){
                                    if(
                                        array_key_exists('type', $userInputItem) && $userInputItem['type'] == "user_input"
                                        && array_key_exists('params', $userInputItem) && !empty($userInputItem['params'])
                                        && array_key_exists('description', $userInputItem['params']) && !empty($userInputItem['params']['description'])
                                    ){
                                        //save user response
                                        $userInputResponse = new UserInputResponse($this->page->getPageId(), $this->subscriber, $flowItem, $userInputItem['params']['description']);
                                        $this->em->persist($userInputResponse);
                                        $this->em->flush();

                                        //SEND NEXT ITEMS
                                        $this->sendNextItemsAfterUserInput($flowItem, $nextItems);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $message
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function payloadWelcomeMessage($message){
        //SEND WELCOME MESSAGE WITH WIDGET
        if(!empty($message['postback']['referral'])){
            if(!empty($message['postback']['referral']['ref'])){
                $widget = $this->em->getRepository("AppBundle:Widget")->find($message['postback']['referral']['ref']);
                if(!$widget instanceof Widget) {
                    $widgetCustomRefParameter = $this->em->getRepository("AppBundle:CustomRefParameter")->findOneBy(['page_id'=>$this->page->getPageId(),'parameter'=>$message['postback']['referral']['ref']]);
                    if($widgetCustomRefParameter instanceof CustomRefParameter){
                        $widget = $widgetCustomRefParameter->getWidget();
                    }
                }
                if($widget instanceof Widget) {
                    $this->sendWidgetFlow($widget);
                }
            }
        }
        else{
            $welcomeMessage = $this->em->getRepository("AppBundle:WelcomeMessage")->findOneBy(['page_id'=>$this->page->getPageId()]);
            if($welcomeMessage instanceof WelcomeMessage and $welcomeMessage->getStatus() == true){
                //SEND WELCOME MESSAGE
                if($welcomeMessage->getFlow() instanceof Flows){
                    $flowSend = new Flow($this->em, $welcomeMessage->getFlow(), $this->subscriber);
                    $flowSend->sendStartStep();
                }
            }
        }
    }

    /**
     * @param $payload
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function payloadParse($payload){
        //PARSE PAYLOAD
        $arrayPostback = explode(':', $payload);
        //CHECK NEW CHATBO
        if(isset($arrayPostback[0]) && $arrayPostback[0] == 'CHATBO_NEW'){
            if(isset($arrayPostback[1]) && !empty($arrayPostback[1])){
                if($arrayPostback[1] == 'BUTTON'){
                    //PARSE BUTTON PAYLOAD
                    $this->payloadParseButton($arrayPostback);
                }
                elseif ($arrayPostback[1] == 'MENU'){
                    //PARSE MENU PAYLOAD
                    //NEED CODE
                    $this->payloadParseMenu($arrayPostback);
                }
                elseif ($arrayPostback[1] == 'BUTTON_USER_INPUT_SKIP'){
                    //PARSE BUTTON USER INPUT SKIP
                    $this->payloadParseButtonInputSkip($arrayPostback);
                }
            }
        }
    }

    /**
     * @param $arrayPostback
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function payloadParseButton($arrayPostback){
        if(isset($arrayPostback[2]) && !empty($arrayPostback[2])){
            if($arrayPostback[2] == 'MainDetailsShow'){
                $textMessage = "Name: ".$this->subscriber->getFirstName()." ".$this->subscriber->getLastName()."\n";
                $textMessage .= "Geschlecht: ".ucfirst($this->subscriber->getGender())."\n";
                $textMessage .= "Sprache: ".locale_get_display_language($this->subscriber->getLocale())."\n";
                $textMessage .= "Zeitzone: ".$this->subscriber->getTimezone()."\n";
                $textMessage .= "Letzte Interaktion: ".$this->subscriber->getLastInteraction()->format("d M Y H:i")."\n";
                $textMessage .= "Datum abonniert: ".$this->subscriber->getDateSubscribed()->format("d M Y H:i")."\n";
                //Tags
                $subscriberTags = $this->em->getRepository("AppBundle:SubscribersTags")->findBy(['subscriber'=>$this->subscriber]);
                if(!empty($subscriberTags)){
                    $textMessage .= "Tags:\n";
                    foreach ($subscriberTags as $subscriberTag){
                        if($subscriberTag instanceof SubscribersTags){
                            $textMessage .= "\t- ".$subscriberTag->getTag()->getName()."\n";
                        }
                    }
                }
                //Custom Fields
                $subscriberCustomFields = $this->em->getRepository("AppBundle:SubscribersCustomFields")->findBy(['subscriber'=>$this->subscriber]);
                if(!empty($subscriberCustomFields)){
                    $customFields = [];
                    foreach ($subscriberCustomFields as $subscriberCustomField){
                        if($subscriberCustomField instanceof SubscribersCustomFields){
                            $customFields[] = [
                              'name' => $subscriberCustomField->getCustomField()->getName(),
                              'type' => $subscriberCustomField->getCustomField()->getType(),
                              'value' => $subscriberCustomField->getValue(),
                            ];
                        }
                    }
                    if(!empty($customFields)){
                        $textMessage .= "Benutzerdefinierte Felder:\n";
                        foreach ($customFields as $customField){
                            if($customField['type'] == 3 || $customField['type'] == 4){
                                $date = new \DateTime($customField['value']);
                                if($date instanceof \DateTime){
                                    if($customField['type'] == 3){
                                        $value = $date->format('Y-m-d');
                                    }
                                    else{
                                        $value = $date->format('Y-m-d H:i');
                                    }
                                }
                                else{
                                    $value = $customField['value'];
                                }
                            }
                            elseif ($customField['type'] == 5){
                                if($customField['value'] == true || $customField['value'] == "true"){
                                    $value = "Yes";
                                }
                                elseif ($customField['value'] == false || $customField['value'] == "false"){
                                    $value = "No";
                                }
                                else{
                                    $value = $customField['value'];
                                }
                            }
                            else{
                                $value = $customField['value'];
                            }
                            $textMessage .= "\t- ".$customField['name'].": ".$value."\n";
                        }
                    }
                }
                $textMessage .= "\nDas sind alle Informationen über dich, die ich habe.";

                $sendobject = new StructuredMessage($this->subscriber->getSubscriberId(),
                    StructuredMessage::TYPE_BUTTON,
                    [
                        'text' => $textMessage,
                        'buttons' => [new MessageButton(MessageButton::TYPE_POSTBACK, "Daten löschen", 'CHATBO_NEW:BUTTON:MainDetailsRemove')]
                    ]
                );
                $this->bot->send($sendobject);
            }
            elseif ($arrayPostback[2] == 'MainDetailsRemove'){
                $textMessage = "Möchtest du wirklich alle Daten löschen?\nIch möchte dich darauf hinweisen, dass wir nach dem Löschen deiner Daten von unserem Server nicht mehr in der Lage sind dich von Facebook Anzeigen auszuschließen. Das bedeutet, wenn wir deine Daten löschen zeigt dir Facebook unsere gesponsorten Nachrichten an. Die Auslieferung von Facebook Anzeigen an dich können wir nur dann verhindern, wenn wir deine Daten nicht löschen.";
                $quickItems = [];
                $quickItems[] = new QuickReplyButton(QuickReplyButton::TYPE_TEXT,
                    "Nein",
                    'CHATBO_NEW:QUICK_REPLY:MainDetailsRemoveNot'
                );
                $quickItems[] = new QuickReplyButton(QuickReplyButton::TYPE_TEXT,
                    "Trotzdem löschen",
                    'CHATBO_NEW:QUICK_REPLY:MainDetailsRemoveYes'
                );
                $sendobject = new QuickReply($this->subscriber->getSubscriberId(), $textMessage, $quickItems);
                $this->bot->send($sendobject);
            }
            else{
                $flow = $this->em->getRepository("AppBundle:Flows")->find($arrayPostback[2]);
                if($flow instanceof Flows){
                    if(isset($arrayPostback[3]) && !empty($arrayPostback[3])){
                        $flowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flow, 'uuid'=>$arrayPostback[3]]);
                        if($flowItem instanceof FlowItems){
                            if(!empty($flowItem->getItems())){
                                if(isset($arrayPostback[4]) && !empty($arrayPostback[4])){
                                    $itemID = $arrayPostback[4];
                                    if(isset($arrayPostback[5]) && !empty($arrayPostback[5])){
                                        $buttonID = $arrayPostback[5];
                                        //UPDATE BUTTON CLICK FOR FLOW ITEM
                                        $nextStepID = $this->updateClickButtonForFlowItem($flowItem, $itemID, $buttonID);

                                        //NEXT STEP
                                        if(!is_null($nextStepID)){
                                            $nextFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flowItem->getFlow(), 'uuid'=>$nextStepID]);
                                            if($nextFlowItem instanceof FlowItems){
                                                $flowsItemSend = new FlowsItem($this->em, $nextFlowItem, $this->subscriber);
                                                $flowsItemSend->send();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $arrayPostback
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function payloadParseMenu($arrayPostback){
        if(isset($arrayPostback[2]) && !empty($arrayPostback[2])){
            if(isset($arrayPostback[3]) && !empty($arrayPostback[3])){
                $mainMenu = $this->em->getRepository("AppBundle:MainMenu")->findOneBy(['page_id'=>$this->page->getPageId()]);
                if($mainMenu instanceof MainMenu){
                    $menuItem = $this->em->getRepository("AppBundle:MainMenuItems")->findOneBy(['mainMenu'=>$mainMenu, 'uuid'=>$arrayPostback[3]]);
                    if($menuItem instanceof MainMenuItems){
                        $menuItem->setClicked($menuItem->getClicked()+1);
                        $this->em->persist($menuItem);
                        $this->em->flush();
                        //Add actions
                        if(!empty($menuItem->getActions())){
                            SubscriberActionHelper::addActionForSubscriber($this->em, $this->page, $menuItem->getActions(), $this->subscriber);
                        }
                        //Send Flow
                        if($mainMenu->getStatus() == true && $menuItem->getFlow() instanceof Flows){
                            $flowSend = new Flow($this->em, $menuItem->getFlow(), $this->subscriber);
                            $flowSend->sendStartStep();
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $arrayPostback
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function payloadParseButtonInputSkip($arrayPostback){
        if(isset($arrayPostback[2]) && !empty($arrayPostback[2])) {
            $flow = $this->em->getRepository("AppBundle:Flows")->find($arrayPostback[2]);
            if ($flow instanceof Flows) {
                if (isset($arrayPostback[3]) && !empty($arrayPostback[3])) {
                    $flowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow' => $flow, 'uuid' => $arrayPostback[3]]);
                    if ($flowItem instanceof FlowItems) {
                        if(!empty($flowItem->getItems())){
                            if(isset($arrayPostback[4]) && !empty($arrayPostback[4])){
                                $itemUUID = $arrayPostback[4];
                                //remove user input delay
                                $userInputDelay = $this->em->getRepository("AppBundle:UserInputDelay")->findOneBy([
                                    'page_id' => $this->page->getPageId(),
                                    'subscriber' => $this->subscriber,
                                    'flowItem' => $flowItem,
                                    'itemUuid' => $itemUUID
                                ]);
                                if($userInputDelay instanceof UserInputDelay){
                                    $this->em->remove($userInputDelay);
                                    $this->em->flush();
                                }
                                //find user input item
                                $userInputItem = null;
                                $userInputKey = null;
                                $nextItems = [];
                                foreach ($flowItem->getItems() as $key=>$item){
                                    if(isset($item['uuid']) && $item['uuid'] == $itemUUID){
                                        $userInputItem = $item;
                                        $userInputKey = $key;
                                    }
                                    elseif (!is_null($userInputKey) && !is_null($userInputItem)){
                                        $nextItems[] = $item;
                                    }
                                }

                                if(!empty($userInputItem) && is_array($userInputItem)){
                                    if(
                                        array_key_exists('type', $userInputItem) && $userInputItem['type'] == "user_input"
                                        && array_key_exists('params', $userInputItem) && !empty($userInputItem['params'])
                                        && array_key_exists('description', $userInputItem['params']) && !empty($userInputItem['params']['description'])
                                    ){
                                        //save user response
                                        $userInputResponse = new UserInputResponse($this->page->getPageId(), $this->subscriber, $flowItem, $userInputItem['params']['description']);
                                        $this->em->persist($userInputResponse);
                                        $this->em->flush();

                                        //SEND NEXT ITEMS
                                        $this->sendNextItemsAfterUserInput($flowItem, $nextItems);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Widget $widget
     * @param $user_ref
     * @return |null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendWidgetFlowByUserRef(Widget $widget, $user_ref){
        if($widget->getStatus() == true){
            $widget->setOptIn($widget->getOptIn()+1);
            $this->em->persist($widget);
            $this->em->flush();

            $flowSend = new Flow($this->em, $widget->getFlow(), $user_ref, Message::NOTIFY_REGULAR, $user_ref);
            $result = $flowSend->sendStartStep();
            if(is_array($result)){
                if(isset($result['fb_id']) && !empty($result['fb_id'])){
                    if(isset($result['recipient_id']) && !empty($result['recipient_id'])){
                        $user = $this->bot->userProfile($result['recipient_id'], 'first_name,last_name,profile_pic,locale,timezone,gender');
                        $subscriber = $this->em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$result['recipient_id']]);
                        if(!$subscriber instanceof Subscribers){
                            //Get Count User Subscribers
                            $userCountSubscribers = $this->em->getRepository("AppBundle:Subscribers")->countAllByUserId($this->page->getUser()->getId());
                            //Check limit subscribers
                            if($this->page->getUser()->getLimitSubscribers() > $userCountSubscribers){
                                //Check send request for upgrade product
                                if(
                                    $this->page->getUser()->getProduct() instanceof DigistoreProduct
                                    && !empty($this->page->getUser()->getProduct()->getQuentnUrl())
                                    && $this->page->getUser()->getProduct()->getLimitedQuentn() == $userCountSubscribers)
                                {
                                    $this->sendQuentn();
                                }
                                $subscriber = new Subscribers(
                                    $this->page->getPageId(),
                                    $result['recipient_id'],
                                    $user->getFirstName(),
                                    $user->getLastName(),
                                    $user->getGender(),
                                    $user->getLocale(),
                                    $user->getTimezone(),
                                    $user->getPicture()
                                );
                                $this->em->persist($subscriber);
                                $this->em->flush();
                            }
                            else {
                                //Check send request for upgrade product
                                if($this->page->getUser()->getProduct() instanceof DigistoreProduct && !empty($this->page->getUser()->getProduct()->getQuentnUrl())){
                                    $this->sendQuentn();
                                }

                                return null;
                            }
                        }
                        else{
                            $subscriber->setAvatar($user->getPicture());
                            if($subscriber->getLastSaveAvatar() instanceof \DateTime){
                                if($subscriber->getLastSaveAvatar()->diff(new \DateTime())->days >= 1){
                                    if(!emoty($subscriber->getAvatar())){
                                        $saveImage = new SaveImages($subscriber->getAvatar(), "uploads/".$this->page->getPageId()."/subscribers/".$subscriber->getSubscriberId()."jpg", $subscriber->getId(), 'subscriber');
                                        $this->em->persist($saveImage);
                                        $this->em->flush();
                                    }
                                    
                                }
                            }
                            $subscriber->setLastInteraction(new \DateTime());
                            $subscriber->setStatus(true);
                            $this->em->persist($subscriber);
                            $this->em->flush();
                        }

                        //SUBSCRIBER SUBSCRIBE WIDGET
                        $subscriberWidget = $this->em->getRepository("AppBundle:SubscribersWidgets")->findOneBy(['subscriber'=>$subscriber, 'widget'=>$widget]);
                        if(!$subscriberWidget instanceof SubscribersWidgets){
                            $subscriberWidget = new SubscribersWidgets($subscriber, $widget);
                            $this->em->persist($subscriberWidget);
                            $this->em->flush();
                        }
                        //SUBSCRIBER SUBSCRIBE SEQUENCE
                        if($widget->getSequence() instanceof Sequences){
                            SubscriberActionHelper::subscribeSequence($this->em, $this->page, $widget->getSequence()->getId(), [$subscriber->getId()]);
                        }
                    }
                    else{
                        $userRef = new UserRef($user_ref, $widget);
                        $this->em->persist($userRef);
                        $this->em->flush();
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param Widget $widget
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function sendWidgetFlow(Widget $widget){
        if($widget->getStatus() == true){
            $widget->setOptIn($widget->getOptIn()+1);
            $this->em->persist($widget);
            $this->em->flush();

            //SUBSCRIBER SUBSCRIBE WIDGET
            $subscriberWidget = $this->em->getRepository("AppBundle:SubscribersWidgets")->findOneBy(['subscriber'=>$this->subscriber, 'widget'=>$widget]);
            if(!$subscriberWidget instanceof SubscribersWidgets){
                $subscriberWidget = new SubscribersWidgets($this->subscriber, $widget);
                $this->em->persist($subscriberWidget);
                $this->em->flush();
            }

            //SEND FLOW WIDGET
            if($widget->getFlow() instanceof Flows){
                $flowSend = new Flow($this->em, $widget->getFlow(), $this->subscriber);
                $flowSend->sendStartStep();
                //SUBSCRIBER SUBSCRIBE SEQUENCE
                if($widget->getSequence() instanceof Sequences){
                    SubscriberActionHelper::subscribeSequence($this->em, $this->page, $widget->getSequence()->getId(), [$this->subscriber->getId()]);
                }
            }
        }
    }

    /**
     * @param $pageID
     * @param Subscribers $subscriber
     * @param $messageConversationItems
     * @param int $type
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function saveConversation($pageID, Subscribers $subscriber, $messageConversationItems, $type=1){
        $conversation = $this->em->getRepository("AppBundle:Conversation")->findOneBy(['page_id'=>$pageID, 'subscriber'=>$subscriber]);
        if(!$conversation instanceof Conversation){
            //Create Conversation
            $conversation = new Conversation($pageID, $subscriber);
            $this->em->persist($conversation);
            $this->em->flush();
        }

        //Create Conversation Message
        if(!empty($messageConversationItems)){
            $text = (isset($messageConversationItems['text'])) ? $messageConversationItems['text'] : null;
            $conversationMessage = new ConversationMessages($conversation, $messageConversationItems, $type, $text);
            $conversation->updateDateTime();
            $this->em->persist($conversation);
            $this->em->persist($conversationMessage);
            $this->em->flush();

            //PUSH SOCKET
            $this->pusher->push(
                [
                    "conversation" => [
                        "id" => $conversation->getId(),
                        "subscriber" => [
                            "id" => $subscriber->getId(),
                            "subscriber_id" => $subscriber->getSubscriberId(),
                            "firstName" => $subscriber->getFirstName(),
                            "lastName" => $subscriber->getLastName(),
                            "avatar" => $subscriber->getAvatar()
                        ],
                        "message" => [
                            'items' => $conversationMessage->getItems(),
                            'text' => $conversationMessage->getText(),
                            'type' => $conversationMessage->getType(),
                            'created' => $conversationMessage->getCreated()->format(DATE_ATOM),
                        ],
                        "updated" => $conversation->getUpdated()->format(DATE_ATOM),
                        "status" => $conversation->getStatus()
                    ],
                    "conversationMessage" => [
                        'id' => $conversationMessage->getId(),
                        'firstName' => $subscriber->getFirstName(),
                        'lastName' => $subscriber->getLastName(),
                        'avatar' => $subscriber->getAvatar(),
                        'message' => $conversationMessage->getItems(),
                        'type' => $conversationMessage->getType(),
                        'created' => $conversationMessage->getCreated()->format(DATE_ATOM)
                    ]
                ],
                "app_topic_chat",
                [
                    "page_id" => $pageID
                ]
            );
        }
    }

    /**
     * @param FlowItems $flowItem
     * @param $itemID
     * @param $buttonID
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateClickButtonForFlowItem(FlowItems $flowItem, $itemID, $buttonID){
        $nextStepID = null;
        //FIND BUTTONS
        $items = $flowItem->getItems();
        foreach ($items as $key=>$item){
            if(isset($item['uuid']) && $item['uuid'] == $itemID && isset($item['type'])){
                if ($item['type'] == 'card' || $item['type'] == 'gallery'){
                    if(isset($item['params']) && isset($item['params']['cards_array']) && !empty($item['params']['cards_array'])){
                        foreach ($item['params']['cards_array'] as $c_key=>$card){
                            if(isset($card['buttons']) && !empty($card['buttons'])){
                                foreach ($card['buttons'] as $b_key=>$button){
                                    if(isset($button['uuid']) && $button['uuid'] == $buttonID){
                                        if(isset($button['click']) && $button['click']>0){
                                            $items[$key]['params']['cards_array'][$c_key]['buttons'][$b_key]['click'] = $button['click'] + 1;
                                        }
                                        else{
                                            $items[$key]['params']['cards_array'][$c_key]['buttons'][$b_key]['click'] = 1;
                                        }
                                        $flowItem->setClicked($flowItem->getClicked() + 1);
                                        //GET NEXT STEP
                                        if(isset($button['next_step']) && !empty($button['next_step'])){
                                            $nextStepID = $button['next_step'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                elseif ($item['type'] == 'list'){
                    if(isset($item['params']['buttons']) && !empty($item['params']['buttons'])){
                        foreach ($item['params']['buttons'] as $b_key=>$button){
                            if(isset($button['uuid']) && $button['uuid'] == $buttonID){
                                if(isset($button['click']) && $button['click']>0){
                                    $items[$key]['params']['buttons'][$b_key]['click'] = $button['click'] + 1;
                                }
                                else{
                                    $items[$key]['params']['buttons'][$b_key]['click'] = 1;
                                }
                                $flowItem->setClicked($flowItem->getClicked() + 1);
                                //GET NEXT STEP
                                if(isset($button['next_step']) && !empty($button['next_step'])){
                                    $nextStepID = $button['next_step'];
                                }
                            }
                        }
                    }
                    if(isset($item['params']['list_array']) && !empty($item['params']['list_array'])){
                        foreach ($item['params']['list_array'] as $l_key=>$list){
                            if(isset($list['buttons']) && !empty($list['buttons'])){
                                foreach ($list['buttons'] as $b_key=>$button){
                                    if(isset($button['uuid']) && $button['uuid'] == $buttonID){
                                        if(isset($button['click']) && $button['click']>0){
                                            $items[$key]['params']['list_array'][$l_key]['buttons'][$b_key]['click'] = $button['click'] + 1;
                                        }
                                        else{
                                            $items[$key]['params']['list_array'][$l_key]['buttons'][$b_key]['click'] = 1;
                                        }
                                        $flowItem->setClicked($flowItem->getClicked() + 1);
                                        //GET NEXT STEP
                                        if(isset($button['next_step']) && !empty($button['next_step'])){
                                            $nextStepID = $button['next_step'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    if(isset($item['params']['buttons']) && !empty($item['params']['buttons'])){
                        foreach ($item['params']['buttons'] as $b_key=>$button){
                            if(isset($button['uuid']) && $button['uuid'] == $buttonID){
                                if(isset($button['click']) && $button['click']>0){
                                    $items[$key]['params']['buttons'][$b_key]['click'] = $button['click'] + 1;
                                }
                                else{
                                    $items[$key]['params']['buttons'][$b_key]['click'] = 1;
                                }
                                $flowItem->setClicked($flowItem->getClicked() + 1);
                                //GET NEXT STEP
                                if(isset($button['next_step']) && !empty($button['next_step'])){
                                    $nextStepID = $button['next_step'];
                                }
                            }
                        }
                    }
                    elseif (isset($item['buttons']) && !empty($item['buttons'])){
                        foreach ($item['buttons'] as $b_key=>$button){
                            if(isset($button['uuid']) && $button['uuid'] == $buttonID){
                                if(isset($button['click']) && $button['click']>0){
                                    $items[$key]['buttons'][$b_key]['click'] = $button['click'] + 1;
                                }
                                else{
                                    $items[$key]['buttons'][$b_key]['click'] = 1;
                                }
                                $flowItem->setClicked($flowItem->getClicked() + 1);
                                //GET NEXT STEP
                                if(isset($button['next_step']) && !empty($button['next_step'])){
                                    $nextStepID = $button['next_step'];
                                }
                            }
                        }
                    }
                }
            }
        }
        $flowItem->setItems($items);
        $this->em->persist($flowItem);
        $this->em->flush();

        return $nextStepID;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \MailchimpAPI\MailchimpException
     */
    public function sendQuentn(){
        try{
            $mailchimp = new Mailchimp($this->container->getParameter('mailchimp_api_key'));
            if(empty($this->page->getUser()->getQuentnId())){
                $post_params = [
                    "email_address" => $this->page->getUser()->getEmail(),
                    "status" => "subscribed",
                    "email_type" => "html",
                    "merge_fields" => [
                        "FNAME" => $this->page->getUser()->getFirstName(),
                        "LNAME" => $this->page->getUser()->getLastName()
                    ],
                ];
                $result = $mailchimp
                    ->lists($this->container->getParameter('mailchimp_list_id'))
                    ->members()
                    ->post($post_params)
                    ->deserialize(true);

                if(isset($result['id']) && !empty($result['id'])){
                    $this->page->getUser()->setQuentnId($result['id']);
                    $this->em->persist($this->page->getUser());
                    $this->em->flush();
                }
                elseif (isset($result['title']) && $result['title'] == 'Member Exists'){
                    $findMembers = $mailchimp->searchMembers()->get([
                        'query'=> $this->page->getUser()->getEmail()->getEmail()
                    ])->deserialize(true);
                    if(isset($findMembers['exact_matches']['members'][0]['id']) && !empty($findMembers['exact_matches']['members'][0]['id'])){
                        $this->page->getUser()->setQuentnId($findMembers['exact_matches']['members'][0]['id']);
                        $this->em->persist($this->page->getUser());
                        $this->em->flush();
                    }
                    elseif(isset($findMembers['full_search']['members'][0]['id']) && !empty($findMembers['full_search']['members'][0]['id'])){
                        $this->page->getUser()->setQuentnId($findMembers['full_search']['members'][0]['id']);
                        $this->em->persist($this->page->getUser());
                        $this->em->flush();
                    }
                }
            }

            if(!empty($this->page->getUser()->getQuentnId())){
                $post_params = [
                    "tags" =>[
                        [
                            "name" => $this->page->getUser()->getProduct()->getQuentnUrl(),
                            "status" => "active"
                        ]
                    ]
                ];
                $mailchimp
                    ->lists($this->container->getParameter('mailchimp_list_id'))
                    ->members($this->page->getUser()->getQuentnId())
                    ->tags()
                    ->post($post_params);
            }
        }
        catch (\Exception $e){}
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
     * @return 
     */
    public function getPusher()
    {
        return $this->pusher;
    }

    /**
     * @param $pusher
     */
    public function setPusher($pusher)
    {
        $this->pusher = $pusher;
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
     * @return MyFbBotApp|null
     */
    public function getBot()
    {
        return $this->bot;
    }

    /**
     * @param MyFbBotApp|null $bot
     */
    public function setBot($bot)
    {
        $this->bot = $bot;
    }

    /**
     * @return Subscribers|null
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * @param Subscribers|null $subscriber
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;
    }
}
