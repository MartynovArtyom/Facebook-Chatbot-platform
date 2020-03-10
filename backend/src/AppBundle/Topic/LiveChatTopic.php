<?php
/**
 * Created by PhpStorm.
 * Date: 26.12.18
 * Time: 13:30
 */

namespace AppBundle\Topic;


use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;
use Gos\Bundle\WebSocketBundle\Topic\ConnectionPeriodicTimer;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicPeriodicTimer;
use Gos\Bundle\WebSocketBundle\Topic\TopicPeriodicTimerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

class LiveChatTopic implements TopicInterface, TopicPeriodicTimerInterface, PushableTopicInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var TopicPeriodicTimer
     */
    protected $periodicTimer;

    /**
     * LiveChatTopic constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param TopicPeriodicTimer $periodicTimer
     */
    public function setPeriodicTimer(TopicPeriodicTimer $periodicTimer)
    {
        $this->periodicTimer = $periodicTimer;
    }

    /**
     * @param Topic $topic
     * @return mixed|void
     */
    public function registerPeriodicTimer(Topic $topic){}

    /**
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request){
        $topicIdArray = explode('/', $topic->getId());
        if(isset($topicIdArray[1]) && $topicIdArray[1] == 'conversation'){
            /** @var ConnectionPeriodicTimer $topicTimer */
            $topicTimer = $connection->PeriodicTimer;
            //Add periodic timer
            $topicTimer->addPeriodicTimer('openConversation', 10, function() use ($topic, $connection) {
                $pageIdArray = explode('/', $topic->getId());
                if(isset($pageIdArray[2]) && !empty($pageIdArray[2])){
                    $page_id = $pageIdArray[2];
                    $page = $this->em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$page_id]);
                    if($page instanceof Page){
                        $openConversation = $this->em->getRepository("AppBundle:Conversation")->count(['page_id'=>$page->getPageId(), 'status'=>true]);
                        $connection->event($topic->getId(), ['openConversation' => $openConversation]);
                    }
                }
            });
        }
    }

    /**
     * @param Topic        $topic
     * @param WampRequest  $request
     * @param array|string $data
     * @param string       $provider The name of pusher who push the data
     */
    public function onPush(Topic $topic, WampRequest $request, $data, $provider)
    {
        $topic->broadcast($data);
    }

    /**
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @param $event
     * @param array $exclude
     * @param array $eligible
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible){}

    /**
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request){
        $topicIdArray = explode('/', $topic->getId());
        if(isset($topicIdArray[1]) && $topicIdArray[1] == 'conversation'){
            /** @var ConnectionPeriodicTimer $topicTimer */
            $topicTimer = $connection->PeriodicTimer;
            //Cancel periodic timer
            $topicTimer->cancelPeriodicTimer('openConversation');
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app.topic.chat';
    }
}
