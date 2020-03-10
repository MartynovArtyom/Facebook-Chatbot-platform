<?php


namespace AppBundle\Pages;


use AppBundle\Entity\CustomFields;
use AppBundle\Entity\CustomRefParameter;
use AppBundle\Entity\DefaultReply;
use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Folders;
use AppBundle\Entity\Keywords;
use AppBundle\Entity\MainMenu;
use AppBundle\Entity\MainMenuDraft;
use AppBundle\Entity\MainMenuItems;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\SequencesItems;
use AppBundle\Entity\Tag;
use AppBundle\Entity\WelcomeMessage;
use AppBundle\Entity\Widget;
use AppBundle\Flows\Flow;
use AppBundle\Helper\MyFbBotApp;
use AppBundle\Helper\WidgetHelper;
use AppBundle\MainMenu\MenuMain;
use Doctrine\ORM\EntityManager;
use Lcobucci\JWT\Signer\Key;

class ClonePage implements ClonePageInterface
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
     * @var Page
     */
    protected $clonePage;

    /**
     * @var Folders | null
     */
    protected $folder = null;

    /**
     * @var array
     */
    protected $flowIds = [];

    /**
     * @var array
     */
    protected $customFields = [];

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var array
     */
    protected $sequences = [];

    /**
     * @var array
     */
    protected $widgets = [];

    /**
     * @var array
     */
    protected $keywords = [];


    /**
     * ClonePage constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param Page $clonePage
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function __construct(EntityManager $em, Page $page, Page $clonePage)
    {
        $this->em = $em;
        $this->page = $page;
        $this->clonePage = $clonePage;
        $this->folder = new Folders($clonePage->getPageId(), 'Klon von '.$page->getTitle());
        $this->em->persist($this->folder);
        $this->em->flush();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cloneAll(){
        //Clone All Flows
        $this->cloneAllFlows();
        //Clone All Sequences
        $this->cloneAllSequences();
        //Clone All Widgets
        $this->cloneAllWidgets();
        //Clone All Keywords
        $this->cloneAllKeywords();
        //Clone Welcome Message
        $this->cloneWelcomeMessage();
        //Clone Default Reply
        $this->cloneDefaultReply();
        //Clone Main Menu
        $this->cloneMainMenu();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cloneAllFlows(){
        $flows = $this->em->getRepository("AppBundle:Flows")->findBy(['page_id'=>$this->page->getPageId(), 'status'=>true]);
        if(!empty($flows)){
            foreach ($flows as $flow){
                if($flow instanceof Flows){
                    $this->replaceFlow($flow);
                }
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cloneAllSequences(){
        $sequences = $this->em->getRepository("AppBundle:Sequences")->findBy(['page_id'=>$this->page->getPageId()]);
        if(!empty($sequences)){
            foreach ($sequences as $sequence){
                if($sequence instanceof Sequences){
                    $this->replaceSequence($sequence);
                }
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cloneAllWidgets(){
        $widgets = $this->em->getRepository("AppBundle:Widget")->findBy(['page_id'=>$this->page->getPageId()]);
        if(!empty($widgets)){
            foreach ($widgets as $widget){
                if($widget instanceof Widget){
                    $this->replaceWidget($widget);
                }
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cloneAllKeywords(){
        //Clone Main Keywords
        $this->cloneMainKeywords();
        //Clone User Keywords
        $this->cloneUserKeywords();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cloneWelcomeMessage(){
        $welcomeMessage = $this->em->getRepository("AppBundle:WelcomeMessage")->findOneBy(['page_id'=>$this->page->getPageId()]);
        if($welcomeMessage instanceof WelcomeMessage && $welcomeMessage->getFlow() instanceof Flows){
            $newFlow = $this->replaceFlow($welcomeMessage->getFlow());
            $cloneWelcomeMessage = $this->em->getRepository("AppBundle:WelcomeMessage")->findOneBy(['page_id'=>$this->clonePage->getPageId()]);
            if($cloneWelcomeMessage instanceof WelcomeMessage){
                $cloneWelcomeMessage->setFlow($newFlow);
                $cloneWelcomeMessage->setStatus($welcomeMessage->getStatus());
            }
            else{
                $cloneWelcomeMessage = new WelcomeMessage($this->clonePage->getPageId(), $newFlow, $welcomeMessage->getStatus());
            }
            $this->em->persist($cloneWelcomeMessage);
            $this->em->flush();
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cloneDefaultReply(){
        $defaultReply = $this->em->getRepository("AppBundle:DefaultReply")->findOneBy(['page_id'=>$this->page->getPageId()]);
        if($defaultReply instanceof DefaultReply && $defaultReply->getFlow() instanceof Flows){
            $newFlow = $this->replaceFlow($defaultReply->getFlow());
            $cloneDefaultReply = $this->em->getRepository("AppBundle:DefaultReply")->findOneBy(['page_id'=>$this->clonePage->getPageId()]);
            if($cloneDefaultReply instanceof DefaultReply){
                $cloneDefaultReply->setFlow($newFlow);
                $cloneDefaultReply->setStatus($defaultReply->getStatus());
                $cloneDefaultReply->setType($defaultReply->getType());
            }
            else{
                $cloneDefaultReply = new DefaultReply($this->clonePage->getPageId(), $newFlow, $defaultReply->getStatus(), $defaultReply->getType());
            }
            $this->em->persist($cloneDefaultReply);
            $this->em->flush();
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function cloneMainMenu(){
        $mainMenu = $this->em->getRepository("AppBundle:MainMenu")->findOneBy(['page_id'=>$this->page->getPageId()]);
        if($mainMenu instanceof MainMenu){
            $cloneMainMenu = $this->em->getRepository("AppBundle:MainMenu")->findOneBy(['page_id'=>$this->clonePage->getPageId()]);
            if(!$cloneMainMenu instanceof MainMenu){
                $cloneMainMenu = new MainMenu($this->clonePage->getPageId(), $mainMenu->getStatus(), $mainMenu->getCopyright());
            }
            else{
                $cloneMainMenu->setStatus($mainMenu->getStatus());
                $cloneMainMenu->setCopyright($mainMenu->getCopyright());
            }
            $this->em->persist($cloneMainMenu);
            $this->em->flush();

            $newMainMenuItemsIds = $this->replaceMainMenuItem($mainMenu, $cloneMainMenu);

            //Remove OLD MENU ITEMS
            $allCloneMenuItems = $this->em->getRepository("AppBundle:MainMenuItems")->findBy(['mainMenu'=>$cloneMainMenu]);
            if(!empty($allCloneMenuItems) && !empty($newMainMenuItemsIds)){
                foreach ($allCloneMenuItems as $allCloneMenuItem){
                    if($allCloneMenuItem instanceof MainMenuItems){
                        if(!in_array($allCloneMenuItem->getId(), $newMainMenuItemsIds)){
                            $this->em->remove($allCloneMenuItem);
                            $this->em->flush();
                        }
                    }
                }
            }

            //Remove Draft Flow items
            $menuDraftItems = $this->em->getRepository("AppBundle:MainMenuDraft")->findOneBy(['mainMenu'=>$cloneMainMenu]);
            if($menuDraftItems instanceof MainMenuDraft){
                $this->em->remove($menuDraftItems);
                $this->em->flush();
            }

            if($cloneMainMenu->getStatus() == true){
                //Publish Menu
                $menuMain = new MenuMain($this->em, $this->clonePage, $cloneMainMenu);
                $menuMain->publish();
            }
            else{
                //Delete Menu
                $bot = new MyFbBotApp($this->clonePage->getAccessToken());
                $bot->deletePersistentMenu();
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function cloneMainKeywords(){
        $startKeyword = $this->em->getRepository("AppBundle:Keywords")->findOneBy(['page_id'=>$this->page->getPageId(), 'main'=>false]);
        if($startKeyword instanceof Keywords && $startKeyword->getFlow() instanceof Flows){
            $cloneStartKeyword = $this->em->getRepository("AppBundle:Keywords")->findOneBy(['page_id'=>$this->clonePage->getPageId(), 'main'=>false]);
            if($cloneStartKeyword instanceof Keywords){
                $actions = $this->replaceKeywordsAction($cloneStartKeyword);
                $newFlow = $this->replaceFlow($startKeyword->getFlow());
                $cloneStartKeyword->setFlow($newFlow);
                $cloneStartKeyword->setActions($actions);
                $this->em->persist($cloneStartKeyword);
                $this->em->flush();
            }
        }

        $stopKeyword = $this->em->getRepository("AppBundle:Keywords")->findOneBy(['page_id'=>$this->page->getPageId(), 'main'=>false], ['id'=>'DESC']);
        if($stopKeyword instanceof Keywords && $stopKeyword->getFlow() instanceof Flows){
            $cloneStopKeyword = $this->em->getRepository("AppBundle:Keywords")->findOneBy(['page_id'=>$this->clonePage->getPageId(), 'main'=>false], ['id'=>'DESC']);
            if($cloneStopKeyword instanceof Keywords){
                $actions = $this->replaceKeywordsAction($cloneStopKeyword);
                $newFlow = $this->replaceFlow($cloneStopKeyword->getFlow());
                $cloneStopKeyword->setFlow($newFlow);
                $cloneStopKeyword->setActions($actions);
                $this->em->persist($cloneStopKeyword);
                $this->em->flush();
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function cloneUserKeywords(){
        $keywords = $this->em->getRepository("AppBundle:Keywords")->findBy(['page_id'=>$this->page->getPageId(), 'main'=>false]);
        if(!empty($keywords)){
            foreach ($keywords as $keyword){
                if($keyword instanceof Keywords){
                    $this->replaceKeyword($keyword);
                }
            }
        }
    }

    /**
     * @param Flows $flow
     * @return Flows|object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    private function replaceFlow(Flows $flow){
        if(!array_key_exists($flow->getId(), $this->flowIds) || empty($this->flowIds[$flow->getId()])) {
            //Copy Flow
            $newFlow = new Flows(
                $this->clonePage->getPageId(), $flow->getName(), $flow->getType(), $this->folder, true
            );
            $this->em->persist($newFlow);
            $this->em->flush();
            $this->flowIds[$flow->getId()] = $newFlow->getId();

            //Copy FLow Items
            $this->replaceFlowItems($flow, $newFlow);

            return $newFlow;
        }

        return $this->em->getRepository("AppBundle:Flows")->find($this->flowIds[$flow->getId()]);
    }

    /**
     * @param Flows $flow
     * @param Flows $newFlow
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function replaceFlowItems(Flows $flow, Flows $newFlow){
        $flowItems = $this->em->getRepository("AppBundle:FlowItems")->findBy(['flow'=>$flow]);
        if(!empty($flowItems)){
            foreach ($flowItems as $flowItem){
                if($flowItem instanceof FlowItems){
                    $newFlowItem = new FlowItems(
                        $newFlow,
                        $flowItem->getUuid(),
                        $flowItem->getName(),
                        $flowItem->getType(),
                        $this->generateFlowItemItems($flowItem),
                        $flowItem->getQuickReply(),
                        $flowItem->getStartStep(),
                        $flowItem->getNextStep(),
                        $flowItem->getPositionX(),
                        $flowItem->getPositionY(),
                        $flowItem->getArrow(),
                        $flowItem->getHideNextStep()
                    );
                    $this->em->persist($newFlowItem);
                    $this->em->flush();
                }
            }
        }
    }

    /**
     * @param FlowItems $flowItem
     * @return array|mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function generateFlowItemItems(FlowItems $flowItem){
        $items = $flowItem->getItems();
        switch ($flowItem->getType()){
            case FlowItems::TYPE_SEND_MESSAGE:
                $items = $this->generateFlowItemItemsSendMessage($items);
                break;
            case FlowItems::TYPE_PERFORM_ACTIONS:
                $items = $this->generateFlowItemItemsPerformAction($items);
                break;
            case FlowItems::TYPE_START_ANOTHER_FLOW:
                $items = $this->generateFlowItemItemsAnotherFlow($items);
                break;
            case FlowItems::TYPE_CONDITION:
                $items = $this->generateFlowItemItemsCondition($items);
                break;
        }

        return $items;
    }

    /**
     * @param array $items
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function generateFlowItemItemsSendMessage(array $items){
        $newItems = [];
        if(!empty($items)) {
            foreach ($items as $key => $item) {
                if (isset($item['type']) && !empty($item['type'])) {
                    switch ($item['type']){
                        //TYPE TEXT
                        case "text":
                            if(array_key_exists('params', $item) && array_key_exists('description', $item['params']) && !empty($item['params']['description'])){
                                $item['params']['description'] = $this->replaceCustomFieldsInText($item['params']['description']);
                            }
                            break;
                        //TYPE CARD|GALLERY
                        case "card":
                        case "gallery":
                            if(array_key_exists('params', $item) && array_key_exists('cards_array', $item['params']) && !empty($item['params']['cards_array'])){
                                foreach ($item['params']['cards_array'] as $k=>$card){
                                    if(array_key_exists('title', $card) && !empty($card['title'])){
                                        $item['params']['cards_array'][$k]['title'] = $this->replaceCustomFieldsInText($card['title']);
                                    }
                                    if(array_key_exists('subtitle', $card) && !empty($card['subtitle'])){
                                        $item['params']['cards_array'][$k]['subtitle'] = $this->replaceCustomFieldsInText($card['subtitle']);
                                    }
                                }
                            }
                            break;
                        //TYPE LIST
                        case "list":
                            if(array_key_exists('params', $item) && array_key_exists('list_array', $item['params']) && !empty($item['params']['list_array'])){
                                foreach ($item['params']['list_array'] as $k=>$list){
                                    if(array_key_exists('title', $list) && !empty($list['title'])){
                                        $item['params']['list_array'][$k]['title'] = $this->replaceCustomFieldsInText($list['title']);
                                    }
                                    if(array_key_exists('subtitle', $list) && !empty($list['subtitle'])){
                                        $item['params']['list_array'][$k]['subtitle'] = $this->replaceCustomFieldsInText($list['subtitle']);
                                    }
                                }
                            }
                            break;

                        case "user_input":
                            if(array_key_exists('params', $item) && array_key_exists('description', $item['params']) && !empty($item['params']['description'])){
                                $item['params']['description'] = $this->replaceCustomFieldsInText($item['params']['description']);
                            }

                            if(array_key_exists('params', $item) && array_key_exists('keyboardInput', $item['params'])
                                && array_key_exists('id', $item['params']['keyboardInput']) && !empty($item['params']['keyboardInput']['id'])
                            ){
                                $customField = $this->em->getRepository('AppBundle:CustomFields')->find($item['params']['keyboardInput']['id']);
                                if($customField instanceof CustomFields){
                                    $item['params']['keyboardInput']['id'] = $this->replaceCustomField($customField);
                                }
                            }
                            break;
                    }
                }
                $newItems[] = $item;
            }
        }

        return $newItems;
    }

    /**
     * @param array $items
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function generateFlowItemItemsPerformAction(array $items){
        $newItems = [];
        if(!empty($items)) {
            foreach ($items as $key => $item) {
                $newItems[] = $this->replaceActions($item);
            }
        }

        return $newItems;
    }

    /**
     * @param array $items
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function generateFlowItemItemsAnotherFlow(array $items){
        $newItems = [];
        if(!empty($items)) {
            foreach ($items as $key => $item) {
                if(array_key_exists('id_select_flow', $item) && !empty($item['id_select_flow'])){
                    if(array_key_exists('type_selected_parent_item', $item) && !empty($item['type_selected_parent_item'])){
                        switch ($item['type_selected_parent_item']){
                            //Widgets
                            case 'newAutoresponder':
                            case 'company':
                                if(array_key_exists('id_selected_parent_item', $item) && !empty($item['id_selected_parent_item'])){
                                    $widget = $this->em->getRepository("AppBundle:Widget")->find($item['id_selected_parent_item']);
                                    if($widget instanceof Widget){
                                        $newWidgetId = $this->replaceWidget($widget);
                                        $newWidget = $this->em->getRepository("AppBundle:Widget")->find($newWidgetId);
                                        if($newWidget instanceof Widget){
                                            $item['id_selected_parent_item'] = $newWidget->getId();
                                            $item['id_select_flow'] = $newWidget->getFlow()->getId();
                                        }
                                    }
                                }
                                break;
                            //Sequence
                            case 'autoresponder':
                                if(array_key_exists('id_selected_parent_item', $item) && !empty($item['id_selected_parent_item'])){
                                    $sequence = $this->em->getRepository("AppBundle:Sequences")->find($item['id_selected_parent_item']);
                                    if($sequence instanceof Sequences){
                                        $newSequenceId = $this->replaceSequence($sequence);
                                        $newSequence = $this->em->getRepository("AppBundle:Sequences")->find($newSequenceId);
                                        if($newSequence instanceof Sequences){
                                            $item['id_selected_parent_item'] = $newSequence->getId();
                                            if(isset($this->flowIds[$item['id_select_flow']]) && !empty($this->flowIds[$item['id_select_flow']])){
                                                $item['id_select_flow'] = $this->flowIds[$item['id_select_flow']];
                                            }
                                            else{
                                                $flow = $this->em->getRepository("AppBundle:Flows")->find($item['id_select_flow']);
                                                if($flow instanceof Flows){
                                                    $newFlow = $this->replaceFlow($flow);
                                                    $item['id_select_flow'] = $newFlow->getId();
                                                }
                                            }
                                        }
                                    }
                                }
                                break;
                            //Keywords
                            case 'keywords':
                                if(array_key_exists('id_selected_parent_item', $item) && !empty($item['id_selected_parent_item'])){
                                    $keyword = $this->em->getRepository("AppBundle:Keywords")->find($item['id_selected_parent_item']);
                                    if($keyword instanceof Keywords){
                                        $newKeywordId = $this->replaceKeyword($keyword);
                                        $newKeyword = $this->em->getRepository("AppBundle:Keywords")->find($newKeywordId);
                                        if($newKeyword instanceof Keywords){
                                            $item['id_selected_parent_item'] = $newKeyword->getId();
                                            $item['id_select_flow'] = $newKeyword->getFlow()->getId();
                                        }
                                    }
                                }
                                break;
                            //Flow
                            default:
                                $flow = $this->em->getRepository("AppBundle:Flows")->find($item['id_select_flow']);
                                if($flow instanceof Flows){
                                    $newFlow = $this->replaceFlow($flow);
                                    $item['id_selected_parent_item'] = $newFlow->getId();
                                    $item['id_select_flow'] = $newFlow->getId();
                                }
                                break;
                        }
                    }
                    else{
                        $flow = $this->em->getRepository("AppBundle:Flows")->find($item['id_select_flow']);
                        if($flow instanceof Flows){
                            $newFlow = $this->replaceFlow($flow);
                            $item['id_selected_parent_item'] = $newFlow->getId();
                            $item['id_select_flow'] = $newFlow->getId();
                        }
                    }
                }
                $newItems[] = $item;
            }
        }

        return $newItems;
    }

    /**
     * @param array $items
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function generateFlowItemItemsCondition(array $items){
        $newItems = [];
        if(!empty($items)) {
            foreach ($items as $key => $item) {
                if(array_key_exists('conditions', $item) && !empty($item['conditions'])){
                    foreach ($item['conditions'] as $k=>$condition){
                        if(isset($condition['conditionType']) && !empty($condition['conditionType'])){
                            switch ($condition['conditionType']){
                                case "tag":
                                    if(array_key_exists('tagID', $condition) && !empty($condition['tagID'])){
                                        $tag = $this->em->getRepository("AppBundle:Tag")->find($condition['tagID']);
                                        if($tag instanceof Tag){
                                            $item['conditions'][$k]['tagID'] = $this->replaceTag($tag);
                                        }
                                    }
                                    break;
                                case "widget":
                                    if(array_key_exists('widgetID', $condition) && !empty($condition['widgetID'])){
                                        $widget = $this->em->getRepository("AppBundle:Widget")->find($condition['widgetID']);
                                        if($widget instanceof Widget){
                                            $item['conditions'][$k]['widgetID'] = $this->replaceWidget($widget);
                                        }
                                    }
                                    break;
                                case "sequence":
                                    if(array_key_exists('sequenceID', $condition) && !empty($condition['sequenceID'])){
                                        $sequence = $this->em->getRepository("AppBundle:Sequences")->find($condition['sequenceID']);
                                        if($sequence instanceof Sequences){
                                            $item['conditions'][$k]['sequenceID'] = $this->replaceSequence($sequence);
                                        }
                                    }
                                    break;

                                case "customField":
                                    if(array_key_exists('customFieldID', $condition) && !empty($condition['customFieldID'])){
                                        $customField = $this->em->getRepository("AppBundle:CustomFields")->find($condition['customFieldID']);
                                        if($customField instanceof CustomFields){
                                            $item['conditions'][$k]['customFieldID'] = $this->replaceCustomField($customField);
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                }

                $newItems[] = $item;
            }
        }

        return $newItems;
    }

    /**
     * @param $textMessage
     * @return string|string[]|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function replaceCustomFieldsInText($textMessage){
        $customFieldsArray = [];
        preg_match_all('/[{]{2}[cf_]+\d+[}]{2}/', $textMessage, $customFieldsArray);
        if(!empty($customFieldsArray) && isset($customFieldsArray[0])) {
            $customFieldsList = $customFieldsArray[0];
            foreach ($customFieldsList as $customFieldItem) {
                $customFieldID = substr($customFieldItem, 5, -2);
                $customField = $this->em->getRepository("AppBundle:CustomFields")->find($customFieldID);
                if ($customField instanceof CustomFields) {
                    $this->replaceCustomField($customField);
                }

                if(array_key_exists($customFieldID, $this->customFields) && !empty($this->customFields[$customFieldID])){
                    $textMessage = preg_replace("/{{cf_".$customFieldID."}}/", "{{cf_".$this->customFields[$customFieldID]."}}", $textMessage);
                }
            }
        }

        return $textMessage;
    }

    /**
     * @param CustomFields $customField
     * @return int|mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function replaceCustomField(CustomFields $customField){
        if(!array_key_exists($customField->getId(), $this->customFields) || empty($this->customFields[$customField->getId()])){
            $checkExistCustomField = $this->em->getRepository("AppBundle:CustomFields")->findOneBy([
                'page_id'=> $this->clonePage->getPageId(),
                'name' => $customField->getName(),
                'type' => $customField->getType()
            ]);
            if($checkExistCustomField instanceof CustomFields){
                $this->customFields[$customField->getId()] = $checkExistCustomField->getId();

                return $checkExistCustomField->getId();
            }
            else{
                $newCustomField = new CustomFields($this->clonePage->getPageId(), $customField->getName(), $customField->getType(), $customField->getDescription());
                $this->em->persist($newCustomField);
                $this->em->flush();
                $this->customFields[$customField->getId()] = $newCustomField->getId();

                return $newCustomField->getId();
            }
        }

        return $this->customFields[$customField->getId()];
    }

    /**
     * @param Tag $tag
     * @return int|mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function replaceTag(Tag $tag){
        if(!array_key_exists($tag->getId(), $this->tags) || empty($this->tags[$tag->getId()])) {
            $checkExistTag = $this->em->getRepository("AppBundle:Tag")->findOneBy([
                'page_id'=> $this->clonePage->getPageId(),
                'name' => $tag->getName()
            ]);
            if($checkExistTag instanceof Tag){
                $this->tags[$tag->getId()] = $checkExistTag->getId();

                return $checkExistTag->getId();
            }
            else{
                $newTag = new Tag($this->clonePage->getPageId(), $tag->getName());
                $this->em->persist($newTag);
                $this->em->flush();
                $this->tags[$tag->getId()] = $newTag->getId();

                return $newTag->getId();
            }
        }

        return $this->tags[$tag->getId()];
    }

    /**
     * @param Sequences $sequence
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function replaceSequence(Sequences $sequence){
        if(!array_key_exists($sequence->getId(), $this->sequences) || empty($this->sequences[$sequence->getId()])) {
            $newSequence = new Sequences($this->clonePage->getPageId(), $sequence->getTitle());
            $this->em->persist($newSequence);
            $this->em->flush();
            $this->sequences[$sequence->getId()] = $newSequence->getId();

            $sequenceItems = $this->em->getRepository("AppBundle:SequencesItems")->findBy(['sequence'=>$sequence]);
            if(!empty($sequenceItems)){
                foreach ($sequenceItems as $sequenceItem){
                    if($sequenceItem instanceof SequencesItems){
                        $newFlow = null;
                        if($sequenceItem->getFlow() instanceof Flows){
                            $newFlow = $this->replaceFlow($sequenceItem->getFlow());
                        }
                        $newSequenceItem = new SequencesItems($newSequence, $sequenceItem->getNumber(), $newFlow, $sequenceItem->getDelay(), $sequenceItem->getStatus());
                        $this->em->persist($newSequenceItem);
                        $this->em->flush();
                    }
                }
            }
        }

        return $this->sequences[$sequence->getId()];
    }

    /**
     * @param Widget $widget
     * @return int|mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function replaceWidget(Widget $widget){
        if(!array_key_exists($widget->getId(), $this->widgets) || empty($this->widgets[$widget->getId()])) {
            $newFlow = null;
            if($widget->getFlow() instanceof Flows){
                $newFlow = $this->replaceFlow($widget->getFlow());
            }
            $newSequence = null;
            if($widget->getSequence() instanceof Sequences){
                $newSequenceId = $this->replaceSequence($widget->getSequence());
                $newSequence = $this->em->getRepository("AppBundle:Sequences")->find($newSequenceId);
            }

            $options = $widget->getOptions();
            if($widget->getType() == 11){
                $options['selected_post_item'] = null;
            }

            $newWidget = new Widget($this->clonePage->getPageId(), $newFlow, $widget->getName(), $widget->getType(), $options, $newSequence);
            $this->em->persist($newWidget);
            $this->em->flush();
            $this->widgets[$widget->getId()] = $newWidget->getId();

            if ($newWidget->getType() == 7){
                $newOptions = $newWidget->getOptions();
                if(array_key_exists('custom_ref', $newOptions) && !empty($newOptions['custom_ref'])){
                    $existCustomRef = $this->em->getRepository("AppBundle:CustomRefParameter")->findOneBy(['page_id'=>$this->clonePage->getPageId(), 'parameter'=>$newOptions['custom_ref']]);
                    if(!$existCustomRef instanceof CustomRefParameter){
                        $newCustomRef = new CustomRefParameter($this->clonePage->getPageId(), $newWidget, $newOptions['custom_ref']);
                        $this->em->persist($newCustomRef);
                        $this->em->flush();
                    }
                    else{
                        $newWidget->setOptions([]);
                        $this->em->persist($newWidget);
                        $this->em->flush();
                    }
                }
            }

            WidgetHelper::generateWidgetFile($newWidget, $this->clonePage->getPageId());

        }

        return $this->widgets[$widget->getId()];
    }

    /**
     * @param Keywords $keyword
     * @return int|mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function replaceKeyword(Keywords $keyword){
        if(!array_key_exists($keyword->getId(), $this->keywords) || empty($this->keywords[$keyword->getId()])){
            $newFlow = null;
            if($keyword->getFlow() instanceof Flows){
                $newFlow = $this->replaceFlow($keyword->getFlow());
            }
            $newActions = $this->replaceKeywordsAction($keyword);

            $newKeyword = new Keywords($this->clonePage->getPageId(), $keyword->getCommand(), $keyword->getType(), $newFlow, $newActions, $keyword->getStatus(), false);
            $this->em->persist($newKeyword);
            $this->em->flush();

            $this->keywords[$keyword->getId()] = $newKeyword->getId();
        }

        return $this->keywords[$keyword->getId()];
    }

    /**
     * @param Keywords $keyword
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function replaceKeywordsAction(Keywords $keyword){
        $newActions = [];
        if(!empty($keyword->getActions())){
            foreach ($keyword->getActions() as $item){
                $newActions[] = $this->replaceActions($item);
            }
        }

        return $newActions;
    }

    /**
     * @param $item
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function replaceActions($item){
        if (isset($item['type']) && !empty($item['type'])) {
            switch ($item['type']) {
                case "add_tag":
                    if(array_key_exists('id', $item) && !empty($item['id'])){
                        $tag = $this->em->getRepository("AppBundle:Tag")->find($item['id']);
                        if($tag instanceof Tag){
                            $item['id'] = $this->replaceTag($tag);
                        }
                    }
                    break;
                case "remove_tag":
                    if(array_key_exists('id', $item) && !empty($item['id'])){
                        $tag = $this->em->getRepository("AppBundle:Tag")->find($item['id']);
                        if($tag instanceof Tag){
                            $item['id'] = $this->replaceTag($tag);
                        }
                    }
                    break;
                case "subscribe_sequence":
                    if(array_key_exists('id', $item) && !empty($item['id'])){
                        $sequence = $this->em->getRepository("AppBundle:Sequences")->find($item['id']);
                        if($sequence instanceof Sequences){
                            $item['id'] = $this->replaceSequence($sequence);
                        }
                    }
                    break;
                case "unsubscribe_sequence":
                    if(array_key_exists('id', $item) && !empty($item['id'])){
                        $sequence = $this->em->getRepository("AppBundle:Sequences")->find($item['id']);
                        if($sequence instanceof Sequences){
                            $item['id'] = $this->replaceSequence($sequence);
                        }
                    }
                    break;
                case "notify_admins":
                    $item['team'] = [];
                    break;
                case "set_custom_field":
                    if(array_key_exists('id', $item) && !empty($item['id'])){
                        $customField = $this->em->getRepository("AppBundle:CustomFields")->find($item['id']);
                        if($customField instanceof CustomFields){
                            $item['id'] = $this->replaceCustomField($customField);
                        }
                    }
                    break;
                case "clear_subscriber_custom_field":
                    if(array_key_exists('id', $item) && !empty($item['id'])){
                        $customField = $this->em->getRepository("AppBundle:CustomFields")->find($item['id']);
                        if($customField instanceof CustomFields){
                            $item['id'] = $this->replaceCustomField($customField);
                        }
                    }
                    break;
            }
        }

        return $item;
    }

    /**
     * @param MainMenu $mainMenu
     * @param MainMenu $cloneMainMenu
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function replaceMainMenuItem(MainMenu $mainMenu, MainMenu $cloneMainMenu){
        $newMainMenuItemIds = [];
        $mainMenuItems = $this->em->getRepository("AppBundle:MainMenuItems")->findBy(['mainMenu'=>$mainMenu]);
        if(!empty($mainMenuItems)){
            foreach ($mainMenuItems as $mainMenuItem){
                if($mainMenuItem instanceof MainMenuItems){
                    $newFlow = null;
                    if($mainMenuItem->getFlow() instanceof Flows){
                        $newFlow = $this->replaceFlow($mainMenuItem->getFlow());
                    }
                    $newActions = $this->replaceMainMenuItemAction($mainMenuItem);
                    $newMainMenuItem = new MainMenuItems(
                        $cloneMainMenu,
                        $mainMenuItem->getUuid(),
                        $mainMenuItem->getName(),
                        $mainMenuItem->getType(),
                        $mainMenuItem->getPosition(),
                        $newFlow,
                        $newActions,
                        $mainMenuItem->getUrl(),
                        $mainMenuItem->getParentID(),
                        $mainMenuItem->getViewSize(),
                        $mainMenuItem->getRemoved()
                    );
                    $this->em->persist($newMainMenuItem);
                    $this->em->flush();

                    $newMainMenuItemIds[] = $newMainMenuItem->getId();
                }
            }
        }

        return $newMainMenuItemIds;
    }

    /**
     * @param MainMenuItems $mainMenuItem
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function replaceMainMenuItemAction(MainMenuItems $mainMenuItem){
        $newActions = [];
        if(!empty($mainMenuItem->getActions())){
            foreach ($mainMenuItem->getActions() as $item){
                $newActions[] = $this->replaceActions($item);
            }
        }

        return $newActions;
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
     * @return Page
     */
    public function getClonePage()
    {
        return $this->clonePage;
    }

    /**
     * @param Page $clonePage
     */
    public function setClonePage($clonePage)
    {
        $this->clonePage = $clonePage;
    }

    /**
     * @return Folders
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param Folders $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * @return array
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * @param array $customFields
     */
    public function setCustomFields($customFields)
    {
        $this->customFields = $customFields;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }
}
