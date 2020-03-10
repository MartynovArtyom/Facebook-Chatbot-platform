<?php
/**
 * Created by PhpStorm.
 * Date: 16.07.18
 * Time: 13:54
 */

namespace AppBundle\Controller\Api\Site;


use AppBundle\Entity\CustomFields;
use AppBundle\Entity\DefaultReply;
use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\GreetingText;
use AppBundle\Entity\Keywords;
use AppBundle\Entity\MainMenu;
use AppBundle\Entity\MainMenuItems;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Page;
use AppBundle\Entity\PageAdmins;
use AppBundle\Entity\PageShare;
use AppBundle\Entity\SaveImages;
use AppBundle\Entity\WelcomeMessage;
use AppBundle\Helper\MyFbBotApp;
use AppBundle\Helper\PageHelper;
use AppBundle\MainMenu\MenuMain;
use AppBundle\Pages\ClonePage;
use FOS\RestBundle\Controller\FOSRestController;
use pimax\FbBotApp;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class PageController
 * @package AppBundle\Controller\Api
 *
 * @Rest\Route("/page")
 */
class PageController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/v2/page/",
     *   tags={"PAGE"},
     *   security=false,
     *   summary="GET ALL CONNECT PAGE ON CHATBO",
     *   description="The method for getting all user page on chatbo",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *              ),
     *              @SWG\Property(
     *                  property="page_id",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="title",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="access_token",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="avatar",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="role",
     *                  type="integer",
     *                  description="1=Admin, 2=Editor, 3=Live Chat Agent, 4=Viewer"
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     * )
     */
    public function getAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $pages = [];
        $userPages = $em->getRepository("AppBundle:Page")->findBy(['user'=>$this->getUser(), 'status'=>true]);
        $pagesAdmin = $em->getRepository("AppBundle:PageAdmins")->findBy(['user'=>$this->getUser()]);
        if(!empty($pagesAdmin)){
            foreach ($pagesAdmin as $pageAdmin ){
                $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$pageAdmin->getPageId(), 'status'=>true]);
                if($page instanceof Page){
                    $userPages[] = $page;
                }
            }
        }
        if(!empty($userPages)){
            foreach ($userPages as $page){
                if($page instanceof Page){
                    if($page->getStatus() == true){
                        $role = 1;
                        if($page->getUser()->getId() != $this->getUser()->getId()){
                            $pageAdmin = $em->getRepository("AppBundle:PageAdmins")->findOneBy(['page_id'=>$page->getPageId(),'user'=>$this->getUser()]);
                            if($pageAdmin instanceof PageAdmins){
                                $role = $pageAdmin->getRole();
                            }
                        }
                        $pages[] = [
                            'id'=>$page->getId(),
                            'page_id'=>$page->getPageId(),
                            'title'=>$page->getTitle(),
                            'access_token'=>$page->getAccessToken(),
                            'avatar'=>$page->getAvatar(),
                            'role'=>$role
                        ];
                    }
                }
            }
        }
        $view = $this->view($pages, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @Rest\Get("/{page_id}", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}",
     *   tags={"PAGE"},
     *   security=false,
     *   summary="GET PAGE BY PAGE_ID",
     *   description="The method for getting page by page_id",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="page_id",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="page_id"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="page_id",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="title",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="access_token",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="avatar",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="role",
     *              type="integer",
     *              description="1=Admin, 2=Editor, 3=Live Chat Agent, 4=Viewer"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *              ),
     *              @SWG\Property(
     *                  property="subcode",
     *                  type="integer",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Access Denied",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              )
     *          )
     *      )
     *   )
     * )
     */
    public function getByIdAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        $user = $this->getUser();
        if($page instanceof Page){
            $cache = new FilesystemAdapter();
            $currentPageCache = $cache->getItem('pageId'.$page->getId());
            if(!$currentPageCache->isHit()){
                $fb = new \Facebook\Facebook([
                    'app_id' => $this->container->getParameter('facebook_id'),
                    'app_secret' => $this->container->getParameter('facebook_secret'),
                    'default_graph_version' => 'v3.3'
                ]);
                try {
                    $response = $fb->get('/me?fields=access_token,id,name,picture{url}', $page->getAccessToken());
                    $decodeBody = $response->getDecodedBody();
                }
                catch(\Facebook\Exceptions\FacebookResponseException $e) {
                    $responseData = $e->getResponseData();
                    if($user->getId() != $page->getUser()->getId()){
                        //not reload AddfbAccount
                        $view = $this->view([
                            'error'=>[
                                'message'=>$e->getMessage(),
                                'code'=>$e->getCode(),
                                'subcode'=>$e->getSubErrorCode()
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                    if(isset($responseData['error'])){
                        if($responseData['error']['code'] == 190){
                            if($responseData['error']['error_subcode'] == 458 ){
                                //reload
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>$e->getMessage(),
                                        'code'=>$e->getCode(),
                                        'subcode'=>$e->getSubErrorCode()
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                            else if($responseData['error']['error_subcode'] == 460){
                                try {
                                    $response = $fb->get('/me/accounts?fields=access_token,id,name,is_webhooks_subscribed,picture{url}&limit=999', $page->getUser()->getFacebookAccessToken());
                                } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                                    //reload
                                    $view = $this->view([
                                        'error'=>[
                                            'message'=>$e->getMessage(),
                                            'code'=>$e->getCode(),
                                            'subcode'=>$e->getSubErrorCode()
                                        ]
                                    ], Response::HTTP_BAD_REQUEST);
                                    return $this->handleView($view);
                                } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                                    //reload
                                    $view = $this->view([
                                        'error'=>[
                                            'message'=>$e->getMessage(),
                                            'code'=>$e->getCode(),
                                            'subcode'=>$e->getSubErrorCode()
                                        ]
                                    ], Response::HTTP_BAD_REQUEST);
                                    return $this->handleView($view);
                                }
                                $decodeBody = $response->getDecodedBody();
                                if(isset($decodeBody['data']) && !empty($decodeBody['data'])){
                                    foreach ($decodeBody['data'] as $responsePage){
                                        if($responsePage['is_webhooks_subscribed'] === true && $responsePage['id'] == $page->getPageId()){
                                            $decodeBody = $responsePage;
                                            break;
                                        }
                                    }
                                }
                                else{
                                    //not reload AddfbAccount
                                    $view = $this->view([
                                        'error'=>[
                                            'message'=>$e->getMessage()
                                        ]
                                    ], Response::HTTP_BAD_REQUEST);
                                    return $this->handleView($view);
                                }
                            }
                            else{
                                // When validation fails or other local issues
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>$e->getMessage()
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        else{
                            // When validation fails or other local issues
                            $view = $this->view([
                                'error'=>[
                                    'message'=>$e->getMessage()
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    else{
                        // When validation fails or other local issues
                        $view = $this->view([
                            'error'=>[
                                'message'=>$e->getMessage()
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                catch(\Facebook\Exceptions\FacebookSDKException $e) {

                    // When validation fails or other local issues
                    $view = $this->view([
                        'error'=>[
                            'message'=>$e->getMessage()
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
                if(!isset($decodeBody['access_token']) or empty($decodeBody['access_token'])){
                    //reload
                    $view = $this->view([
                        'error'=>[
                            'message'=>$e->getMessage()
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
                $page->update($decodeBody['access_token'], $decodeBody['name'], $decodeBody['picture']['data']['url']);
                $em->persist($page);
                $em->flush();
                $currentPageCache->expiresAfter(\DateInterval::createFromDateString('30 minutes'));
                $cache->save($currentPageCache->set($page));
                //SAVE AVATAR
                $saveImage = new SaveImages($page->getAvatar(), "uploads/".$page->getPageId()."/avatar.jpg", $page->getId(), 'page');
                $em->persist($saveImage);
                $em->flush();
            }

            $role = 1;
            if($page->getUser()->getId() != $this->getUser()->getId()){
                $pageAdmin = $em->getRepository("AppBundle:PageAdmins")->findOneBy(['page_id'=>$page->getPageId(),'user'=>$this->getUser()]);
                if($pageAdmin instanceof PageAdmins){
                    $role = $pageAdmin->getRole();
                }
            }

            $view = $this->view([
                'id' => $page->getId(),
                'page_id' => $page->getPageId(),
                'title' => $page->getTitle(),
                'access_token' => $page->getAccessToken(),
                'avatar' => $page->getAvatar(),
                'role' => $role
            ], Response::HTTP_OK);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Access denied"
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     * @throws \Exception
     *
     * @Rest\Post("/connect")
     * @SWG\Post(path="/v2/page/connect",
     *   tags={"PAGE"},
     *   security=false,
     *   summary="CONNECT PAGE",
     *   description="The method for connecting page with chatbo",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="title",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="token",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="avatar",
     *              type="string",
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="page_id",
     *              type="string",
     *              description="pageID"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *              ),
     *              @SWG\Property(
     *                  property="subcode",
     *                  type="integer",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     * )
     */
    public function connectAction(Request $request){
        $fb = new \Facebook\Facebook([
            'app_id' => $this->container->getParameter('facebook_id'),
            'app_secret' => $this->container->getParameter('facebook_secret'),
            'default_graph_version' => 'v3.3'
        ]);
        if($request->request->has('id') && $request->request->has('title') && $request->request->has('token') && $request->request->has('avatar')) {
            try {
                $response = $fb->post(
                    '/me/subscribed_apps',
                    [
                        'subscribed_fields' => 'feed, messages, message_deliveries, messaging_referrals, messaging_postbacks, message_reads, message_echoes, messaging_optins, messaging_payments, messaging_account_linking',
                    ],
                    $request->request->get('token'));
            } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                $view = $this->view([
                    'error'=>[
                        'message'=>$e->getMessage(),
                        'code'=>$e->getCode(),
                        'subcode'=>$e->getSubErrorCode()
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                $view = $this->view([
                    'error'=>[
                        'message'=>$e->getMessage(),
                        'code'=>$e->getCode(),
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
            $decodeBody = $response->getDecodedBody();
            if (isset($decodeBody['success']) && $decodeBody['success'] == true) {
                $em = $this->getDoctrine()->getManager();

                //SAVE OR CREATE PAGE
                $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$request->request->get('id')]);
                if($page instanceof Page){
                    $page->update($request->request->get('token'), $request->request->get('title'), $request->request->get('avatar'), true);
                    $em->persist($page);
                    $em->flush();
                    //Change admin
                    if($page->getUser()->getId() != $this->getUser()->getId()){
                        $oldUserAdmin = $page->getUser();
                        $page->setUser($this->getUser());
                        $em->persist($page);
                        $em->flush();
                        $checkNewUserAdmin = $em->getRepository("AppBundle:PageAdmins")->findOneBy(['page_id'=>$page->getPageId(), 'user'=>$this->getUser()]);
                        if($checkNewUserAdmin instanceof PageAdmins){
                            $em->remove($checkNewUserAdmin);
                            $em->flush();
                        }
                        $checkOldUserAdmin = $em->getRepository("AppBundle:PageAdmins")->findOneBy(['page_id'=>$page->getPageId(), 'user'=>$oldUserAdmin]);
                        if(!$checkOldUserAdmin instanceof PageAdmins){
                            $checkOldUserAdmin = new PageAdmins($page->getPageId(), $oldUserAdmin, 1);
                            $em->persist($checkOldUserAdmin);
                            $em->flush();
                        }
                    }
                }
                else{
                    $page = new Page($this->getUser(), $request->request->get('id'), $request->request->get('token'), $request->request->get('title'), $request->request->get('avatar'));
                    $em->persist($page);
                    $em->flush();
                }

                //CREATE NOTIFICATION
                $notification = $em->getRepository("AppBundle:Notification")->findOneBy(['page_id'=>$page->getPageId(), 'user'=>$this->getUser()]);
                if(!$notification instanceof Notification){
                    $notification = new Notification($page->getPageId(), $this->getUser(), $this->getUser()->getEmail());
                    $em->persist($notification);
                    $em->flush();
                }

                //SAVE AVATAR
                $saveImage = new SaveImages($page->getAvatar(), "uploads/".$page->getPageId()."/avatar.jpg", $page->getId(), 'page');
                $em->persist($saveImage);
                $em->flush();

                //BOT
                $bot = new FbBotApp($page->getAccessToken());

                //Set Domain White List
                $domainList = [
                    "https://chatbo.de/",
                    "https://app.chatbo.de/",
                    "https://api.chatbo.de/",
                ];
                $domainListResult = $bot->getDomainWhitelist();
                if(isset($domainListResult['data'][0]['whitelisted_domains']) && !empty($domainListResult['data'][0]['whitelisted_domains'])){
                    $domainList = array_unique(array_merge($domainList, $domainListResult['data'][0]['whitelisted_domains']), SORT_REGULAR);
                }
                $bot->setDomainWhitelist($domainList);

                //Set Start Button
                $bot->setGetStartedButton('WELCOME_MESSAGE');

                //Set Greeting Text
                $greetingText = $em->getRepository('AppBundle:GreetingText')->findOneBy(['page_id'=>$page->getPageId()]);
                if(!$greetingText instanceof GreetingText){
                    $text = "Gratuliere, nur noch ein Schritt klick bitte auf \"Los geht's\" unten...";
                    $result = $bot->setGreetingText([
                        [
                            "locale" => "default",
                            "text" => $text
                        ]
                    ]);
                    if(isset($result['result']) && $result['result'] == 'success'){
                        $greetingText = new GreetingText($page->getPageId(), $text);
                        $em->persist($greetingText);
                        $em->flush();
                    }
                }

                //Create Main Menu
                $mainMenu = $em->getRepository("AppBundle:MainMenu")->findOneBy(['page_id'=>$page->getPageId()]);
                if(!$mainMenu instanceof MainMenu){
                    $mainMenu = new MainMenu($page->getPageId());
                    $em->persist($mainMenu);
                    $em->flush();
                    //Create Flow For unsubscribe
                    $mainMenuFlow = new Flows($page->getPageId(), 'Abmelden', Flows::FLOW_TYPE_MENU);
                    $em->persist($mainMenuFlow);
                    $em->flush();
                    //Create flow items
                    $mainMenuFlowUuids = [
                        'flowItem1' => [
                            'uuid' => Uuid::uuid4(),
                            'item' => Uuid::uuid4(),
                            'button' => Uuid::uuid4(),
                        ],
                        'flowItem2' => [
                            'uuid' => Uuid::uuid4(),
                            'item' => Uuid::uuid4(),
                            'button' => Uuid::uuid4(),
                        ],
                        'flowItem3' => [
                            'uuid' => Uuid::uuid4(),
                        ],
                        'flowItem4' => [
                            'uuid' => Uuid::uuid4(),
                        ],
                        'flowItem5' => [
                            'uuid' => Uuid::uuid4(),
                            'item' => Uuid::uuid4(),
                        ],
                    ];

                    $mainMenuFlowItem1 = new FlowItems(
                        $mainMenuFlow,
                        $mainMenuFlowUuids['flowItem1']['uuid'],
                        'Sende Nachricht',
                        FlowItems::TYPE_SEND_MESSAGE,
                        [
                            [
                                'uuid' => $mainMenuFlowUuids['flowItem1']['item'],
                                'type' => 'text',
                                'params' => [
                                    'description' => 'Möchtest du dich wirklich von {{page_name}} abmelden?',
                                    'buttons' => [
                                        [
                                            'uuid' => $mainMenuFlowUuids['flowItem1']['button'],
                                            'title' => 'Abmelden',
                                            'type' => 'send_message',
                                            'click' => 0,
                                            'btnValue' => NULL,
                                            'next_step' => $mainMenuFlowUuids['flowItem2']['uuid'],
                                            'arrow' => [
                                                'from' => [
                                                    'id' => $mainMenuFlowUuids['flowItem1']['button'],
                                                    'toItemX' => NULL,
                                                    'toItemY' => NULL,
                                                    'fromItemX' => 10157.597725086644,
                                                    'fromItemY' => 9959.0037272747304,
                                                ],
                                                'to' => [
                                                    'id' => $mainMenuFlowUuids['flowItem2']['uuid'],
                                                    'toItemX' => 10502.222222222221,
                                                    'toItemY' => 9762.2222222222226,
                                                    'fromItemX' => NULL,
                                                    'fromItemY' => NULL,
                                                ],
                                            ],
                                            'activeTitlePanel' => false,
                                        ]
                                    ],
                                    'active' => false,
                                ]
                            ]
                        ],
                        [],
                        true,
                        null,
                        9819,
                        9772,
                        [
                            'from' => [
                                'id' => $mainMenuFlowUuids['flowItem1']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 10219.097744360903,
                                'fromItemY' => 9771.5037593984962,
                            ],
                            'to' => [
                                'id' => NULL,
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $mainMenuFlowItem2 = new FlowItems(
                        $mainMenuFlow,
                        $mainMenuFlowUuids['flowItem2']['uuid'],
                        'Sende Nachricht',
                        FlowItems::TYPE_SEND_MESSAGE,
                        [
                            [
                                'uuid' => $mainMenuFlowUuids['flowItem2']['item'],
                                'type' => 'text',
                                'params' => [
                                    'description' => "Das hat geklappt, du bist jetzt abgemeldet und bekommst keine weiteren Nachrichten von uns.\n\nWenn du dich irrtümlich abgemeldet hast, klicke auf den Button, um dein Abo wieder zu aktivieren.",
                                    'buttons' => [
                                        [
                                            'uuid' => $mainMenuFlowUuids['flowItem2']['button'],
                                            'title' => 'Aktivieren',
                                            'type' => 'perform_actions',
                                            'click' => 0,
                                            'btnValue' => NULL,
                                            'next_step' => $mainMenuFlowUuids['flowItem4']['uuid'],
                                            'arrow' => [
                                                'from' => [
                                                    'id' => $mainMenuFlowUuids['flowItem2']['button'],
                                                    'toItemX' => NULL,
                                                    'toItemY' => NULL,
                                                    'fromItemX' => 10840.722249348957,
                                                    'fromItemY' => 9949.7222222222226,
                                                ],
                                                'to' => [
                                                    'id' => $mainMenuFlowUuids['flowItem4']['uuid'],
                                                    'toItemX' => 11213.333333333334,
                                                    'toItemY' => 10131.111111111111,
                                                    'fromItemX' => NULL,
                                                    'fromItemY' => NULL,
                                                ],
                                            ],
                                            'activeTitlePanel' => false,
                                        ]
                                    ],
                                    'active' => false,
                                ]
                            ]
                        ],
                        [],
                        false,
                        $mainMenuFlowUuids['flowItem3']['uuid'],
                        10502,
                        9762,
                        [
                            'from' => [
                                'id' => $mainMenuFlowUuids['flowItem2']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 10902.222222222221,
                                'fromItemY' => 9762.2222222222226,
                            ],
                            'to' => [
                                'id' => $mainMenuFlowUuids['flowItem3']['uuid'],
                                'toItemX' => 11227.460317460316,
                                'toItemY' => 9647.7777777777756,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $mainMenuFlowItem3 = new FlowItems(
                        $mainMenuFlow,
                        $mainMenuFlowUuids['flowItem3']['uuid'],
                        'Aktion ausführen',
                        FlowItems::TYPE_PERFORM_ACTIONS,
                        [
                            [
                                'type' => 'unsubscribe_bot'
                            ]
                        ],
                        [],
                        false,
                        null,
                        11227,
                        9648,
                        [
                            'from' => [
                                'id' => $mainMenuFlowUuids['flowItem3']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 11627.460317460316,
                                'fromItemY' => 9647.7777777777756,
                            ],
                            'to' => [
                                'id' => NULL,
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $mainMenuFlowItem4 = new FlowItems(
                        $mainMenuFlow,
                        $mainMenuFlowUuids['flowItem4']['uuid'],
                        'Aktion ausführen',
                        FlowItems::TYPE_PERFORM_ACTIONS,
                        [
                            [
                                'type' => 'subscribe_bot'
                            ]
                        ],
                        [],
                        false,
                        $mainMenuFlowUuids['flowItem5']['uuid'],
                        11213,
                        10131,
                        [
                            'from' => [
                                'id' => $mainMenuFlowUuids['flowItem4']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 11613.333333333334,
                                'fromItemY' => 10131.111111111111,
                            ],
                            'to' => [
                                'id' => $mainMenuFlowUuids['flowItem5']['uuid'],
                                'toItemX' => 11948.888888888891,
                                'toItemY' => 10140.000000000002,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $mainMenuFlowItem5 = new FlowItems(
                        $mainMenuFlow,
                        $mainMenuFlowUuids['flowItem5']['uuid'],
                        'Sende Nachricht',
                        FlowItems::TYPE_SEND_MESSAGE,
                        [
                            [
                                'uuid' => $mainMenuFlowUuids['flowItem5']['item'],
                                'type' => 'text',
                                'params' => [
                                    'description' => "Great! Die nächste Nachricht kommt in Kürze.\n\nP.S. Du kannst Dich jederzeit wieder abmelden, indem Du \"stop\" in das Feld tippst.",
                                    'buttons' => [],
                                    'active' => false,
                                ]
                            ]
                        ],
                        [],
                        false,
                        null,
                        11949,
                        10140,
                        [
                            'from' => [
                                'id' => $mainMenuFlowUuids['flowItem5']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 9577.7142857142862,
                                'fromItemY' => 9585.4285714285706,
                            ],
                            'to' => [
                                'id' => NULL,
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $em->persist($mainMenuFlowItem1);
                    $em->persist($mainMenuFlowItem2);
                    $em->persist($mainMenuFlowItem3);
                    $em->persist($mainMenuFlowItem4);
                    $em->persist($mainMenuFlowItem5);
                    $em->flush();
                    //Create Menu Item Unsubscribe
                    $mainMenuItem = new MainMenuItems($mainMenu, Uuid::uuid4(), 'Abmelden', 'reply_message', 0, $mainMenuFlow, [], null, null, null, false);
                    $em->persist($mainMenuItem);
                    $em->flush();
                }
                //Publish main menu
                $menuMain = new MenuMain($em, $page, $mainMenu);
                $menuMain->publish();

                //Automation default
                //Default Reply
                $defaultReply = $em->getRepository("AppBundle:DefaultReply")->findOneBy(['page_id'=>$page->getPageId()]);
                if(!$defaultReply instanceof DefaultReply){
                    $defaultReplyFlow = new Flows($page->getPageId(), 'Default Reply', Flows::FLOW_TYPE_DEFAULT_REPLY);
                    $em->persist($defaultReplyFlow);
                    $em->flush();
                    $defaultReply = new DefaultReply($page->getPageId(), $defaultReplyFlow, false);
                    $em->persist($defaultReply);
                    $em->flush();
                    //Ceate Flow items
                    $defaultReplyFlowUuids = [
                        'flowItem1' => [
                            'uuid' => Uuid::uuid4(),
                            'item' => Uuid::uuid4(),
                        ],
                    ];
                    //Start Step
                    $defaultReplyFlowItem1 = new FlowItems(
                        $defaultReplyFlow,
                        $defaultReplyFlowUuids['flowItem1']['uuid'],
                        'Sende Nachricht',
                        FlowItems::TYPE_SEND_MESSAGE,
                        [
                            [
                                'uuid' => $defaultReplyFlowUuids['flowItem1']['item'],
                                'type' => 'text',
                                'params' => [
                                    'description' => "Gib hier die Standardantwort ein. Das ist die Nachricht, die deine Abonnenten sehen, wenn der Nutzer eine Nachricht eintippt, auf die der Bot keine Antwort konfiguriert hat. Diese Nachricht wird nur 1 x alle 24 Stunden ausgeliefert.",
                                    'buttons' => [],
                                    'active' => false,
                                ]
                            ]
                        ],
                        [],
                        true,
                        null,
                        9800,
                        9800,
                        [
                            'from' => [
                                'id' => $defaultReplyFlowUuids['flowItem1']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 10200,
                                'fromItemY' => 9800,
                            ],
                            'to' => [
                                'id' => NULL,
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $em->persist($defaultReplyFlowItem1);
                    $em->flush();
                }

                //Welcome Message
                $welcomeMessage = $em->getRepository("AppBundle:WelcomeMessage")->findOneBy(['page_id'=>$page->getPageId()]);
                if(!$welcomeMessage instanceof WelcomeMessage){
                    $welcomeMessageFlow = new Flows($page->getPageId(), 'Welcome Message', Flows::FLOW_TYPE_WELCOME_MESSAGE);
                    $em->persist($welcomeMessageFlow);
                    $em->flush();
                    $welcomeMessage = new WelcomeMessage($page->getPageId(), $welcomeMessageFlow, false);
                    $em->persist($welcomeMessage);
                    $em->flush();
                    //Create flow items
                    $welcomeMessageFlowUuids = [
                        'flowItem1' => [
                            'uuid' => Uuid::uuid4(),
                            'item' => Uuid::uuid4(),
                        ],
                    ];
                    //Start Step
                    $welcomeMessageFlowItem1 = new FlowItems(
                        $welcomeMessageFlow,
                        $welcomeMessageFlowUuids['flowItem1']['uuid'],
                        'Sende Nachricht',
                        FlowItems::TYPE_SEND_MESSAGE,
                        [
                            [
                                'uuid' => $welcomeMessageFlowUuids['flowItem1']['item'],
                                'type' => 'text',
                                'params' => [
                                    'description' => "Gib hier die Willkommensnachricht ein, welche deine Interessenten auf deiner Facebook Fanpage sehen. Diese Willkommensnachricht ist ausschließlich auf deiner Fanpage zu sehen. Du kannst hier auch eine deiner bestehenden Kampagnen einfügen, indem du du auf die Nachricht klickst und dann auf bestehende Nachricht auswählen.",
                                    'buttons' => [],
                                    'active' => false,
                                ]
                            ]
                        ],
                        [],
                        true,
                        null,
                        9800,
                        9800,
                        [
                            'from' => [
                                'id' => $welcomeMessageFlowUuids['flowItem1']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 10200,
                                'fromItemY' => 9800,
                            ],
                            'to' => [
                                'id' => NULL,
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $em->persist($welcomeMessageFlowItem1);
                    $em->flush();
                }

                //Main Keywords
                $keywords = $em->getRepository("AppBundle:Keywords")->findBy(['page_id'=>$page->getPageId(), 'main'=>true]);
                if(count($keywords) < 2){
                    //Keywords Subscribed
                    $keywordSubscribedFlow = new Flows($page->getPageId(), 'Anmeldung zum Bot', Flows::FLOW_TYPE_KEYWORDS);
                    $em->persist($keywordSubscribedFlow);
                    $em->flush();
                    $keywordSubscribed = new Keywords($page->getPageId(), 'start,anmelden,Start,Anmelden', 1, $keywordSubscribedFlow, [["type"=>"subscribe_bot"]], true, true);
                    $em->persist($keywordSubscribed);
                    $em->flush();
                    //Create flow items
                    $keywordSubscribedFlowUuids = [
                        'flowItem1' => [
                            'uuid' => Uuid::uuid4(),
                            'item' => Uuid::uuid4(),
                        ],
                    ];
                    $keywordSubscribedFlowItem1 = new FlowItems(
                        $keywordSubscribedFlow,
                        $keywordSubscribedFlowUuids['flowItem1']['uuid'],
                        'Sende Nachricht',
                        FlowItems::TYPE_SEND_MESSAGE,
                        [
                            [
                                'uuid' => $keywordSubscribedFlowUuids['flowItem1']['item'],
                                'type' => 'text',
                                'params' => [
                                    'description' => "Sie haben sich erfolgreich angemeldet {{page_name}}! Die nächste Nachricht kommt in Kürze.!\n\nP.S. Du kannst Dich jederzeit wieder abmelden, indem Du \"stop\" in das Feld tippst.",
                                    'buttons' =>[],
                                    'active' => false
                                ]
                            ]
                        ],
                        [],
                        true,
                        null,
                        9800,
                        9800,
                        [
                            'from' => [
                                'id' => $keywordSubscribedFlowUuids['flowItem1']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 10200,
                                'fromItemY' => 9800,
                            ],
                            'to' => [
                                'id' => NULL,
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL
                            ]
                        ]
                    );
                    $em->persist($keywordSubscribedFlowItem1);
                    $em->flush();
                    //Keyword Unsubscribed
                    $keywordUnSubscribedFlow = new Flows($page->getPageId(), 'Abmeldung vom Bot', Flows::FLOW_TYPE_KEYWORDS);
                    $em->persist($keywordUnSubscribedFlow);
                    $em->flush();
                    $keywordUnSubscribed = new Keywords($page->getPageId(), 'stop,stopp,Stop,Stopp,abmelden,Abmelden', 1, $keywordUnSubscribedFlow, [], true, true);
                    $em->persist($keywordUnSubscribed);
                    $em->flush();
                    //Create flow items
                    $keywordUnSubscribedFlowUuids = [
                        'flowItem1' => [
                            'uuid' => Uuid::uuid4(),
                            'item' => Uuid::uuid4(),
                            'button' => Uuid::uuid4(),
                        ],
                        'flowItem2' => [
                            'uuid' => Uuid::uuid4(),
                            'item' => Uuid::uuid4(),
                            'button' => Uuid::uuid4(),
                        ],
                        'flowItem3' => [
                            'uuid' => Uuid::uuid4(),
                        ],
                        'flowItem4' => [
                            'uuid' => Uuid::uuid4(),
                        ],
                        'flowItem5' => [
                            'uuid' => Uuid::uuid4(),
                            'item' => Uuid::uuid4(),
                        ],
                    ];

                    $keywordUnSubscribedFlowItem1 = new FlowItems(
                        $keywordUnSubscribedFlow,
                        $keywordUnSubscribedFlowUuids['flowItem1']['uuid'],
                        'Sende Nachricht',
                        FlowItems::TYPE_SEND_MESSAGE,
                        [
                            [
                                'uuid' => $keywordUnSubscribedFlowUuids['flowItem1']['item'],
                                'type' => 'text',
                                'params' => [
                                    'description' => 'Möchtest du dich wirklich von {{page_name}} abmelden?',
                                    'buttons' => [
                                        [
                                            'uuid' => $keywordUnSubscribedFlowUuids['flowItem1']['button'],
                                            'title' => 'Abmelden',
                                            'type' => 'send_message',
                                            'click' => 0,
                                            'btnValue' => NULL,
                                            'next_step' => $keywordUnSubscribedFlowUuids['flowItem2']['uuid'],
                                            'arrow' => [
                                                'from' => [
                                                    'id' => $keywordUnSubscribedFlowUuids['flowItem1']['button'],
                                                    'toItemX' => NULL,
                                                    'toItemY' => NULL,
                                                    'fromItemX' => 10157.597725086644,
                                                    'fromItemY' => 9959.0037272747304,
                                                ],
                                                'to' => [
                                                    'id' => $keywordUnSubscribedFlowUuids['flowItem2']['uuid'],
                                                    'toItemX' => 10502.222222222221,
                                                    'toItemY' => 9762.2222222222226,
                                                    'fromItemX' => NULL,
                                                    'fromItemY' => NULL,
                                                ],
                                            ],
                                            'activeTitlePanel' => false,
                                        ]
                                    ],
                                    'active' => false,
                                ]
                            ]
                        ],
                        [],
                        true,
                        null,
                        9819,
                        9772,
                        [
                            'from' => [
                                'id' => $keywordUnSubscribedFlowUuids['flowItem1']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 10219.097744360903,
                                'fromItemY' => 9771.5037593984962,
                            ],
                            'to' => [
                                'id' => NULL,
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $keywordUnSubscribedFlowItem2 = new FlowItems(
                        $keywordUnSubscribedFlow,
                        $keywordUnSubscribedFlowUuids['flowItem2']['uuid'],
                        'Sende Nachricht',
                        FlowItems::TYPE_SEND_MESSAGE,
                        [
                            [
                                'uuid' => $keywordUnSubscribedFlowUuids['flowItem2']['item'],
                                'type' => 'text',
                                'params' => [
                                    'description' => "Das hat geklappt, du bist jetzt abgemeldet und bekommst keine weiteren Nachrichten von uns.\n\nWenn du dich irrtümlich abgemeldet hast, klicke auf den Button, um dein Abo wieder zu aktivieren.",
                                    'buttons' => [
                                        [
                                            'uuid' => $keywordUnSubscribedFlowUuids['flowItem2']['button'],
                                            'title' => 'Aktivieren',
                                            'type' => 'perform_actions',
                                            'click' => 0,
                                            'btnValue' => NULL,
                                            'next_step' => $keywordUnSubscribedFlowUuids['flowItem4']['uuid'],
                                            'arrow' => [
                                                'from' => [
                                                    'id' => $keywordUnSubscribedFlowUuids['flowItem2']['button'],
                                                    'toItemX' => NULL,
                                                    'toItemY' => NULL,
                                                    'fromItemX' => 10840.722249348957,
                                                    'fromItemY' => 9949.7222222222226,
                                                ],
                                                'to' => [
                                                    'id' => $keywordUnSubscribedFlowUuids['flowItem4']['uuid'],
                                                    'toItemX' => 11213.333333333334,
                                                    'toItemY' => 10131.111111111111,
                                                    'fromItemX' => NULL,
                                                    'fromItemY' => NULL,
                                                ],
                                            ],
                                            'activeTitlePanel' => false,
                                        ]
                                    ],
                                    'active' => false,
                                ]
                            ]
                        ],
                        [],
                        false,
                        $keywordUnSubscribedFlowUuids['flowItem3']['uuid'],
                        10502,
                        9762,
                        [
                            'from' => [
                                'id' => $keywordUnSubscribedFlowUuids['flowItem2']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 10902.222222222221,
                                'fromItemY' => 9762.2222222222226,
                            ],
                            'to' => [
                                'id' => $keywordUnSubscribedFlowUuids['flowItem3']['uuid'],
                                'toItemX' => 11227.460317460316,
                                'toItemY' => 9647.7777777777756,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $keywordUnSubscribedFlowItem3 = new FlowItems(
                        $keywordUnSubscribedFlow,
                        $keywordUnSubscribedFlowUuids['flowItem3']['uuid'],
                        'Aktion ausführen',
                        FlowItems::TYPE_PERFORM_ACTIONS,
                        [
                            [
                                'type' => 'unsubscribe_bot'
                            ]
                        ],
                        [],
                        false,
                        null,
                        11227,
                        9648,
                        [
                            'from' => [
                                'id' => $keywordUnSubscribedFlowUuids['flowItem3']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 11627.460317460316,
                                'fromItemY' => 9647.7777777777756,
                            ],
                            'to' => [
                                'id' => NULL,
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $keywordUnSubscribedFlowItem4 = new FlowItems(
                        $keywordUnSubscribedFlow,
                        $keywordUnSubscribedFlowUuids['flowItem4']['uuid'],
                        'Aktion ausführen',
                        FlowItems::TYPE_PERFORM_ACTIONS,
                        [
                            [
                                'type' => 'subscribe_bot'
                            ]
                        ],
                        [],
                        false,
                        $keywordUnSubscribedFlowUuids['flowItem5']['uuid'],
                        11213,
                        10131,
                        [
                            'from' => [
                                'id' => $keywordUnSubscribedFlowUuids['flowItem4']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 11613.333333333334,
                                'fromItemY' => 10131.111111111111,
                            ],
                            'to' => [
                                'id' => $keywordUnSubscribedFlowUuids['flowItem5']['uuid'],
                                'toItemX' => 11948.888888888891,
                                'toItemY' => 10140.000000000002,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $keywordUnSubscribedFlowItem5 = new FlowItems(
                        $keywordUnSubscribedFlow,
                        $keywordUnSubscribedFlowUuids['flowItem5']['uuid'],
                        'Sende Nachricht',
                        FlowItems::TYPE_SEND_MESSAGE,
                        [
                            [
                                'uuid' => $keywordUnSubscribedFlowUuids['flowItem5']['item'],
                                'type' => 'text',
                                'params' => [
                                    'description' => "Great! Die nächste Nachricht kommt in Kürze.\n\nP.S. Du kannst Dich jederzeit wieder abmelden, indem Du \"stop\" in das Feld tippst.",
                                    'buttons' => [],
                                    'active' => false,
                                ]
                            ]
                        ],
                        [],
                        false,
                        null,
                        11949,
                        10140,
                        [
                            'from' => [
                                'id' => $keywordUnSubscribedFlowUuids['flowItem5']['uuid'],
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => 9577.7142857142862,
                                'fromItemY' => 9585.4285714285706,
                            ],
                            'to' => [
                                'id' => NULL,
                                'toItemX' => NULL,
                                'toItemY' => NULL,
                                'fromItemX' => NULL,
                                'fromItemY' => NULL,
                            ]
                        ]
                    );

                    $em->persist($keywordUnSubscribedFlowItem1);
                    $em->persist($keywordUnSubscribedFlowItem2);
                    $em->persist($keywordUnSubscribedFlowItem3);
                    $em->persist($keywordUnSubscribedFlowItem4);
                    $em->persist($keywordUnSubscribedFlowItem5);
                    $em->flush();
                }

                //Custom Fields
                $defaultCustomFields = [
                    [
                        'name' => 'E-Mail-Adresse',
                        'type' => 1
                    ],
                    [
                        'name' => 'Strasse',
                        'type' => 1
                    ],
                    [
                        'name' => 'Postleitzahl',
                        'type' => 1
                    ],
                    [
                        'name' => 'Stadt',
                        'type' => 1
                    ],
                    [
                        'name' => 'Land',
                        'type' => 1
                    ],
                    [
                        'name' => 'Telefonnummer',
                        'type' => 2
                    ]
                ];
                foreach ($defaultCustomFields as $defaultCustomField){
                    $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(
                        [
                            'page_id'=>$page->getPageId(),
                            'name'=>$defaultCustomField['name'],
                            'type' => $defaultCustomField['type']
                        ]
                    );
                    if(!$customField instanceof CustomFields){
                        $customField = new CustomFields($page->getPageId(), $defaultCustomField['name'], $defaultCustomField['type']);
                        $em->persist($customField);
                        $em->flush();
                    }
                }

                $view = $this->view([
                    'page_id' => $page->getPageId()
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Oops... Something went wrong!"
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"id, title, token, avatar is require fields"
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     *
     * @Rest\Patch("/{page_id}/disconnect", requirements={"page_id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/disconnect",
     *   tags={"PAGE"},
     *   security=false,
     *   summary="DISCONNECT PAGE BY PAGE_ID",
     *   description="The method for disconnecting page by page_id",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="page_id",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="page_id"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=false,
     *              description="only false"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *              ),
     *              @SWG\Property(
     *                  property="subcode",
     *                  type="integer",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Access denied",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     * )
     */
    public function disconnectByIdAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('status') && $request->request->getBoolean('status', false) == false){
                try{
                    $bot = new MyFbBotApp($page->getAccessToken());
                    $bot->deletePersistentMenu();
                } catch(\Exception $e){}

                $fb = new \Facebook\Facebook([
                    'app_id' => $this->container->getParameter('facebook_id'),
                    'app_secret' => $this->container->getParameter('facebook_secret'),
                    'default_graph_version' => 'v3.3'
                ]);

                try {
                    $response = $fb->delete('/me/subscribed_apps?access_token='.$page->getAccessToken());
                } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                    $view = $this->view([
                        'error'=>[
                            'message'=>$e->getMessage(),
                            'code'=>$e->getCode(),
                            'subcode'=>$e->getSubErrorCode()
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                    $view = $this->view([
                        'error'=>[
                            'message'=>$e->getMessage(),
                            'code'=>$e->getCode(),
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
                $decodeBody = $response->getDecodedBody();
                if(isset($decodeBody['success']) && $decodeBody['success'] == true) {
                    $page->setStatus(false);
                    $em->persist($page);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>'Oops... Something went wrong!'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }

            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"status is require fields"
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Access Denied"
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }

    }

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     *
     * @Rest\Delete("/{page_id}", requirements={"page_id"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}",
     *   tags={"PAGE"},
     *   security=false,
     *   summary="REMOVE PAGE BY PAGE_ID",
     *   description="The method for removing page by page_id",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="page_id",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="page_id"
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *              ),
     *              @SWG\Property(
     *                  property="subcode",
     *                  type="integer",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Access denied",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              )
     *          )
     *      )
     *   )
     * )
     */
    public function removeByIdAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            try{
                $bot = new MyFbBotApp($page->getAccessToken());
                $bot->deletePersistentMenu();
            } catch(\Exception $e){}

            $fb = new \Facebook\Facebook([
                'app_id' => $this->container->getParameter('facebook_id'),
                'app_secret' => $this->container->getParameter('facebook_secret'),
                'default_graph_version' => 'v3.3'
            ]);

            try {
                $response = $fb->delete('/me/subscribed_apps?access_token='.$page->getAccessToken());
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                $view = $this->view([
                    'error'=>[
                        'message'=>$e->getMessage(),
                        'code'=>$e->getCode(),
                        'subcode'=>$e->getSubErrorCode()
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                $view = $this->view([
                    'error'=>[
                        'message'=>$e->getMessage(),
                        'code'=>$e->getCode(),
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
            $decodeBody = $response->getDecodedBody();
            if(isset($decodeBody['success']) && $decodeBody['success'] == true) {

                $em->getRepository("AppBundle:Page")->removeByPageId($page->getPageId());

                $view = $this->view([], Response::HTTP_NO_CONTENT);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>'Oops... Something went wrong!'
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Access Denied"
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     *
     * @Rest\Get("/{page_id}/stats", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/stats",
     *   tags={"PAGE"},
     *   security=false,
     *   summary="GET PAGE STATS BY PAGE_ID",
     *   description="The method for getting page stats by page_id",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="page_id",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="page_id"
     *   ),
     *   @SWG\Parameter(
     *      name="type",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="active, net, sub-unsub"
     *   ),
     *   @SWG\Parameter(
     *      name="startDate",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="startDate"
     *   ),
     *   @SWG\Parameter(
     *      name="endDate",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="endDate"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="chart",
     *              type="array",
     *              @SWG\Items(
     *                  type="object"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="stats",
     *              type="object",
     *              @SWG\Property(
     *                  property="subs",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="unsubs",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="net",
     *                  type="integer"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="limitSubscribers",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="userSubscribers",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="pageSubscribers",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="productLabel",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="mapSubscribers",
     *              type="object"
     *          ),
     *          @SWG\Property(
     *              property="upgradeButton",
     *              type="boolean"
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=403,
     *      description="Access Denied",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              )
     *          )
     *      )
     *   )
     * )
     */
    public function statsByIdAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $pageStats = $em->getRepository("AppBundle:Subscribers")->findSubscribersForChart($page->getPageId(), $request->query->all());
            $pageStats['limitSubscribers'] = $page->getUser()->getLimitSubscribers();
            $pageStats['userSubscribers'] = $em->getRepository("AppBundle:Subscribers")->countAllByUserId($page->getUser()->getId());
            $pageStats['pageSubscribers'] = $em->getRepository('AppBundle:Subscribers')->count(['page_id'=>$page->getPageId(),'status'=>true]);
            $pageStats['productLabel'] = ($page->getUser()->getProduct() instanceof DigistoreProduct) ? $page->getUser()->getProduct()->getLabel() : 'Not Product';
            $pageStats['mapSubscribers'] = $em->getRepository("AppBundle:Subscribers")->getSubscriberForMap($page->getPageId());
            $pageStats['upgradeButton'] = ($page->getUser()->getProduct() instanceof DigistoreProduct && $page->getUser()->getProduct()->getId() != 12) ? false : true;

            $view = $this->view($pageStats, Response::HTTP_OK);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Access denied"
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/{page_id}/sidebar", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/sidebar",
     *   tags={"PAGE"},
     *   security=false,
     *   summary="GET DATA FOR SIDEBAR BY PAGE ID",
     *   description="The method for getting data for sidebar by page id",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="page_id",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="page_id"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="pages",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="page_id",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="title",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="access_token",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="avatar",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="role",
     *                      type="integer",
     *                      description="1=Admin, 2=Editor, 3=Live Chat Agent, 4=Viewer"
     *                  )
     *              )
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="openConversation"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="upgradeButton"
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="upgradeDay"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     * )
     */
    public function sidebarAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $pages = [];
            $userPages = $em->getRepository("AppBundle:Page")->findBy(['user'=>$this->getUser(), 'status'=>true]);
            $pagesAdmin = $em->getRepository("AppBundle:PageAdmins")->findBy(['user'=>$this->getUser()]);
            if(!empty($pagesAdmin)){
                foreach ($pagesAdmin as $pageAdmin ){
                    $pageA = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$pageAdmin->getPageId(), 'status'=>true]);
                    if($pageA instanceof Page){
                        $userPages[] = $pageA;
                    }
                }
            }
            if(!empty($userPages)){
                foreach ($userPages as $pageU){
                    if($pageU instanceof Page){
                        if($pageU->getStatus() == true){
                            $role = 1;
                            if($pageU->getUser()->getId() != $this->getUser()->getId()){
                                $pageAdmin = $em->getRepository("AppBundle:PageAdmins")->findOneBy(['page_id'=>$pageU->getPageId(),'user'=>$this->getUser()]);
                                if($pageAdmin instanceof PageAdmins){
                                    $role = $pageAdmin->getRole();
                                }
                            }
                            $pages[] = [
                                'id'=>$pageU->getId(),
                                'page_id'=>$pageU->getPageId(),
                                'title'=>$pageU->getTitle(),
                                'access_token'=>$pageU->getAccessToken(),
                                'avatar'=>$pageU->getAvatar(),
                                'role'=>$role
                            ];
                        }
                    }
                }
            }

            $openConversation = $em->getRepository("AppBundle:Conversation")->count(['page_id'=>$page->getPageId(), 'status'=>true]);

            $upgradeButton = false;
            $upgradeDay = 0;
            if(!$page->getUser()->getProduct() instanceof DigistoreProduct || $page->getUser()->getProduct()->getId() == 12){
                $upgradeButton = true;
                if($page->getUser()->getTrialEnd() instanceof \DateTime){
                    $now = new \DateTime();
                    $now->setTime(0,0,0);
                    $trialDay = new \DateTime($page->getUser()->getTrialEnd()->format('Y-m-d'));
                    $trialDay->setTime(0,0,0);
                    $upgradeDay = $trialDay->diff($now)->d;
                }

            }

            $view = $this->view([
                'pages' => $pages,
                'openConversation' => $openConversation,
                'upgradeButton' => $upgradeButton,
                'upgradeDay' => $upgradeDay,

            ], Response::HTTP_OK);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Access denied"
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Post("/{page_id}/clone", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/clone",
     *   tags={"PAGE"},
     *   security=false,
     *   summary="CLONE PAGE BY PAGE ID",
     *   description="The method for cloning page by page id",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="page_id",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="page_id"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="page_id",
     *              type="string",
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="Not Found",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=500,
     *      description="INTERNAL SERVER ERROR",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     * )
     */
    public function cloneAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('page_id') && !empty($request->request->get('page_id'))){
                $clonePage = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$request->request->get('page_id')]);
                if($clonePage instanceof Page){
                    if($clonePage->getUser()->getProduct() instanceof DigistoreProduct){
                        $countWidgetAll = $em->getRepository("AppBundle:Widget")->countAllByUserId($clonePage->getUser()->getId());
                        $countSequenceAll = $em->getRepository("AppBundle:Sequences")->countAllByUserId($clonePage->getUser()->getId());
                        $countWidget = $em->getRepository("AppBundle:Widget")->count(['page_id'=>$page->getPageId()]);
                        $countSequence = $em->getRepository("AppBundle:Sequences")->count(['page_id'=>$page->getPageId()]);
                        if(
                            (is_null($clonePage->getUser()->getProduct()->getLimitSequences()) || $clonePage->getUser()->getProduct()->getLimitSequences() >= ($countSequenceAll + $countSequence))
                            && (is_null($clonePage->getUser()->getProduct()->getLimitCompany()) || $clonePage->getUser()->getProduct()->getLimitCompany() >= ($countWidgetAll + $countWidget))
                        ){
                            if($clonePage->getUser()->getProduct()->getComments() == false){
                                $checkComments = $em->getRepository("AppBundle:Widget")->count(['page_id'=>$page->getPageId(), 'type'=>11]);
                                if($checkComments>0){
                                    $view = $this->view([
                                        'error'=>[
                                            'message'=>"Diese Funktion ist für deinen Plan nicht freigeschaltet",
                                            'type'=>'version'
                                        ]
                                    ], Response::HTTP_BAD_REQUEST);
                                    return $this->handleView($view);
                                }
                            }

                            try{
                                $pageClone = new ClonePage($em, $page, $clonePage);
                                $pageClone->cloneAll();

                                $view = $this->view([], Response::HTTP_NO_CONTENT);
                                return $this->handleView($view);
                            }
                            catch (\Exception $e){
                                $view = $this->view([
                                    'error'=>[
                                        'message'=> $e->getMessage(),
                                        'type'=>'internal_error'
                                    ]
                                ], Response::HTTP_INTERNAL_SERVER_ERROR);
                                return $this->handleView($view);
                            }
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Diese Funktion ist für deinen Plan nicht freigeschaltet",
                                    'type'=>'version'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Diese Funktion ist für deinen Plan nicht freigeschaltet",
                                'type'=>'version'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Clone Page Not Found",
                            'type'=>'not_found'
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"page_id is required",
                        'type'=>'bad_request'
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Access denied",
                    'type'=>'access_denied'
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/{page_id}/share", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/share",
     *   tags={"PAGE"},
     *   security=false,
     *   summary="GET SHARE PAGE BY PAGE ID",
     *   description="The method for get share page by page id",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="page_id",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="page_id"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="page_id",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="token",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="widgets"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="sequences"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="keywords"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="welcomeMessage"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="defaultReply"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="mainMenu"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="flows"
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad request",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="Not Found",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     * )
     */
    public function shareAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $pageShare = $em->getRepository("AppBundle:PageShare")->findOneBy(['page_id'=>$page->getPageId()]);
            if(!$pageShare instanceof PageShare){
                $pageShare = new PageShare($page->getPageId());
                $em->persist($pageShare);
                $em->flush();
            }

            $view = $this->view($pageShare->toArray(), Response::HTTP_OK);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Access denied",
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Put("/{page_id}/share", requirements={"page_id"="\d+"})
     * @SWG\Put(path="/v2/page/{page_id}/share",
     *   tags={"PAGE"},
     *   security=false,
     *   summary="CHANGE SHARE PAGE BY PAGE ID",
     *   description="The method for change share page by page id",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *      name="Authorization",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="Bearer <token>",
     *      description="Authorization Token"
     *   ),
     *   @SWG\Parameter(
     *      name="Content-Type",
     *      in="header",
     *      required=true,
     *      type="string",
     *      default="application/json",
     *      description="Content Type"
     *   ),
     *   @SWG\Parameter(
     *      name="page_id",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="page_id"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="widgets"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="sequences"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="keywords"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="welcomeMessage"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="defaultReply"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="mainMenu"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="flows"
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="page_id",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="token",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="widgets"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="sequences"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="keywords"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="welcomeMessage"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="defaultReply"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="mainMenu"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="flows"
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAD REQUEST",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="message",
     *                      type="string",
     *                  )
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="code",
     *              type="integer",
     *              example=401,
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="Not Found",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="error",
     *              type="object",
     *              @SWG\Property(
     *                  property="message",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     * )
     */
    public function changeShareAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $pageShare = $em->getRepository("AppBundle:PageShare")->findOneBy(['page_id'=>$page->getPageId()]);
            if($pageShare instanceof PageShare){
                if(!empty($request->request->all())){
                    $pageShare->update($request->request->all());
                    $errors = $this->get('validator')->validate($pageShare, null, array('pageShare'));
                    if(count($errors) === 0){
                        if(
                            $pageShare->getWidgets() == true || $pageShare->getSequences() == true || $pageShare->getKeywords() == true || $pageShare->getWelcomeMessage() == true
                            || $pageShare->getDefaultReply() == true || $pageShare->getMainMenu() == true || $pageShare->getFlows() == true
                        ){
                            $em->persist($pageShare);
                            $em->flush();

                            $view = $this->view($pageShare->toArray(), Response::HTTP_OK);
                            return $this->handleView($view);
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    [
                                        'message' => 'at least one must be selected'
                                    ]
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    else {
                        $error_description = [];
                        foreach ($errors as $er) {
                            $error_description[]['message'] = $er->getMessage();
                        }
                        $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"at least one field must be"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Not found",
                    ]
                ], Response::HTTP_NOT_FOUND);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Access denied",
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }
}

