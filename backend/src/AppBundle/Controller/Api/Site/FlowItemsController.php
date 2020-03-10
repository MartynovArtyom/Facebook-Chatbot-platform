<?php
/**
 * Created by PhpStorm.
 * Date: 02.11.18
 * Time: 15:02
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\FlowItems;
use AppBundle\Entity\FlowItemsDraft;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\UserInputResponse;
use AppBundle\Flows\Flow;
use AppBundle\Flows\SelectFlowItem;
use AppBundle\Helper\Flow\FlowHelper;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class FlowItemsController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/flow/{flowID}/item")
 */
class FlowItemsController extends FOSRestController
{

    /**
     * @param Request $request
     * @param $page_id
     * @param $flowID
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+", "flowID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/flow/{flowID}/item/",
     *   tags={"FLOW ITEMS"},
     *   security=false,
     *   summary="GET FLOW ITEMS BY flowID BY PAGE_ID",
     *   description="The method for get flow items by flowID by page_id",
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
     *      name="flowID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="flowID"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *              description="flowID"
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              description="flowName"
     *          ),
     *          @SWG\Property(
     *              property="draft",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="draftItems",
     *              type="object"
     *          ),
     *          @SWG\Property(
     *                  property="items",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      @SWG\Property(
     *                          property="id",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="uuid",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="type",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="widget_content",
     *                          type="object"
     *                      ),
     *                      @SWG\Property(
     *                          property="quick_reply",
     *                          type="object"
     *                      ),
     *                      @SWG\Property(
     *                          property="start_step",
     *                          type="boolean"
     *                      ),
     *                      @SWG\Property(
     *                          property="next_step",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="x",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="y",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="arow",
     *                          type="object"
     *                      ),
     *                      @SWG\Property(
     *                          property="sent",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="delivered",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="opened",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="clicked",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="countResponses",
     *                          type="integer"
     *                      ),
     *                  )
     *              )
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
    public function getFlowDataAction(Request $request, $page_id, $flowID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){

                $view = $this->view(FlowHelper::getFlowDataResponse($em, $flow), Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Flow Not Found"
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
     * @param $flowID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Post("/", requirements={"page_id"="\d+", "flowID"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/flow/{flowID}/item/",
     *   tags={"FLOW ITEMS"},
     *   security=false,
     *   summary="SAVE FLOW ITEMS BY flowID BY PAGE_ID",
     *   description="The method for save flow items by flowID by page_id",
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
     *      name="flowID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="flowID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object"
     *              )
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *              description="flowID"
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              description="flowName"
     *          ),
     *          @SWG\Property(
     *              property="draft",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="draftItems",
     *              type="object"
     *          ),
     *          @SWG\Property(
     *                  property="items",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      @SWG\Property(
     *                          property="id",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="uuid",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="type",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="widget_content",
     *                          type="object"
     *                      ),
     *                      @SWG\Property(
     *                          property="quick_reply",
     *                          type="object"
     *                      ),
     *                      @SWG\Property(
     *                          property="start_step",
     *                          type="boolean"
     *                      ),
     *                      @SWG\Property(
     *                          property="next_step",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="x",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="y",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="arow",
     *                          type="object"
     *                      ),
     *                      @SWG\Property(
     *                          property="sent",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="delivered",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="opened",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="clicked",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="countResponses",
     *                          type="integer"
     *                      ),
     *                  )
     *              )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAD REQUEST",
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
    public function saveAction(Request $request, $page_id, $flowID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){
                if($request->request->has('items') && !empty($request->request->get('items'))){
                    //UPDATE CREATE FLOW ITEMS
                    $includeFlowItems = [];
                    foreach ($request->request->get('items') as $item){
                        if(
                            array_key_exists('uuid', $item) && !empty($item['uuid'])
                            && array_key_exists('name', $item) && !empty($item['name'])
                            && array_key_exists('type', $item) && !empty($item['type'])
                            && array_key_exists('widget_content', $item) && !empty($item['widget_content'])
                            && array_key_exists('quick_reply', $item)
                            && array_key_exists('start_step', $item) && is_bool($item['start_step'])
                            && array_key_exists('next_step', $item)
                            && array_key_exists('x', $item) && !empty($item['x'])
                            && array_key_exists('y', $item) && !empty($item['y'])
                            && array_key_exists('arrow', $item)
                            && array_key_exists('hideNextStep', $item) && is_bool($item['hideNextStep'])
                        ){
                            $flowItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flow, 'uuid'=>$item['uuid']]);
                            if(!$flowItem instanceof FlowItems){
                                $flowItem = new FlowItems(
                                    $flow,
                                    $item['uuid'],
                                    $item['name'],
                                    $item['type'],
                                    $item['widget_content'],
                                    $item['quick_reply'],
                                    $item['start_step'],
                                    $item['next_step'],
                                    $item['x'],
                                    $item['y'],
                                    $item['arrow'],
                                    $item['hideNextStep']
                                );
                            }
                            else{
                                $flowItem->update(
                                    $item['name'],
                                    $item['type'],
                                    $item['widget_content'],
                                    $item['quick_reply'],
                                    $item['start_step'],
                                    $item['next_step'],
                                    $item['x'],
                                    $item['y'],
                                    $item['arrow'],
                                    $item['hideNextStep']
                                );
                            }

                            try{
                                $em->persist($flowItem);
                                $em->flush();

                                $includeFlowItems[] = $flowItem->getId();
                            }
                            catch (\Exception $e){
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>$e->getMessage()
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }

                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Invalid message items"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }

                    //SAVE FLOW
                    $flow->setModified(new \DateTime());
                    $em->persist($flow);
                    $em->flush();

                    //Remove OLD Flow Items
                    $allFlowItems = $em->getRepository("AppBundle:FlowItems")->findBy(['flow'=>$flow]);
                    if(!empty($allFlowItems) && !empty($includeFlowItems)){
                        foreach ($allFlowItems as $allFlowItem){
                            if($allFlowItem instanceof FlowItems){
                                if(!in_array($allFlowItem->getId(), $includeFlowItems)){
                                    $em->remove($allFlowItem);
                                    $em->flush();
                                }
                            }
                        }
                    }

                    //Remove Draft Flow items
                    $flowDraftItems = $em->getRepository("AppBundle:FlowItemsDraft")->findOneBy(['flow'=>$flow]);
                    if($flowDraftItems instanceof FlowItemsDraft){
                        $em->remove($flowDraftItems);
                        $em->flush();
                    }

                    $view = $this->view(FlowHelper::getFlowDataResponse($em, $flow), Response::HTTP_OK);
                    return $this->handleView($view);

                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Erstellen Sie mindestens eine Nachricht"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Flow nicht gefunden"
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
     * @param $flowID
     * @return Response
     * @throws
     *
     * @Rest\Post("/draft", requirements={"page_id"="\d+", "flowID"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/flow/{flowID}/item/draft",
     *   tags={"FLOW ITEMS"},
     *   security=false,
     *   summary="SAVE FLOW ITEMS DRAFT BY flowID BY PAGE_ID",
     *   description="The method for save flow items draft by flowID by page_id",
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
     *      name="flowID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="flowID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object"
     *              )
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success."
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAD REQUEST",
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
    public function saveDraftAction(Request $request, $page_id, $flowID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){
                if($request->request->has('items')){
                    $flowItemsDraft = $em->getRepository("AppBundle:FlowItemsDraft")->findOneBy(['flow'=>$flow]);
                    if($flowItemsDraft instanceof FlowItemsDraft){
                        $flowItemsDraft->setItems($request->request->get('items'));
                    }
                    else{
                        $flowItemsDraft = new FlowItemsDraft($flow, $request->request->get('items'));
                    }
                    $em->persist($flowItemsDraft);
                    $em->flush();

                    $flow->setModified(new \DateTime());
                    $em->persist($flow);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"items is required field"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Flow Not Found"
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
     * @param $flowID
     * @return Response
     *
     * @Rest\Delete("/revert", requirements={"page_id"="\d+", "flowID"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/flow/{flowID}/item/revert",
     *   tags={"FLOW ITEMS"},
     *   security=false,
     *   summary="REVERT TO PUBLISH FLOW ITEMS BY flowID BY PAGE_ID",
     *   description="The method for revert to publish flow items by flowID by page_id",
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
     *      name="flowID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="flowID"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *              description="flowID"
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              description="flowName"
     *          ),
     *          @SWG\Property(
     *              property="draft",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="draftItems",
     *              type="object"
     *          ),
     *          @SWG\Property(
     *                  property="items",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      @SWG\Property(
     *                          property="id",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="uuid",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="type",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="widget_content",
     *                          type="object"
     *                      ),
     *                      @SWG\Property(
     *                          property="quick_reply",
     *                          type="object"
     *                      ),
     *                      @SWG\Property(
     *                          property="start_step",
     *                          type="boolean"
     *                      ),
     *                      @SWG\Property(
     *                          property="next_step",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="x",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="y",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="arow",
     *                          type="object"
     *                      ),
     *                      @SWG\Property(
     *                          property="sent",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="delivered",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="opened",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="clicked",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="countResponses",
     *                          type="integer"
     *                      ),
     *                  )
     *              )
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
    public function revertToPublishAction(Request $request, $page_id, $flowID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){
                $draftFlow = $em->getRepository("AppBundle:FlowItemsDraft")->findOneBy(['flow'=>$flow]);
                if($draftFlow instanceof FlowItemsDraft){
                    $em->remove($draftFlow);
                    $em->flush();
                }

                $view = $this->view(FlowHelper::getFlowDataResponse($em, $flow), Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Flow Not Found"
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
     * @param $flowID
     * @return Response
     *
     * @Rest\Post("/upload", requirements={"page_id"="\d+", "flowID"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/flow/{flowID}/item/upload",
     *   tags={"FLOW ITEMS"},
     *   security=false,
     *   summary="UPLOAD FILE FOR FLOW BY flowID BY PAGE_ID",
     *   description="The method for upload file for flow by flowID by page_id",
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
     *      name="flowID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="flowID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="file",
     *              type="file",
     *              example="file.jpg"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="string",
     *              example="file"
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="url",
     *              type="string",
     *              example="fileURL"
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              example="fileName"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAD REQUEST",
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
    public function uploadFileAction(Request $request, $page_id, $flowID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){
                if($request->files->has('file')){
                    $fileUpload = $request->files->get('file');
                    if($fileUpload instanceof UploadedFile){
                        if($request->request->has('type') && $request->request->get('type') == 'file'){
                            $fileName = str_replace(' ', '_', $fileUpload->getClientOriginalName());
                            $path = "uploads/".$page->getPageId()."/flow/".$flowID."/files";
                        }
                        else{
                            $fileName = uniqid().".".$fileUpload->getClientOriginalExtension();
                            $path = "uploads/".$page->getPageId()."/flow/".$flowID;
                        }
                        try {
                            $fileUpload->move($path,$fileName);
                        } catch (\Exception $e) {
                            $view = $this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                            return $this->handleView($view);
                        }

                        $view = $this->view([
                            'url' => $request->getSchemeAndHttpHost()."/".$path."/".$fileName,
                            'name' => str_replace(' ', '_', $fileUpload->getClientOriginalName())
                        ], Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Is not file"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"file is required"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Flow Not Found"
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
     * @param $flowID
     * @param $flowItemID
     * @return Response
     *
     * @Rest\Get("/{flowItemID}/responses", requirements={"page_id"="\d+", "flowID"="\d+", "flowItemID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/flow/{flowID}/item/{flowItemID}/responses",
     *   tags={"FLOW ITEMS"},
     *   security=false,
     *   summary="GET FLOW ITEM RESPONSES BY flowItemID BY flowID BY PAGE_ID",
     *   description="The method for get flow item responses by flowItemID by flowID by page_id",
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
     *      name="flowID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="flowID"
     *   ),
     *   @SWG\Parameter(
     *      name="flowItemID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="flowItemID"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="answers",
     *              type="array",
     *              @SWG\Items(
     *                  type="object"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="questions",
     *              type="array",
     *              @SWG\Items(
     *                  type="object"
     *              )
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
    public function responsesByIdAction(Request $request, $page_id, $flowID, $flowItemID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){
                $flowItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flow, 'id'=>$flowItemID]);
                if($flowItem instanceof FlowItems){
                    $responses = $em->getRepository("AppBundle:UserInputResponse")->findBy(['flowItem'=>$flowItem],['created'=>'DESC']);
                    $answers = [];
                    $questions = [];
                    if(!empty($responses)){
                        foreach ($responses as $response){
                            if($response instanceof UserInputResponse){
                                if(!in_array($response->getQuestion(), $questions)){
                                    $questions[] = $response->getQuestion();
                                }
                            }
                        }
                        foreach ($responses as $response){
                            if($response instanceof UserInputResponse && $response->getSubscriber() instanceof Subscribers){
                                $answer['subscriber'] = [
                                    'id' => $response->getSubscriber()->getId(),
                                    'firstName' => $response->getSubscriber()->getFirstName(),
                                    'lastName' => $response->getSubscriber()->getLastName(),
                                    'avatar' => $response->getSubscriber()->getAvatar()
                                ];
                                foreach ($questions as $question){
                                    if($question == $response->getQuestion()){
                                        $answer[$question] = [
                                            'response' => $response->getResponse(),
                                            'type' => $response->getType()
                                        ];
                                    }
                                    else{
                                        $answer[$question] = [];
                                    }
                                }
                                $answer['created'] = $response->getCreated();
                                $answers[] = $answer;
                            }
                        }
                    }

                    $view = $this->view([
                        'answers' => $answers,
                        'questions' => $questions
                    ], Response::HTTP_OK);
                    return $this->handleView($view);

                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Flow Item Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Flow Not Found"
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
     * @param $flowID
     * @param $flowItemID
     * @return Response
     *
     * @Rest\Get("/{flowItemID}/select", requirements={"page_id"="\d+", "flowID"="\d+", "flowItemID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/flow/{flowID}/item/{flowItemID}/select",
     *   tags={"FLOW ITEMS"},
     *   security=false,
     *   summary="GET FLOW ITEM FOR SELECT BY flowItemID BY flowID BY PAGE_ID",
     *   description="The method for get flow item for select by flowItemID by flowID by page_id",
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
     *      name="flowID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="flowID"
     *   ),
     *   @SWG\Parameter(
     *      name="flowItemID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="flowItemID"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="item",
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="uuid",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="widget_content",
     *                  type="object"
     *              ),
     *              @SWG\Property(
     *                  property="quick_reply",
     *                  type="object"
     *              ),
     *              @SWG\Property(
     *                  property="start_step",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="next_step",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="x",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="y",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="arow",
     *                  type="object"
     *              ),
     *              @SWG\Property(
     *                  property="sent",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="delivered",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="opened",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="clicked",
     *                  type="integer"
     *              )
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
     *                      property="widget_content",
     *                      type="object"
     *                  ),
     *                  @SWG\Property(
     *                      property="quick_reply",
     *                      type="object"
     *                  ),
     *                  @SWG\Property(
     *                      property="start_step",
     *                      type="boolean"
     *                  ),
     *                  @SWG\Property(
     *                      property="next_step",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="x",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="y",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="arow",
     *                      type="object"
     *                  ),
     *                  @SWG\Property(
     *                      property="sent",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="delivered",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="opened",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="clicked",
     *                      type="integer"
     *                 )
     *              )
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
    public function selectByIdAction(Request $request, $page_id, $flowID, $flowItemID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){
                $flowItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flow, 'id'=>$flowItemID]);
                if($flowItem instanceof FlowItems){
                    if($flowItem->getType() == FlowItems::TYPE_SEND_MESSAGE){
                        $selectFlowItems = new SelectFlowItem($em, $flowItem);
                        $selectItems = $selectFlowItems->selectItems();

                        $view = $this->view($selectItems, Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Flow Start Step Type NOT Sende Nachricht"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Flow Item Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Flow Not Found"
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
