<?php
/**
 * Created by PhpStorm.
 * Date: 07.11.18
 * Time: 15:43
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\Flows;
use AppBundle\Entity\MainMenu;
use AppBundle\Entity\MainMenuDraft;
use AppBundle\Entity\MainMenuItems;
use AppBundle\Entity\Page;
use AppBundle\Helper\MyFbBotApp;
use AppBundle\Helper\PageHelper;
use AppBundle\MainMenu\MenuMain;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class MainMenuController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/automation/menu")
 */
class MainMenuController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/automation/menu/",
     *   tags={"MAIN MENU"},
     *   security=false,
     *   summary="GET MAIN MENU BY PAGE_ID",
     *   description="The method for get main menu by page_id",
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
     *              property="copyright",
     *              type="boolean",
     *              example=true,
     *              description="true = show default item menu"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true,
     *          ),
     *          @SWG\Property(
     *              property="draft",
     *              type="boolean",
     *              example=true,
     *          ),
     *          @SWG\Property(
     *              property="draftItems",
     *              type="object"
     *          ),
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="flow",
     *                      type="object",
     *                      @SWG\Property(
     *                          property="id",
     *                          type="integer",
     *                          example=1
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string",
     *                          example="flowName"
     *                      ),
     *                      @SWG\Property(
     *                          property="type",
     *                          type="integer",
     *                          example=1,
     *                          description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *                      ),
     *                      @SWG\Property(
     *                          property="folderID",
     *                          type="integer",
     *                      ),
     *                      @SWG\Property(
     *                          property="modified",
     *                          type="datetime",
     *                          example="2018-09-09"
     *                      ),
     *                      @SWG\Property(
     *                          property="status",
     *                          type="boolean",
     *                          example=true
     *                      ),
     *                      @SWG\Property(
     *                          property="draft",
     *                          type="boolean",
     *                          example=true,
     *                          description="true = have draft, false = not have draft"
     *                      ),
     *                  ),
     *                  @SWG\Property(
     *                      property="actions",
     *                      type="object"
     *                  ),
     *                  @SWG\Property(
     *                      property="url",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="parentID",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="clicked",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="new_item",
     *                      type="boolean"
     *                  ),
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
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="NOT FOUND",
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
    public function getAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $mainMenu = $em->getRepository("AppBundle:MainMenu")->findOneBy(['page_id'=>$page_id]);
            if(!$mainMenu instanceof MainMenu){
                $mainMenu = new MainMenu($page->getPageId());
                $em->persist($mainMenu);
                $em->flush();
            }

            $menuMain = new MenuMain($em, $page, $mainMenu);
            $view = $this->view($menuMain->getData(), Response::HTTP_OK);
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/automation/menu/",
     *   tags={"MAIN MENU"},
     *   security=false,
     *   summary="SAVE MAIN MENU BY PAGE_ID",
     *   description="The method for save main menu by page_id",
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
     *              property="items",
     *              type="object"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="copyright",
     *              type="boolean",
     *              example=true,
     *              description="true = show default item menu"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true,
     *          ),
     *          @SWG\Property(
     *              property="draft",
     *              type="boolean",
     *              example=true,
     *          ),
     *          @SWG\Property(
     *              property="draftItems",
     *              type="object"
     *          ),
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="uuid",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="flow",
     *                      type="object",
     *                      @SWG\Property(
     *                          property="id",
     *                          type="integer",
     *                          example=1
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string",
     *                          example="flowName"
     *                      ),
     *                      @SWG\Property(
     *                          property="type",
     *                          type="integer",
     *                          example=1,
     *                          description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *                      ),
     *                      @SWG\Property(
     *                          property="folderID",
     *                          type="integer",
     *                      ),
     *                      @SWG\Property(
     *                          property="modified",
     *                          type="datetime",
     *                          example="2018-09-09"
     *                      ),
     *                      @SWG\Property(
     *                          property="status",
     *                          type="boolean",
     *                          example=true
     *                      ),
     *                      @SWG\Property(
     *                          property="draft",
     *                          type="boolean",
     *                          example=true,
     *                          description="true = have draft, false = not have draft"
     *                      ),
     *                  ),
     *                  @SWG\Property(
     *                      property="actions",
     *                      type="object"
     *                  ),
     *                  @SWG\Property(
     *                      property="url",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="parentID",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="clicked",
     *                      type="integer"
     *                  ),
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
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="NOT FOUND",
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
    public function saveAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $mainMenu = $em->getRepository("AppBundle:MainMenu")->findOneBy(['page_id'=>$page_id]);
            if($mainMenu instanceof MainMenu){
                if($request->request->has('items') && !empty($request->request->get('items'))){
                    $includeMenuItems = [];
                    foreach ($request->request->get('items') as $key=>$item){
                        if(array_key_exists('uuid', $item) && !empty($item['uuid'])
                            && array_key_exists('name', $item) && !empty($item['name'])
                            && array_key_exists('type', $item) && !empty($item['type'])
                        ){
                            if(in_array($item['type'], ['reply_message', 'open_website'])){
                                if($item['type'] == 'reply_message'){
                                    if(array_key_exists('flow', $item) && array_key_exists('id', $item['flow']) && !empty($item['flow']['id'])){
                                        $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$item['flow']['id']]);
                                        if($flow instanceof Flows){
                                            $menuItem = $em->getRepository("AppBundle:MainMenuItems")->findOneBy(['mainMenu'=>$mainMenu, 'uuid'=>$item['uuid']]);
                                            if($menuItem instanceof MainMenuItems){
                                                $menuItem->update(
                                                    $item['name'],
                                                    $item['type'],
                                                    $key,
                                                    $flow,
                                                    $item['actions'],
                                                    null,
                                                    null,
                                                    null,
                                                    $item['removed']
                                                );
                                            }
                                            else{
                                                $menuItem = new MainMenuItems($mainMenu, $item['uuid'], $item['name'], $item['type'], $key, $flow, $item['actions'], null, null, null, $item['removed']);
                                            }
                                            $em->persist($menuItem);
                                            $em->flush();

                                            $includeMenuItems[] = $menuItem->getId();
                                        }
                                        else{
                                            $view = $this->view([
                                                'error'=>[
                                                    'message'=>"item[".$key."] Flow Not Found"
                                                ]
                                            ], Response::HTTP_BAD_REQUEST);
                                            return $this->handleView($view);
                                        }
                                    }
                                    else{
                                        $view = $this->view([
                                            'error'=>[
                                                'message'=>"item[".$key."] flow is required"
                                            ]
                                        ], Response::HTTP_BAD_REQUEST);
                                        return $this->handleView($view);
                                    }
                                }
                                elseif ($item['type'] == 'open_website'){
                                    if(array_key_exists('url', $item) && !empty($item['url']) && array_key_exists('viewSize', $item) && !empty($item['viewSize'])){
                                        $menuItem = $em->getRepository("AppBundle:MainMenuItems")->findOneBy(['mainMenu'=>$mainMenu, 'uuid'=>$item['uuid']]);
                                        if($menuItem instanceof MainMenuItems){
                                            $menuItem->update(
                                                $item['name'],
                                                $item['type'],
                                                $key,
                                                null,
                                                [],
                                                $item['url'],
                                                null,
                                                $item['viewSize'],
                                                $item['removed']
                                            );
                                        }
                                        else{
                                            $menuItem = new MainMenuItems($mainMenu, $item['uuid'], $item['name'], $item['type'], $key, null, [], $item['url'], null,$item['viewSize'], $item['removed']);
                                        }
                                        $em->persist($menuItem);
                                        $em->flush();

                                        $includeMenuItems[] = $menuItem->getId();
                                    }
                                    else{
                                        $view = $this->view([
                                            'error'=>[
                                                'message'=>"item[".$key."] url is required"
                                            ]
                                        ], Response::HTTP_BAD_REQUEST);
                                        return $this->handleView($view);
                                    }
                                }
                            }
                            else{
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>"item[".$key."][type] is invalid"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"item[".$key."] is invalid object"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }

                    //Remove OLD MENU ITEMS
                    $allMenuItems = $em->getRepository("AppBundle:MainMenuItems")->findBy(['mainMenu'=>$mainMenu]);
                    if(!empty($allMenuItems) && !empty($includeMenuItems)){
                        foreach ($allMenuItems as $allMenuItem){
                            if($allMenuItem instanceof MainMenuItems){
                                if(!in_array($allMenuItem->getId(), $includeMenuItems)){
                                    $em->remove($allMenuItem);
                                    $em->flush();
                                }
                            }
                        }
                    }

                    //Remove Draft Flow items
                    $menuDraftItems = $em->getRepository("AppBundle:MainMenuDraft")->findOneBy(['mainMenu'=>$mainMenu]);
                    if($menuDraftItems instanceof MainMenuDraft){
                        $em->remove($menuDraftItems);
                        $em->flush();
                    }

                    //PUBLISH
                    $menuMain = new MenuMain($em, $page, $mainMenu);
                    $resultMenuPublish = $menuMain->publish();
                    if(array_key_exists('result', $resultMenuPublish) && $resultMenuPublish['result'] == false){
                        if(array_key_exists('message', $resultMenuPublish) && !empty($resultMenuPublish['message'])){
                            $view = $this->view([
                                'error'=>[
                                    'message'=>$resultMenuPublish['message']
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Oops, something went wrong!"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }

                    //Response new main menu object
                    $view = $this->view($menuMain->getData(), Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"items is required and should be not empty"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Main Menu Not Found"
                    ]
                ], Response::HTTP_NOT_FOUND);
                return $this->handleView($view);
            }
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
     * @Rest\Patch("/", requirements={"page_id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/automation/menu/",
     *   tags={"MAIN MENU"},
     *   security=false,
     *   summary="UPDATE MAIN MENU BY PAGE_ID",
     *   description="The method for update main menu by page_id",
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
     *              example=true
     *          ),
     *          @SWG\Property(
     *              property="copyright",
     *              type="boolean",
     *              example=true
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
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
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="NOT FOUND",
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
    public function updateAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $mainMenu = $em->getRepository("AppBundle:MainMenu")->findOneBy(['page_id'=>$page_id]);
            if($mainMenu instanceof MainMenu){
                //UPDATE STATUS
                if($request->request->has('status') && is_bool($request->request->get('status'))){
                    $mainMenu->setStatus($request->request->get('status'));
                    $em->persist($mainMenu);
                    $em->flush();

                    if($mainMenu->getStatus() == true){
                        $menuMain = new MenuMain($em, $page, $mainMenu);
                        $menuMain->publish();
                    }
                    else{
                        $bot = new MyFbBotApp($page->getAccessToken());
                        $bot->deletePersistentMenu();
                    }

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                //UPDATE copyright
                elseif ($request->request->has('copyright') && is_bool($request->request->get('copyright'))){
					if($page->getUser()->getProduct() instanceof DigistoreProduct && $page->getUser()->getProduct()->getId() != 12){
						$mainMenu->setCopyright($request->request->get('copyright'));
						$em->persist($mainMenu);
						$em->flush();

						if($mainMenu->getStatus() == true){
							$menuMain = new MenuMain($em, $page, $mainMenu);
							$menuMain->publish();
						}

						$view = $this->view([], Response::HTTP_NO_CONTENT);
						return $this->handleView($view);
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
                            'message'=>"status or copyright is required and should be boolean type"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Main Menu Not Found"
                    ]
                ], Response::HTTP_NOT_FOUND);
                return $this->handleView($view);
            }
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
     * @Rest\Post("/draft", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/automation/menu/draft",
     *   tags={"MAIN MENU"},
     *   security=false,
     *   summary="SAVE DRAFT MAIN MENU BY PAGE_ID",
     *   description="The method for save draft main menu by page_id",
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
     *              property="items",
     *              type="object"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
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
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="NOT FOUND",
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
    public function saveDraftAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $mainMenu = $em->getRepository("AppBundle:MainMenu")->findOneBy(['page_id'=>$page_id]);
            if($mainMenu instanceof MainMenu){
                if($request->request->has('items')){
                    $mainMenuDraft = $em->getRepository("AppBundle:MainMenuDraft")->findOneBy(['mainMenu'=>$mainMenu]);
                    if($mainMenuDraft instanceof MainMenuDraft){
                        $mainMenuDraft->setItems($request->request->get('items'));
                    }
                    else{
                        $mainMenuDraft = new MainMenuDraft($mainMenu, $request->request->get('items'));
                    }
                    $em->persist($mainMenuDraft);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"items is required"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Main Menu Not Found"
                    ]
                ], Response::HTTP_NOT_FOUND);
                return $this->handleView($view);
            }
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
     * @Rest\Delete("/revert", requirements={"page_id"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/automation/menu/revert",
     *   tags={"MAIN MENU"},
     *   security=false,
     *   summary="REVERT TO PUBLISH MAIN MENU BY PAGE_ID NOT WORKING",
     *   description="The method for revert to publish main menu by page_id",
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
     *              property="copyright",
     *              type="boolean",
     *              example=true,
     *              description="true = show default item menu"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true,
     *          ),
     *          @SWG\Property(
     *              property="draft",
     *              type="boolean",
     *              example=true,
     *          ),
     *          @SWG\Property(
     *              property="draftItems",
     *              type="object"
     *          ),
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="uuid",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="flow",
     *                      type="object",
     *                      @SWG\Property(
     *                          property="id",
     *                          type="integer",
     *                          example=1
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string",
     *                          example="flowName"
     *                      ),
     *                      @SWG\Property(
     *                          property="type",
     *                          type="integer",
     *                          example=1,
     *                          description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *                      ),
     *                      @SWG\Property(
     *                          property="folderID",
     *                          type="integer",
     *                      ),
     *                      @SWG\Property(
     *                          property="modified",
     *                          type="datetime",
     *                          example="2018-09-09"
     *                      ),
     *                      @SWG\Property(
     *                          property="status",
     *                          type="boolean",
     *                          example=true
     *                      ),
     *                      @SWG\Property(
     *                          property="draft",
     *                          type="boolean",
     *                          example=true,
     *                          description="true = have draft, false = not have draft"
     *                      ),
     *                  ),
     *                  @SWG\Property(
     *                      property="actions",
     *                      type="object"
     *                  ),
     *                  @SWG\Property(
     *                      property="url",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="parentID",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="clicked",
     *                      type="integer"
     *                  ),
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
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="NOT FOUND",
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
    public function revertToPublishAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $mainMenu = $em->getRepository("AppBundle:MainMenu")->findOneBy(['page_id'=>$page_id]);
            if($mainMenu instanceof MainMenu){
                $menuDraft = $em->getRepository("AppBundle:MainMenuDraft")->findOneBy(['mainMenu'=>$mainMenu]);
                if($menuDraft instanceof MainMenuDraft){
                    $em->remove($menuDraft);
                    $em->flush();
                }

                $menuMain = new MenuMain($em, $page, $mainMenu);
                $view = $this->view($menuMain->getData(), Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Main Menu Not Found"
                    ]
                ], Response::HTTP_NOT_FOUND);
                return $this->handleView($view);
            }
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
}
