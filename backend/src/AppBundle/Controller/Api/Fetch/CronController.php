<?php
/**
 * Created by PhpStorm.
 * Date: 21.12.18
 * Time: 15:15
 */

namespace AppBundle\Controller\Api\Fetch;


use AppBundle\Entity\Broadcast;
use AppBundle\Entity\CommentDelay;
use AppBundle\Entity\Conversation;
use AppBundle\Entity\ConversationMessages;
use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Page;
use AppBundle\Entity\SaveImages;
use AppBundle\Entity\ScheduleBroadcast;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\SequencesItems;
use AppBundle\Entity\SubscriberDelay;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\SubscribersSequences;
use AppBundle\Entity\User;
use AppBundle\Entity\Widget;
use AppBundle\Flows\Flow;
use AppBundle\Flows\FlowsItem;
use AppBundle\Helper\Message\GraphMessage;
use AppBundle\Helper\MyFbBotApp;
use AppBundle\Helper\OpenGraph;
use AppBundle\Helper\OtherHelper;
use AppBundle\Webhooks\FBFeed;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use pimax\Messages\Message;
use pimax\UserProfile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;


/**
 * Class CronController
 * @package AppBundle\Controller\Api\Fetch
 *
 * @Rest\Route("/cron")
 */
class CronController extends FOSRestController
{
    /**
     * CronController constructor.
     */
    public function __construct()
    {
        ini_set('max_execution_time', 0);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/email_send")
     * @SWG\Get(path="/v2/fetch/cron/email_send",
     *   tags={"CRON"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function emailSendAction(Request $request){
        shell_exec('php ../bin/console swiftmailer:spool:send --env=prod');

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/send_delay_comment")
     * @SWG\Get(path="/v2/fetch/cron/send_delay_comment",
     *   tags={"CRON"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function sendDelayCommentAction(Request $request){
        set_time_limit(0);
        try {
            $em = $this->getDoctrine()->getManager();
            $delayComments = $em->getRepository("AppBundle:CommentDelay")->findNeedSendNow();
            if(!empty($delayComments)){
                foreach ($delayComments as $delayComment){
                    if($delayComment instanceof CommentDelay){
                        if($delayComment->getWidget() instanceof Widget && $delayComment->getWidget()->getStatus() == true){
                            $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$delayComment->getWidget()->getPageId(), 'status'=>true]);
                            if($page instanceof Page){
                                $fbFeed = new FBFeed($em, $this->container);
                                $fbFeed->setPage($page);
                                $fbFeed->sendPrivateReply($delayComment->getWidget(), $delayComment->getCommentId(), $delayComment->getWidget()->getOptions(), $delayComment->getRecipient());
                            }
                        }

                        $em->remove($delayComment);
                        $em->flush();
                    }
                }
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('cron_request.txt', "SEND COMMENT:\n".$e->getMessage()."\n\n");
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/save_image")
     * @SWG\Get(path="/v2/fetch/cron/save_image",
     *   tags={"CRON"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function saveImageAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $saveImages = $em->getRepository("AppBundle:SaveImages")->findAll();
        if(!empty($saveImages)){
            foreach ($saveImages as $saveImage){
                if($saveImage instanceof SaveImages){
                    if(OtherHelper::saveImage($saveImage->getUrl(), $saveImage->getPath())){
                        $url = $request->getSchemeAndHttpHost()."/".$saveImage->getPath();
                        $object = null;
                        switch ($saveImage->getItemType()){
                            case 'user':
                                $object = $em->getRepository("AppBundle:User")->find($saveImage->getItemId());
                                if($object instanceof User){
                                    $object->setAvatar($url);
                                    $em->persist($object);
                                    $em->flush();
                                }
                                break;
                            case 'page':
                                $object = $em->getRepository("AppBundle:Page")->find($saveImage->getItemId());
                                if($object instanceof Page){
                                    $object->setAvatar($url);
                                    $em->persist($object);
                                    $em->flush();
                                }
                                break;
                            case 'subscriber':
                                $object = $em->getRepository("AppBundle:Subscribers")->find($saveImage->getItemId());
                                if($object instanceof Subscribers){
                                    $object->setAvatar($url);
                                    $object->setLastSaveAvatar(new \DateTime());
                                    $em->persist($object);
                                    $em->flush();
                                }
                                break;
                        }
                    }
                    $em->remove($saveImage);
                    $em->flush();
                }
            }
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/auto_posting")
     * @SWG\Get(path="/v2/fetch/cron/auto_posting",
     *   tags={"CRON"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function autoPostingAction(Request $request){
        $fs = new Filesystem();
        try {
            $em = $this->getDoctrine()->getManager();
            $autoPostings = $em->getRepository("AppBundle:Autoposting")->findBy(['status' => true]);
            if (!empty($autoPostings)) {
                $fb = new \Facebook\Facebook([
                    'app_id' => $this->container->getParameter('facebook_id'),
                    'app_secret' => $this->container->getParameter('facebook_secret'),
                    'default_graph_version' => 'v3.3'
                ]);
                $messages = [];
                $now = new \DateTime();
                $converFallback = [];
                //FIND NEW FEED
                foreach ($autoPostings as $autoPosting) {
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id' => $autoPosting->getPageId()]);
                    if ($page instanceof Page && $page->getStatus() == true) {
                        if ($autoPosting->getType() == 4) {
                            try {
                                $response = $fb->get($autoPosting->getUrl() . '?access_token=' . $page->getAccessToken());
                            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                                break;
                            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                                break;
                            }

                            if ($response->getHttpStatusCode() == 200) {
                                $feeds = $response->getDecodedBody();
                                if (isset($feeds['data']) && !empty($feeds['data'])) {
                                    foreach ($feeds['data'] as $feed) {
                                        if (!isset($feed['story'])) {
                                            $dateCreateFeed = new \DateTime($feed['created_time']);
                                            $dateCreateFeed->setTimezone($now->getTimezone());
                                            if ($dateCreateFeed->format('Y-m-d H:i') <= $now->format('Y-m-d H:i') && $autoPosting->getLastSeen()->format('Y-m-d H:i') < $dateCreateFeed->format('Y-m-d H:i')
                                            ) {
                                                $subscribers = $em->getRepository("AppBundle:Subscribers")->getSubscribersByPageId($autoPosting->getPageId(), $autoPosting->getTargeting());
                                                if (!empty($subscribers)) {
                                                    $id_post = explode("_", $feed['id']);
                                                    $fallbackMessage = OpenGraph::fetch("https://www.facebook.com/" . $id_post[0] . "/posts/" . $id_post[1]);
                                                    foreach ($subscribers as $subscriber) {
                                                        if ($subscriber instanceof Subscribers && $subscriber->getStatus() == true) {
                                                            $typePush = Message::NOTIFY_REGULAR;
                                                            if ($autoPosting->getTypePush() == 2) {
                                                                $typePush = Message::NOTIFY_SILENT_PUSH;
                                                            } else if ($autoPosting->getTypePush() == 3) {
                                                                $typePush = Message::NOTIFY_NO_PUSH;
                                                            }
                                                            $messages[$autoPosting->getPageId()][] = new GraphMessage($subscriber->getSubscriberId(), "https://www.facebook.com/" . $id_post[0] . "/posts/" . $id_post[1], $typePush);
                                                            $converFallback[] = ['page_id' => $autoPosting->getPageId(), 'subscriber_id' => $subscriber->getSubscriberId(), 'fallback' => $fallbackMessage];
                                                        }
                                                    }
                                                }
                                            } else {
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $feeds = OtherHelper::getFeed($autoPosting->getUrl());
                            if ($feeds) {
                                $feeds = new \SimpleXMLElement($feeds);
                                if (isset($feeds->entry) && !empty($feeds->entry)) {
                                    foreach ($feeds->entry as $feed) {
                                        $dateCreateFeed = new \DateTime($feed->published);
                                        $dateCreateFeed->setTimezone($now->getTimezone());
                                        if ($dateCreateFeed->format('Y-m-d H:i') <= $now->format('Y-m-d H:i') && $autoPosting->getLastSeen()->format('Y-m-d H:i') < $dateCreateFeed->format('Y-m-d H:i')) {
                                            if (isset($feed->link['href']) && !empty($feed->link['href'])) {
                                                $attr = (array)$feed->link->attributes()->href;
                                                if (isset($attr[0]) && !empty($attr[0])) {
                                                    $subscribers = $em->getRepository("AppBundle:Subscribers")->getSubscribersByPageId($autoPosting->getPageId(), $autoPosting->getTargeting());
                                                    if (!empty($subscribers)) {
                                                        $fallbackMessage = OpenGraph::fetch($attr[0]);
                                                        foreach ($subscribers as $subscriber) {
                                                            if ($subscriber instanceof Subscribers && $subscriber->getStatus() == true) {
                                                                $typePush = Message::NOTIFY_REGULAR;
                                                                if ($autoPosting->getTypePush() == 2) {
                                                                    $typePush = Message::NOTIFY_SILENT_PUSH;
                                                                } else if ($autoPosting->getTypePush() == 3) {
                                                                    $typePush = Message::NOTIFY_NO_PUSH;
                                                                }
                                                                $messages[$autoPosting->getPageId()][] = new GraphMessage($subscriber->getSubscriberId(), $attr[0], $typePush);
                                                                $converFallback[] = ['page_id' => $autoPosting->getPageId(), 'subscriber_id' => $subscriber->getSubscriberId(), 'fallback' => $fallbackMessage];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            break;
                                        }
                                    }
                                } elseif (isset($feeds->channel) && !empty($feeds->channel) && isset($feeds->channel->item) && !empty($feeds->channel->item)) {
                                    foreach ($feeds->channel->item as $feed) {
                                        $dateCreateFeed = new \DateTime($feed->pubDate);
                                        $dateCreateFeed->setTimezone($now->getTimezone());
                                        if ($dateCreateFeed->format('Y-m-d H:i') <= $now->format('Y-m-d H:i') && $autoPosting->getLastSeen()->format('Y-m-d H:i') < $dateCreateFeed->format('Y-m-d H:i')) {
                                            $attr = (array)$feed->link;
                                            if (isset($attr[0]) && !empty($attr[0])) {
                                                $subscribers = $em->getRepository("AppBundle:Subscribers")->getSubscribersByPageId($autoPosting->getPageId(), $autoPosting->getTargeting());
                                                if (!empty($subscribers)) {
                                                    $fallbackMessage = OpenGraph::fetch($attr[0]);
                                                    foreach ($subscribers as $subscriber) {
                                                        if ($subscriber instanceof Subscribers) {
                                                            $typePush = Message::NOTIFY_REGULAR;
                                                            if ($autoPosting->getTypePush() == 2) {
                                                                $typePush = Message::NOTIFY_SILENT_PUSH;
                                                            } else if ($autoPosting->getTypePush() == 3) {
                                                                $typePush = Message::NOTIFY_NO_PUSH;
                                                            }
                                                            $messages[$autoPosting->getPageId()][] = new GraphMessage($subscriber->getSubscriberId(), $attr[0], $typePush);
                                                            $converFallback[] = ['page_id' => $autoPosting->getPageId(), 'subscriber_id' => $subscriber->getSubscriberId(), 'fallback' => $fallbackMessage];
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                //UPDATE LAST SEEN FEED
                $em->getRepository("AppBundle:Autoposting")->updateLastSeenAll($now);
                //SEND
                if (!empty($messages)) {
                    foreach ($messages as $page_id => $items) {
                        $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id' => $page_id]);
                        if ($page instanceof Page) {
                            $bot = new MyFbBotApp($page->getAccessToken());
                            if (count($items) > 50) {
                                $chunkMessages = array_chunk($items, 50);
                                foreach ($chunkMessages as $item) {
                                    $bot->batch($item);
                                }
                            } else {
                                $bot->batch($items);
                            }
                        }
                    }
                }
                //SAVE CONVERSATION MESSAGE
                if (!empty($converFallback)) {
                    foreach ($converFallback as $fallback) {
                        if (isset($fallback['page_id']) && isset($fallback['subscriber_id']) && isset($fallback['fallback'])) {
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id' => $fallback['page_id'], 'subscriber_id' => $fallback['subscriber_id']]);
                            if ($subscriber instanceof Subscribers) {
                                $conversation = $em->getRepository("AppBundle:Conversation")->findOneBy(['page_id' => $fallback['page_id'], 'subscriber' => $subscriber]);
                                if (!$conversation instanceof Conversation) {
                                    $conversation = new Conversation($fallback['page_id'], $subscriber);
                                    $em->persist($conversation);
                                    $em->flush();
                                }
                                $conversationMessage = new ConversationMessages($conversation, ['fallback' => $fallback['fallback']], 2);
                                $conversation->updateDateTime();
                                $em->persist($conversation);
                                $em->persist($conversationMessage);
                                $em->flush();
                            }
                        }
                    }
                }
            }
        }
        catch (\Exception $e){
            $fs->appendToFile('cron_request.txt', "AUTO POSTING ERROR:\n".$e->getMessage()."\n\n");
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/sequences")
     * @SWG\Get(path="/v2/fetch/cron/sequences",
     *   tags={"CRON"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function sequencesAction(Request $request){
        set_time_limit(0);
        try {
            $em = $this->getDoctrine()->getManager();
            $sequenceFlows = $this->getSequenceFlowForSend($em);
            if (!empty($sequenceFlows)) {
                foreach ($sequenceFlows as $page_id => $items) {
                    if (!empty($items)) {
                        $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id' => $page_id]);
                        if (!$page instanceof Page) {
                            if ($items[0]['flow'] instanceof Flows) {
                                $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id' => $items[0]['flow']->getPageId()]);
                            }
                        }
                        if ($page instanceof Page && $page->getStatus() == true) {
                            foreach ($items as $item) {
                                if (isset($item['subscriber']) && $item['subscriber'] instanceof Subscribers && isset($item['flow']) && $item['flow'] instanceof Flows) {
                                    $flowSend = new Flow($em, $item['flow'], $item['subscriber']);
                                    $flowSend->sendStartStep();
                                }
                            }
                        }
                    }
                }
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('cron_request.txt', "SEQUENCES ERROR:\n".$e->getMessage()."\n\n");
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/broadcast")
     * @SWG\Get(path="/v2/fetch/cron/broadcast",
     *   tags={"CRON"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function broadcastAction(Request $request){
        $fs = new Filesystem();
        try {
            $em = $this->getDoctrine()->getManager();
            $broadcasts = $em->getRepository("AppBundle:Broadcast")->findBy(['status' => 2]);
            if (!empty($broadcasts)) {
                foreach ($broadcasts as $broadcast) {
                    if ($broadcast instanceof Broadcast) {
                        $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id' => $broadcast->getPageId(), 'status' => true]);
                        if ($page instanceof Page) {
                            $now = new \DateTime();
                            $nowPlus1 = new \DateTime("+1 minutes");
                            if ($now->format("Y-m-d H:i") == $broadcast->getCreated()->format("Y-m-d H:i")) {
                                $broadcast->setStatus(3);
                                $em->persist($broadcast);
                                $em->flush();
                            }

                            if ($nowPlus1->format("Y-m-d H:i") == $broadcast->getCreated()->format("Y-m-d H:i")) {
                                $targeting = [
                                    'system'=> [],
                                    'tags'=> [],
                                    'widgets'=> [],
                                    'sequences'=> [],
                                    'customFields'=> [],
                                ];
                                if(!empty($broadcast->getTargeting())){
                                    foreach ($broadcast->getTargeting() as $target){
                                        if(isset($target['conditionType']) && !empty($target['conditionType'])){
                                            switch ($target['conditionType']){
                                                case 'tag':
                                                    $targeting['tags'][] = $target;
                                                    break;
                                                case 'widget':
                                                    $targeting['widgets'][] = $target;
                                                    break;
                                                case 'sequence':
                                                    $targeting['sequences'][] = $target;
                                                    break;
                                                case 'system':
                                                    $targeting['system'][] = $target;
                                                    break;
                                                case 'customField':
                                                    $targeting['customFields'][] = $target;
                                                    break;
                                            }
                                        }
                                    }
                                }
                                $subscribers = $em->getRepository("AppBundle:Subscribers")->getSubscribersByPageId($page->getPageId(), $targeting);
                                if (!empty($subscribers)) {
                                    foreach ($subscribers as $subscriber) {
                                        if ($subscriber instanceof Subscribers) {
                                            $diff = $nowPlus1->diff($subscriber->getLastInteraction());
                                            if ($broadcast->getType() == 1 || $diff->days == 0) {
                                                $scheduleBroadcast = new ScheduleBroadcast($broadcast, $subscriber);
                                                $em->persist($scheduleBroadcast);
                                                $em->flush();
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
        catch (\Exception $e){
            $fs->appendToFile('cron_request.txt', "BROADCAST ERROR:\n".$e->getMessage()."\n\n");
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/sendBroadcast")
     * @SWG\Get(path="/v2/fetch/cron/sendBroadcast",
     *   tags={"CRON"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function sendBroadcastAction(Request $request){
        set_time_limit(0);
        try{
            $em = $this->getDoctrine()->getManager();
            $scheduledBroadcasts = $em->getRepository("AppBundle:ScheduleBroadcast")->findBy([], null, 250);
            $needSend = [];
            if(!empty($scheduledBroadcasts)){
                foreach ($scheduledBroadcasts as $scheduleBroadcast){
                    if($scheduleBroadcast instanceof ScheduleBroadcast){
                        if($scheduleBroadcast->getBroadcast() instanceof Broadcast && $scheduleBroadcast->getSubscriber() instanceof Subscribers ){
                            $needSend[] = [
                                'broadcast' => $scheduleBroadcast->getBroadcast(),
                                'subscriber' => $scheduleBroadcast->getSubscriber()
                            ];
                        }
                        $em->remove($scheduleBroadcast);
                        $em->flush();
                    }
                }
            }

            if(!empty($needSend)){
                foreach ($needSend as $item){
                    if(isset($item['broadcast']) && isset($item['subscriber'])){
                        $broadcast = $item['broadcast'];
                        $subscriber = $item['subscriber'];
                        if($broadcast instanceof Broadcast && $subscriber instanceof Subscribers){
                            if($broadcast->getFlow() instanceof Flows && $subscriber->getStatus() == true){
                                $typePush = Message::NOTIFY_REGULAR;
                                if ($broadcast->getPushType() == 2) {
                                    $typePush = Message::NOTIFY_SILENT_PUSH;
                                } else if ($broadcast->getPushType() == 3) {
                                    $typePush = Message::NOTIFY_NO_PUSH;
                                }
                                $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$broadcast->getPageId(), 'status'=>true]);
                                if($page instanceof Page){
                                    $flowSend = new Flow(
                                        $em,
                                        $broadcast->getFlow(),
                                        $subscriber,
                                        $typePush,
                                        null,
                                        ($broadcast->getType() == 1) ? $broadcast->getTag() : null,
                                        ($broadcast->getType() == 1) ? Message::TYPE_MESSAGE_TAG : Message::TYPE_RESPONSE
                                    );
                                    $flowSend->sendStartStep();
                                }
                            }
                        }
                    }
                }
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('cron_request.txt', "SEND BROADCAST ERROR:\n".$e->getMessage()."\n\n");
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/sendDelay")
     * @SWG\Get(path="/v2/fetch/cron/sendDelay",
     *   tags={"CRON"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function sendDelayAction(Request $request){
        set_time_limit(0);
        $fs = new Filesystem();
        try{
            $em = $this->getDoctrine()->getManager();
            $subscriberDelayObjects = $em->getRepository("AppBundle:SubscriberDelay")->findNeedSendNow();
            if(!empty($subscriberDelayObjects)){
                $needSend = [];
                foreach ($subscriberDelayObjects as $subscriberDelay){
                    if($subscriberDelay instanceof SubscriberDelay){
                        if($subscriberDelay->getFlowItem() instanceof FlowItems && $subscriberDelay->getSubscriber() instanceof Subscribers){
                            $needSend[] = [
                                'flowItem' => $subscriberDelay->getFlowItem(),
                                'subscriber' => $subscriberDelay->getSubscriber()
                            ];

                            $em->remove($subscriberDelay);
                            $em->flush();
                        }
                    }
                }

                if(!empty($needSend)) {
                    foreach ($needSend as $item) {
                        if(isset($item['flowItem']) && $item['flowItem'] instanceof FlowItems
                        && isset($item['subscriber']) && $item['subscriber'] instanceof Subscribers && $item['subscriber']->getStatus() == true ){
                            $flowItemSend = new FlowsItem($em, $item['flowItem'], $item['subscriber']);
                            $flowItemSend->send();
                        }
                    }
                }
            }

        }
        catch (\Exception $e){
            $fs->appendToFile('cron_request.txt', "SEND DELAY ERROR:\n".$e->getMessage()."\n\n");
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @param $type
     * @return Response
     *
     * TYPE: 1 = Daily 2 = Weekly 3 = Monthly
     * @Rest\Get("/report/{type}")
     * @SWG\Get(path="/v2/fetch/cron/report/{type}",
     *   tags={"CRON"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function reportAction(Request $request, $type){
        $fs = new Filesystem();
        try{
            if(in_array($type,[1,2,3])){
                $title = 'ChatBo Tagesbericht';
                if($type == 2){
                    $title = 'ChatBo Wochenbericht';
                }
                elseif ($type == 3){
                    $title = 'ChatBo Monatsbericht';
                }
                $em = $this->getDoctrine()->getManager();
                $notifications = $em->getRepository("AppBundle:Notification")->findBy(['status'=>true, 'type'=>$type]);
                if(!empty($notifications)){
                    foreach ($notifications as $notification){
                        if($notification instanceof Notification){
                            $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$notification->getPageId(), 'status'=>true]);
                            if($page instanceof Page){
                                $newSubscribers = $em->getRepository("AppBundle:Subscribers")->getNewCountSubscribers($page->getPageId(), $type);
                                $totalSubscribers = $em->getRepository("AppBundle:Subscribers")->findBy(['page_id'=>$page->getPageId(), 'status'=>true]);
                                $message = (new \Swift_Message($title))
                                    ->setFrom('news@chatbo.de', 'ChatBo')
                                    ->setTo($notification->getEmail())
                                    ->setBody(
                                        $this->renderView('emails/report.html.twig',[
                                            'title' => $title,
                                            'user_name' => $notification->getUser()->getFirstName(),
                                            'page' => $page,
                                            'newSubscribers' => (isset($newSubscribers['newSubscriber']) && intval($newSubscribers['newSubscriber'])>0) ? intval($newSubscribers['newSubscriber']) : 0,
                                            'totalSubscribers' => (count($totalSubscribers)>0) ? count($totalSubscribers) : 0,
                                            'link_broadcasts' => 'https://app.chatbo.de/'.$page->getPageId().'/broadcasts',
                                            'link_contacts' => 'https://app.chatbo.de/'.$page->getPageId().'/audience',
                                            'type' => $type
                                        ]),
                                        'text/html'
                                    );
                                try{
                                    $this->get('mailer')->send($message);
                                }catch(\Swift_TransportException $e){
                                    $fs->appendToFile('cron_request.txt', "REPORT TYPE(".$type.") SEND EMAIL ERROR:\n".$e->getMessage()."\n\n");
                                }
                            }
                        }
                    }
                }
            }
        }
        catch (\Exception $e){
            $fs->appendToFile('cron_request.txt', "REPORT TYPE(".$type.") ERROR:\n".$e->getMessage()."\n\n");
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/clearStarterVersion")
     * @SWG\Get(path="/v2/fetch/cron/clearStarterVersion",
     *   tags={"CRON"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function clearStarterVersionAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->getAllForTrialEnd();
        foreach ($users as $user){
            if($user instanceof User){
                if(!$user->getProduct() instanceof DigistoreProduct || $user->getProduct()->getId() == 12){
                    $user->setProduct(null);
                    $user->setLimitSubscribers(0);
                }
                $user->setTrialEnd(null);
                $em->persist($user);
                $em->flush();
            }
        }


        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param EntityManager $em
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function getSequenceFlowForSend(EntityManager $em){
        $result = [];
        $sequences = $em->getRepository("AppBundle:Sequences")->findAll();
        if(!empty($sequences)){
            foreach ($sequences as $sequence){
                if($sequence instanceof Sequences){
                    $subscribersSequence = $em->getRepository("AppBundle:SubscribersSequences")->getAllWithoutLastStageNotProcessed($sequence);
                    if(!empty($subscribersSequence)){
                        foreach ($subscribersSequence as $subscriberSequence){
                            if($subscriberSequence instanceof SubscribersSequences && $subscriberSequence->getSubscriber() instanceof Subscribers && $subscriberSequence->getSubscriber()->getStatus() == true) {
                                $sequenceItem = $em->getRepository("AppBundle:SequencesItems")->findOneBy(['sequence'=>$sequence, 'number'=>$subscriberSequence->getStage()]);
                                if(!$sequenceItem instanceof SequencesItems || $sequenceItem->getStatus() == false) {
                                    $sequenceItem = $em->getRepository("AppBundle:SequencesItems")->getNextSequencesItem($subscriberSequence->getSequence(), $subscriberSequence->getStage());
                                }
                                if($sequenceItem instanceof SequencesItems){
                                    $delay = $sequenceItem->getDelay();
                                    if(isset($delay['type'])){
                                        if($delay['type'] == 'immediately'){
                                            $result[$sequenceItem->getSequence()->getPageId()][] = ['flow'=>$sequenceItem->getFlow(), 'subscriber'=>$subscriberSequence->getSubscriber()];
                                            $subscriberSequence->setStage($sequenceItem->getNumber()+1);
                                            $subscriberSequence->setLastSendDate(new \DateTime());
                                            $em->persist($subscriberSequence);
                                            $em->flush();
                                        }
                                        else{
                                            if(isset($delay['value'])){
                                                $lastSend = $subscriberSequence->getLastSendDate();
                                                $lastSend->modify('+'.$delay['value'].' '.$delay['type']);
                                                $now = new \DateTime();
                                                if($now->format("Y-m-d H:i") == $lastSend->format("Y-m-d H:i")){
                                                    $result[$sequenceItem->getSequence()->getPageId()][] = ['flow'=>$sequenceItem->getFlow(), 'subscriber'=>$subscriberSequence->getSubscriber()];
                                                    $subscriberSequence->setStage($sequenceItem->getNumber()+1);
                                                    $subscriberSequence->setLastSendDate($now);
                                                    $em->persist($subscriberSequence);
                                                    $em->flush();
                                                }
                                            }
                                            else{
                                                $subscriberSequence->setStage('last');
                                                $em->persist($subscriberSequence);
                                                $em->flush();
                                            }
                                        }
                                    }
                                }
                                else{
                                    $subscriberSequence->setStage('last');
                                    $em->persist($subscriberSequence);
                                    $em->flush();
                                }
                            }
                            else{
                                $subscriberSequence->setStage('last');
                                $em->persist($subscriberSequence);
                                $em->flush();
                            }
                        }
                    }
                }
            }
        }

        if(!empty($result)){
            $r = $this->getSequenceFlowForSend($em);
            if(!empty($r)){
                $resultAll = array_merge_recursive($result, $r);
            }
            else{
                $resultAll = $result;
            }
        }
        else{
            $resultAll = [];
        }
        return $resultAll;
    }
}
