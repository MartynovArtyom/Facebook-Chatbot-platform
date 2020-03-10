<?php
/**
 * Created by PhpStorm.
 * Date: 18.03.19
 * Time: 14:25
 */

namespace AppBundle\MainMenu;


use AppBundle\Entity\Flows;
use AppBundle\Entity\MainMenu;
use AppBundle\Entity\MainMenuDraft;
use AppBundle\Entity\MainMenuItems;
use AppBundle\Entity\Page;
use AppBundle\Helper\Flow\FlowHelper;
use AppBundle\Helper\MyFbBotApp;
use Doctrine\ORM\EntityManager;
use pimax\Menu\LocalizedMenu;
use pimax\Menu\MenuItem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MenuMain
 * @package AppBundle\MainMenu
 */
class MenuMain implements MenuMainInterface
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
     * @var MainMenu
     */
    protected $mainMenu;

    /**
     * @var MyFbBotApp
     */
    protected $bot;

    /**
     * MenuMain constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param MainMenu $mainMenu
     */
    public function __construct(EntityManager $em, Page $page, MainMenu $mainMenu)
    {
        $this->em = $em;
        $this->page = $page;
        $this->mainMenu = $mainMenu;
    }

    /**
     * @return array
     */
    public function getData(){
        $draft = false;
        $draftItems = [];
        $mainMenuDraft = $this->em->getRepository("AppBundle:MainMenuDraft")->findOneBy(['mainMenu'=>$this->mainMenu]);
        if($mainMenuDraft instanceof MainMenuDraft){
            $draft = true;
            $draftItems = $mainMenuDraft->getItems();
        }

        $items = $this->generateData(null);

        return [
            'copyright' => $this->mainMenu->getCopyright(),
            'status' => $this->mainMenu->getStatus(),
            'draft' => $draft,
            'draftItems' => $draftItems,
            'items' => $items
        ];
    }

    /**
     * @return array
     */
    public function publish(){
        if($this->mainMenu->getStatus() == true){
            //SET BOT
            $this->setBot(new MyFbBotApp($this->page->getAccessToken()));

            //Check Start Button
            $this->checkStartButton();

            //Generate Items
            $items = $this->generateData(null);

            //Generate Menu Items
            $localizedItems = $this->generateMenuItems($items);
            if($this->mainMenu->getCopyright() == true){
                $localizedItems[] = new MenuItem(MenuItem::TYPE_WEB, 'Unterstützt von ChatBo', 'https://chatbo.de');
            }
            if(!empty($localizedItems)){
                //Generate Menu
                $localizedMenu = new LocalizedMenu('default', false, $localizedItems);

                //Set Menu
                $result = $this->bot->setPersistentMenu([$localizedMenu]);

                if(isset($result['result']) && $result['result'] == 'success'){
                    return [
                        'result' => true,
                        'message' => ''
                    ];
                }
                elseif(isset($result['error']) && isset($result['error']['message'])){
                    return [
                        'result' => false,
                        'message' => $result['error']['message']
                    ];
                }
                else{
                    return [
                        'result' => false,
                        'message' => 'Oops, something went wrong!'
                    ];
                }
            }
        }

        return [
            'result' => true,
            'message' =>''
        ];
    }

    /**
     * @param $items
     * @return array
     */
    public function generateMenuItems($items){
        $localizedItem = [];
        $request = Request::createFromGlobals();
        if(!empty($items)){
            foreach ($items as $item){
                if(array_key_exists('type', $item) && !empty($item['type'])
                    && array_key_exists('name', $item) && !empty($item['name'])
                    && array_key_exists('uuid', $item) && !empty($item['uuid'])
                ){
                    if($item['type'] == 'reply_message'){
                        $localizedItem[] = new MenuItem(
                            MenuItem::TYPE_POSTBACK,
                            $item['name'],
                            //CHATBO_NEW :TYPE      :PAGE_ID              :ITEM_UUID
                            'CHATBO_NEW:MENU:'.$this->page->getPageId().':'.$item['uuid']
                        );
                    }
                    elseif($item['type'] == 'open_website'){
                        $urlButton = $request->getSchemeAndHttpHost()."/v2/fetch/insights/menu?pageID=".$this->page->getPageId()."&itemID=".$item['uuid']."&url=".urlencode($item['url']);
                        if(isset($item['viewSize']) && in_array($item['viewSize'], ['full', 'medium', 'compact'])){
                            if($item['viewSize'] == 'full'){
                                $localizedItem[] = new MenuItem(
                                    MenuItem::TYPE_WEB,
                                    $item['name'],
                                    $urlButton,
                                    'full',
                                    true
                                );
                            }
                            elseif ($item['viewSize'] == 'medium'){
                                $localizedItem[] = new MenuItem(
                                    MenuItem::TYPE_WEB,
                                    $item['name'],
                                    $urlButton,
                                    'tall',
                                    true
                                );
                            }
                            else{
                                $localizedItem[] = new MenuItem(
                                    MenuItem::TYPE_WEB,
                                    $item['name'],
                                    $urlButton,
                                    'compact',
                                    true
                                );
                            }
                        }
                        else{
                            $localizedItem[] = new MenuItem(
                                MenuItem::TYPE_WEB,
                                $item['name'],
                                $urlButton
                            );
                        }
                    }
                }
            }
        }

        return $localizedItem;
    }

    /**
     * Check Start Button
     */
    public function checkStartButton(){
        $checkStartButton = $this->bot->call('me/messenger_profile', [
            'fields' => 'get_started'
        ], 'get');

        if(!isset($checkStartButton['data'][0]['get_started']['payload']) || $checkStartButton['data'][0]['get_started']['payload'] != 'WELCOME_MESSAGE'){
            $this->bot->setGetStartedButton('WELCOME_MESSAGE');
        }
    }

    /**
     * @param $parentID
     * @return array
     */
    public function generateData($parentID){
        $items = [];
        $itemsRoot = $this->em->getRepository("AppBundle:MainMenuItems")->findBy(['mainMenu'=>$this->mainMenu, 'parentID'=>$parentID]);
        if(!empty($itemsRoot)) {
            //SORT BY POSITION
            for ($j = 0; $j < count($itemsRoot) - 1; $j++){
                for ($i = 0; $i < count($itemsRoot) - $j - 1; $i++){
                    if(isset($itemsRoot[$i]) && $itemsRoot[$i] instanceof MainMenuItems && isset($itemsRoot[$i + 1]) && $itemsRoot[$i + 1] instanceof MainMenuItems){
                        if ($itemsRoot[$i]->getPosition() > $itemsRoot[$i + 1]->getPosition()){
                            $tmp_var = $itemsRoot[$i + 1];
                            $itemsRoot[$i + 1] = $itemsRoot[$i];
                            $itemsRoot[$i] = $tmp_var;
                        }
                    }
                }
            }
            foreach ($itemsRoot as $rootItem) {
                if($rootItem instanceof MainMenuItems){
                    $flow = null;
                    if($rootItem->getFlow() instanceof Flows){
                        $flow = FlowHelper::getFlowResponse($this->em, $rootItem->getFlow());
                    }
                    $items[] = [
                        'id' => $rootItem->getId(),
                        'uuid' => $rootItem->getUuid(),
                        'name' => $rootItem->getName(),
                        'type' => $rootItem->getType(),
                        'flow' => $flow,
                        'actions' => $rootItem->getActions(),
                        'url' => $rootItem->getUrl(),
                        'parentID' => $rootItem->getParentID(),
                        'clicked' => $rootItem->getClicked(),
                        'viewSize' => $rootItem->getViewSize(),
                        'removed' => $rootItem->getRemoved(),
                    ];
                }
            }
        }

        return $items;
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
     * @return MainMenu
     */
    public function getMainMenu()
    {
        return $this->mainMenu;
    }

    /**
     * @param MainMenu $mainMenu
     */
    public function setMainMenu($mainMenu)
    {
        $this->mainMenu = $mainMenu;
    }

    /**
     * @return MyFbBotApp
     */
    public function getBot()
    {
        return $this->bot;
    }

    /**
     * @param MyFbBotApp $bot
     */
    public function setBot($bot)
    {
        $this->bot = $bot;
    }
}
