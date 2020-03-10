<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 16:55
 */

namespace AppBundle\Flows\Type;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Flows\FlowsItem;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;

/**
 * Class RandomizerType
 * @package AppBundle\Flows\Type
 */
class RandomizerType implements FlowsTypeInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var FlowItems
     */
    protected $flowItem;

    /**
     * @var string|Subscribers
     */
    protected $subscriber;

    /**
     * @var string
     */
    protected $typePush = null;

    /**
     * @var null
     */
    protected $user_ref = null;

    /**
     * @var null
     */
    protected $tag = null;

    /**
     * @var string
     */
    protected $messageType = Message::TYPE_RESPONSE;

    /**
     * RandomizerType constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @param $subscriber
     * @param string $typePush
     * @param null $user_ref
     * @param null $tag
     * @param string $messageType
     */
    public function __construct(EntityManager $em, Page $page, FlowItems $flowItem, $subscriber, $typePush = Message::NOTIFY_REGULAR, $user_ref = null, $tag = null, $messageType = Message::TYPE_RESPONSE)
    {
        $this->em = $em;
        $this->page = $page;
        $this->flowItem = $flowItem;
        $this->subscriber = $subscriber;
        $this->typePush = $typePush;
        $this->user_ref = $user_ref;
        $this->tag = $tag;
        $this->messageType = $messageType;
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function send()
    {
        if(!empty($this->flowItem->getItems())) {
            $items = $this->flowItem->getItems()[0];
            if(isset($items['randomData']) && !empty($items['randomData'])){
                $randomItems = [];
                foreach ($items['randomData'] as $item) {
                    if(isset($item['next_step']) && !empty($item['next_step']) && isset($item['random_leter']) && !empty($item['random_leter']) && isset($item['value']) && !empty($item['value'])){
                        $randomItems[] = [
                            'next_step' => $item['next_step'],
                            'value' => $item['value']
                        ];
                    }
                }

                if(!empty($randomItems)){
                    $randomIndex = $this->getRandomIndex($randomItems, 'value');
                    if(isset($randomItems[$randomIndex]) && isset($randomItems[$randomIndex]['next_step']) && !empty($randomItems[$randomIndex]['next_step'])){
                        $randomNextStepFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$this->flowItem->getFlow(), 'uuid'=>$randomItems[$randomIndex]['next_step']]);
                        if($randomNextStepFlowItem instanceof FlowItems){
                            $flowsItem = new FlowsItem($this->em, $randomNextStepFlowItem, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                            return $flowsItem->send();
                        }
                    }
                }
            }
        }

        return ['result' => false];
    }

    /**
     * @return array|mixed
     */
    public function getJSON()
    {
        return [
            'result' => false,
            'adsJSON' => "Der Start Sequenz sollte Sende Nachricht sein"
        ];
    }

    /**
     * @param $data
     * @param string $column
     * @return int
     */
    public function getRandomIndex($data, $column = 'value') {
        $rand = mt_rand(1, array_sum(array_column($data, $column)));
        $cur = $prev = 0;
        for ($i = 0; $i < count($data); ++$i) {
            $prev += $i != 0 ? $data[$i-1][$column] : 0;
            $cur += $data[$i][$column];
            if ($rand > $prev && $rand <= $cur) {
                return $i;
            }
        }
        return -1;
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
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return FlowItems
     */
    public function getFlowItem()
    {
        return $this->flowItem;
    }

    /**
     * @param FlowItems $flowItem
     */
    public function setFlowItem($flowItem)
    {
        $this->flowItem = $flowItem;
    }

    /**
     * @return Subscribers|string
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * @param Subscribers|string $subscriber
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @return string
     */
    public function getTypePush()
    {
        return $this->typePush;
    }

    /**
     * @param string $typePush
     */
    public function setTypePush($typePush)
    {
        $this->typePush = $typePush;
    }

    /**
     * @return null
     */
    public function getUserRef()
    {
        return $this->user_ref;
    }

    /**
     * @param null $user_ref
     */
    public function setUserRef($user_ref)
    {
        $this->user_ref = $user_ref;
    }

    /**
     * @return null
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param null $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * @param string $messageType
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;
    }
}
