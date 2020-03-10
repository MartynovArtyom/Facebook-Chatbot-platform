<?php
/**
 * Created by PhpStorm.
 * Date: 08.10.18
 * Time: 12:36
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\Broadcast;
use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Page;
use AppBundle\Entity\ScheduleBroadcast;
use AppBundle\Entity\Subscribers;
use AppBundle\Flows\CopyFlow;
use AppBundle\Helper\Flow\FlowHelper;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class BroadcastController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/broadcast")
 */
class BroadcastController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/broadcast/",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="GET BROADCASTS BY PAGE_ID",
     *   description="The method for getting broadcasts by page_id",
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
     *              property="draft",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      description="id",
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      description="name",
     *                  ),
     *                  @SWG\Property(
     *                      property="created",
     *                      type="datetime",
     *                      example="2018-09-09",
     *                      description="created",
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="integer",
     *                      example=1,
     *                      description="1=draft 2=schedule 3=history"
     *                  )
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="schedule",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      description="id",
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      description="name",
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      example=1,
     *                      description="1 = Subscription 2 = Promotional 3 = Follow-Up"
     *                  ),
     *                  @SWG\Property(
     *                      property="created",
     *                      type="datetime",
     *                      example="2018-09-09",
     *                      description="Schedule Date",
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="integer",
     *                      example=1,
     *                      description="1=draft 2=schedule 3=history"
     *                  )
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="history",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      description="id",
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      description="name",
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      example=1,
     *                      description="1 = Subscription 2 = Promotional 3 = Follow-Up"
     *                  ),
     *                  @SWG\Property(
     *                      property="created",
     *                      type="datetime",
     *                      example="2018-09-09",
     *                      description="Schedule Date",
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="integer",
     *                      example=1,
     *                      description="1=draft 2=schedule 3=history"
     *                  ),
     *                  @SWG\Property(
     *                      property="sent",
     *                      type="integer",
     *                      example=0,
     *                  ),
     *                  @SWG\Property(
     *                      property="delivered",
     *                      type="integer",
     *                      example=0
     *                  ),
     *                  @SWG\Property(
     *                      property="opened",
     *                      type="integer",
     *                      example=0
     *                  ),
     *                  @SWG\Property(
     *                      property="clicked",
     *                      type="integer",
     *                      example=0
     *                  )
     *              ),
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
    public function getAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcastsResult = $em->getRepository("AppBundle:Broadcast")->findBy(['page_id'=>$page_id],['created'=>'DESC']);
            $broadcasts = [];
            $broadcasts['draft'] = $broadcasts['schedule'] = $broadcasts['history'] = [];
            if(!empty($broadcastsResult)){
                foreach ($broadcastsResult as $broadcast){
                    if($broadcast instanceof Broadcast){
                        if($broadcast->getStatus() == 1){
                            $broadcasts['draft'][] = [
                              'id' => $broadcast->getId(),
                              'name' => $broadcast->getName(),
                              'created' => $broadcast->getCreated(),
                              'status' => $broadcast->getStatus()
                            ];
                        }
                        elseif($broadcast->getStatus() == 2){
                            $broadcasts['schedule'][] = [
                                'id' => $broadcast->getId(),
                                'name' => $broadcast->getName(),
                                'type' => $broadcast->getType(),
                                'created' => $broadcast->getCreated(),
                                'status' => $broadcast->getStatus()
                            ];
                        }
                        elseif($broadcast->getStatus() == 3){
                            $flowStartItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$broadcast->getFlow(), 'startStep'=>true]);

                            $broadcasts['history'][] = [
                                'id' => $broadcast->getId(),
                                'name' => $broadcast->getName(),
                                'type' => $broadcast->getType(),
                                'created' => $broadcast->getCreated(),
                                'status' => $broadcast->getStatus(),
                                'sent' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getSent() : 0,
                                'delivered' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getDelivered() : 0,
                                'opened' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getOpened() : 0,
                                'clicked' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getClicked() : 0
                            ];
                        }
                    }
                }
            }

            $view = $this->view($broadcasts, Response::HTTP_OK);
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
     * @Rest\Post("/", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/broadcast/",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="CREATE BROADCASTS BY PAGE_ID",
     *   description="The method for create broadcasts by page_id",
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
     *              property="name",
     *              type="string",
     *              example="broadcastName"
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *              example=1
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
    public function createAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('name') && !empty($request->request->get('name'))){
                $flow = new Flows($page->getPageId(), $request->request->get('name'), Flows::FLOW_TYPE_BROADCAST);
                $em->persist($flow);
                $em->flush();

                $broadcast = new Broadcast($page->getPageId(), $request->request->get('name'),$flow);
                $em->persist($broadcast);
                $em->flush();

                $view = $this->view([
                    'id' => $broadcast->getId()
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"name is required and should be not empty"
                    ]
                ], Response::HTTP_BAD_REQUEST);
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
     * @Rest\Get("/draft", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/broadcast/draft",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="GET BROADCASTS DRAFT BY PAGE_ID",
     *   description="The method for getting broadcasts draft by page_id",
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
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="id",
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="name",
     *              ),
     *              @SWG\Property(
     *                  property="created",
     *                  type="datetime",
     *                  example="2018-09-09",
     *                  description="created",
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="integer",
     *                  example=1,
     *                  description="1=draft 2=schedule 3=history"
     *              )
     *         ),
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
    public function getDraftAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcastsResult = $em->getRepository("AppBundle:Broadcast")->findBy(['page_id'=>$page_id, 'status'=>1],['created'=>'DESC']);
            $broadcasts = [];
            if(!empty($broadcastsResult)){
                foreach ($broadcastsResult as $broadcast){
                    if($broadcast instanceof Broadcast){
                        $broadcasts[] = [
                            'id' => $broadcast->getId(),
                            'name' => $broadcast->getName(),
                            'created' => $broadcast->getCreated(),
                            'status' => $broadcast->getStatus()
                        ];
                    }
                }
            }

            $view = $this->view($broadcasts, Response::HTTP_OK);
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
     * @Rest\Get("/schedule", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/broadcast/schedule",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="GET BROADCASTS SCHEDULE BY PAGE_ID",
     *   description="The method for getting broadcasts schedule by page_id",
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
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="id",
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="name",
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="integer",
     *                  example=1,
     *                  description="1 = Subscription 2 = Promotional 3 = Follow-Up"
     *              ),
     *              @SWG\Property(
     *                  property="created",
     *                  type="datetime",
     *                  example="2018-09-09",
     *                  description="created",
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="integer",
     *                  example=1,
     *                  description="1=draft 2=schedule 3=history"
     *              )
     *         ),
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
    public function getScheduleAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcastsResult = $em->getRepository("AppBundle:Broadcast")->findBy(['page_id'=>$page_id, 'status'=>2],['created'=>'DESC']);
            $broadcasts = [];
            if(!empty($broadcastsResult)){
                foreach ($broadcastsResult as $broadcast){
                    if($broadcast instanceof Broadcast){
                        $broadcasts[] = [
                            'id' => $broadcast->getId(),
                            'name' => $broadcast->getName(),
                            'type' => $broadcast->getType(),
                            'created' => $broadcast->getCreated(),
                            'status' => $broadcast->getStatus()
                        ];
                    }
                }
            }

            $view = $this->view($broadcasts, Response::HTTP_OK);
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
     * @Rest\Get("/history", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/broadcast/history",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="GET BROADCASTS HISTORY BY PAGE_ID",
     *   description="The method for getting broadcasts history by page_id",
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
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="id",
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="name",
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="integer",
     *                  example=1,
     *                  description="1 = Subscription 2 = Promotional 3 = Follow-Up"
     *              ),
     *              @SWG\Property(
     *                  property="created",
     *                  type="datetime",
     *                  example="2018-09-09",
     *                  description="created",
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="integer",
     *                  example=1,
     *                  description="1=draft 2=schedule 3=history"
     *              ),
     *              @SWG\Property(
     *                  property="sent",
     *                  type="integer",
     *                  example=0
     *              ),
     *              @SWG\Property(
     *                  property="delivered",
     *                  type="integer",
     *                  example=0
     *              ),
     *              @SWG\Property(
     *                  property="opened",
     *                  type="integer",
     *                  example=0
     *              ),
     *              @SWG\Property(
     *                  property="clicked",
     *                  type="integer",
     *                  example=0
     *              )
     *         ),
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
    public function getHistoryAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcastsResult = $em->getRepository("AppBundle:Broadcast")->findBy(['page_id'=>$page_id, 'status'=>3],['created'=>'DESC']);
            $broadcasts = [];
            if(!empty($broadcastsResult)){
                foreach ($broadcastsResult as $broadcast){
                    if($broadcast instanceof Broadcast){
                        $flowStartItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$broadcast->getFlow(), 'startStep'=>true]);

                        $broadcasts[] = [
                            'id' => $broadcast->getId(),
                            'name' => $broadcast->getName(),
                            'type' => $broadcast->getType(),
                            'created' => $broadcast->getCreated(),
                            'status' => $broadcast->getStatus(),
                            'sent' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getSent() : 0,
                            'delivered' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getDelivered() : 0,
                            'opened' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getOpened() : 0,
                            'clicked' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getClicked() : 0
                        ];
                    }
                }
            }

            $view = $this->view($broadcasts, Response::HTTP_OK);
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
     * @param $broadcastID
     * @return Response
     *
     * @Rest\Get("/{broadcastID}", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/broadcast/{broadcastID}",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="GET BROADCAST BY ID BY PAGE_ID",
     *   description="The method for getting broadcast by ID by page_id",
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
     *      name="broadcastID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="broadcastID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *              description="id",
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              description="name",
     *          ),
     *          @SWG\Property(
     *              property="flow",
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="flowID"
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="flowName"
     *              ),
     *              @SWG\Property(
     *                  property="draft",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="draftItems",
     *                  type="object"
     *              ),
     *              @SWG\Property(
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
     *          ),
     *          @SWG\Property(
     *              property="targeting",
     *              type="array",
     *              @SWG\Items(
     *                  type="object"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = Subscription 2 = Promotional 3 = Follow-Up"
     *          ),
     *          @SWG\Property(
     *              property="pushType",
     *              type="integer",
     *              example=1,
     *              description="1=Regular Push 2=Silent Push 3=Silent"
     *          ),
     *          @SWG\Property(
     *              property="created",
     *              type="datetime",
     *              example="2018-09-09",
     *              description="Schedule Date",
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="integer",
     *              example=1,
     *              description="1=draft 2=schedule 3=history"
     *          ),
     *          @SWG\Property(
     *              property="tag",
     *              type="string"
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
    public function getByIDAction(Request $request, $page_id, $broadcastID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcast = $em->getRepository("AppBundle:Broadcast")->findOneBy(['page_id'=>$page_id, 'id'=>$broadcastID]);
            if($broadcast instanceof Broadcast){
                $view = $this->view([
                    'id' => $broadcast->getId(),
                    'name' => $broadcast->getName(),
                    'flow' => FlowHelper::getFlowDataResponse($em, $broadcast->getFlow()),
                    'targeting' => $broadcast->getTargeting(),
                    'type' => $broadcast->getType(),
                    'pushType' => $broadcast->getPushType(),
                    'created' => $broadcast->getCreated(),
                    'status' => $broadcast->getStatus(),
                    'tag' => $broadcast->getTag()
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Broadcast Not Found"
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
     * @param $broadcastID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Put("/{broadcastID}", requirements={"page_id"="\d+"})
     * @SWG\Put(path="/v2/page/{page_id}/broadcast/{broadcastID}",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="EDIT BROADCAST BY ID BY PAGE_ID (ONLY DRAFT)",
     *   description="The method for edit broadcast by ID by page_id",
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
     *      name="broadcastID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="broadcastID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="targeting",
     *              type="array",
     *              @SWG\Items(
     *                  type="object"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = Subscription 2 = Promotional 3 = Follow-Up"
     *          ),
     *          @SWG\Property(
     *              property="pushType",
     *              type="integer",
     *              example=1,
     *              description="1=Regular Push 2=Silent Push 3=Silent"
     *          ),
     *          @SWG\Property(
     *              property="tag",
     *              type="string",
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success.",
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
    public function editByIDAction(Request $request, $page_id, $broadcastID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcast = $em->getRepository("AppBundle:Broadcast")->findOneBy(['page_id'=>$page_id, 'id'=>$broadcastID]);
            if($broadcast instanceof Broadcast){
                if($broadcast->getStatus() == 1){
                    if($request->request->has('targeting')
                        && $request->request->has('type') && $request->request->getInt('type',0) > 0 && $request->request->getInt('type',0) < 4
                        && $request->request->has('pushType') && $request->request->getInt('pushType',0) > 0 && $request->request->getInt('pushType',0) < 4
                    ){
                        $broadcast->update($request->request->getInt('pushType',0),
                            new \DateTime(),
                            1,
                            (!empty($request->request->get('targeting')) && is_array($request->request->get('targeting'))) ? $request->request->get('targeting') : [],
                            $request->request->getInt('type',0),
                            ($request->request->getInt('type',0) == 1 && $request->request->has('tag')) ? $request->request->get('tag') : null
                        );
                        $em->persist($broadcast);
                        $em->flush();

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"targeting, type, pushType is required and should be valid"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"You can edit only draft broadcast"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Broadcast Not Found"
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
     * @param $broadcastID
     * @return Response
     *
     * @Rest\Patch("/{broadcastID}", requirements={"page_id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/broadcast/{broadcastID}",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="UPDATE BROADCAST BY ID BY PAGE_ID",
     *   description="The method for update broadcast by ID by page_id",
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
     *      name="broadcastID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="broadcastID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              example="broadcastName"
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success.",
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
    public function updateByIDAction(Request $request, $page_id, $broadcastID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcast = $em->getRepository("AppBundle:Broadcast")->findOneBy(['page_id'=>$page_id, 'id'=>$broadcastID]);
            if($broadcast instanceof Broadcast){
                if($request->request->has('name') && !empty($request->request->get('name'))){
                    $broadcast->setName($request->request->get('name'));
                    $em->persist($broadcast);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"name is required and should be not empty"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Broadcast Not Found"
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
     * @param $broadcastID
     * @return Response
     *
     * @Rest\Delete("/{broadcastID}", requirements={"page_id"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/broadcast/{broadcastID}",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="REMOVE BROADCAST BY ID BY PAGE_ID (ONLY DRAFT)",
     *   description="The method for remove broadcast by ID by page_id",
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
     *      name="broadcastID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="broadcastID"
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success.",
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
    public function removeByIDAction(Request $request, $page_id, $broadcastID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcast = $em->getRepository("AppBundle:Broadcast")->findOneBy(['page_id'=>$page_id, 'id'=>$broadcastID]);
            if($broadcast instanceof Broadcast){
                if($broadcast->getStatus() == 1){
                    $em->remove($broadcast->getFlow());
                    $em->remove($broadcast);
                    $em->flush();
                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"You can remove only draft broadcast"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }

            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Broadcast Not Found"
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
     * @param $broadcastID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Put("/{broadcastID}/publish", requirements={"page_id"="\d+"})
     * @SWG\Put(path="/v2/page/{page_id}/broadcast/{broadcastID}/publish",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="PUBLISH BROADCAST BY ID BY PAGE_ID (ONLY DRAFT)",
     *   description="The method for publish broadcast by ID by page_id",
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
     *      name="broadcastID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="broadcastID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="targeting",
     *              type="array",
     *              @SWG\Items(
     *                  type="object"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = Subscription 2 = Promotional 3 = Follow-Up"
     *          ),
     *          @SWG\Property(
     *              property="tag",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="pushType",
     *              type="integer",
     *              example=1,
     *              description="1=Regular Push 2=Silent Push 3=Silent"
     *          ),
     *          @SWG\Property(
     *              property="sendType",
     *              type="integer",
     *              example=1,
     *              description="1=NOW 2=LATER"
     *          ),
     *          @SWG\Property(
     *              property="sendDate",
     *              type="datetime",
     *              example="2018-09-09",
     *              description="required when sendType=2"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success.",
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
    public function publishByIDAction(Request $request, $page_id, $broadcastID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcast = $em->getRepository("AppBundle:Broadcast")->findOneBy(['page_id'=>$page_id, 'id'=>$broadcastID]);
            if($broadcast instanceof Broadcast){
                if($broadcast->getStatus() == 1){
                    if($request->request->has('targeting')
                        && $request->request->has('type') && $request->request->getInt('type',0) > 0 && $request->request->getInt('type',0) < 4
                        && $request->request->has('pushType') && $request->request->getInt('pushType',0) > 0 && $request->request->getInt('pushType',0) < 4
                        && $request->request->has('sendType') && $request->request->getInt('sendType',0) > 0 && $request->request->getInt('sendType',0) < 3
                    ){
                        if($request->request->getInt('type',0) == 1 && (!$request->request->has('tag') || empty($request->request->get('tag')))){
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Tag ist erforderlich"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        $now = new \DateTime();
                        //SEND NOW
                        if($request->request->getInt('sendType',0) == 1){
                            $broadcast->update($request->request->getInt('pushType',0),
                                $now,
                                3,
                                (!empty($request->request->get('targeting')) && is_array($request->request->get('targeting'))) ? $request->request->get('targeting') : [],
                                $request->request->getInt('type',0),
                                ($request->request->getInt('type',0) == 1) ? $request->request->get('tag') : null
                            );
                            $em->persist($broadcast);
                            $em->flush();

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
                                        if($subscriber->getLastInteraction() instanceof \DateTime){
                                            $diff = $now->diff($subscriber->getLastInteraction());
                                        }
                                        else{
                                            $diff = 0;
                                        }
                                        if ($broadcast->getType() == 1 || $diff->days == 0) {
                                            $scheduleBroadcast = new ScheduleBroadcast($broadcast, $subscriber);
                                            $em->persist($scheduleBroadcast);
                                            $em->flush();
                                        }
                                    }
                                }
                            }

                            $view = $this->view([], Response::HTTP_NO_CONTENT);
                            return $this->handleView($view);
                        }
                        //SEND LATER
                        elseif ($request->request->getInt('sendType',0) == 2){
                            if($request->request->has('sendDate') && !empty($request->request->get('sendDate'))){
                                $sendDate = null;
                                if($request->request->get('sendDate') instanceof \DateTime){
                                    $sendDate = $request->request->get('sendDate');
                                }
                                else{
                                    $newSendDate = new \DateTime($request->request->get('sendDate'));
                                    if($newSendDate instanceof \DateTime){
                                        $sendDate = $newSendDate;
                                    }
                                }
                                if($sendDate instanceof \DateTime){
                                    $sendDate->setTimezone($now->getTimezone());
                                    if($sendDate->getTimestamp() > $now->getTimestamp()){
                                        $broadcast->update($request->request->getInt('pushType',0),
                                            $sendDate,
                                            2,
                                            (!empty($request->request->get('targeting')) && is_array($request->request->get('targeting'))) ? $request->request->get('targeting') : [],
                                            $request->request->getInt('type',0),
                                            ($request->request->getInt('type',0) == 1) ? $request->request->get('tag') : null
                                        );
                                        $em->persist($broadcast);
                                        $em->flush();

                                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                                        return $this->handleView($view);
                                    }
                                    else{
                                        $view = $this->view([
                                            'error'=>[
                                                'message'=>"Wählen Sie das gültige Datum und die Uhrzeit aus"
                                            ]
                                        ], Response::HTTP_BAD_REQUEST);
                                        return $this->handleView($view);
                                    }
                                }
                                else{
                                    $view = $this->view([
                                        'error'=>[
                                            'message'=>"Sendedatum ist erforderlich"
                                        ]
                                    ], Response::HTTP_BAD_REQUEST);
                                    return $this->handleView($view);
                                }
                            }
                            else{
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>"Sendedatum ist erforderlich"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"sendType is required and should be 1 or 2"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }

                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"targeting, type, pushType, sendType is required and should be valid"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"You can publish only draft broadcast"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Broadcast Not Found"
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
     * @param $broadcastID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Post("/{broadcastID}/copy", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/broadcast/{broadcastID}/copy",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="COPY BROADCAST BY ID BY PAGE_ID",
     *   description="The method for copy broadcast by ID by page_id",
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
     *      name="broadcastID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="broadcastID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *              description="id",
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              description="name",
     *          ),
     *          @SWG\Property(
     *              property="created",
     *              type="datetime",
     *              example="2018-09-09",
     *              description="created",
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="integer",
     *              example=1,
     *              description="1=draft 2=schedule 3=history"
     *          )
     *     )
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
    public function copyByIDAction(Request $request, $page_id, $broadcastID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcast = $em->getRepository("AppBundle:Broadcast")->findOneBy(['page_id'=>$page_id, 'id'=>$broadcastID]);
            if($broadcast instanceof Broadcast && $broadcast->getFlow() instanceof Flows){
                $copyFlow = new CopyFlow($em, $page, $broadcast->getFlow());
                $newFlow = $copyFlow->copy(Flows::FLOW_TYPE_BROADCAST);

                $newBroadcast = new Broadcast($page->getPageId(), $broadcast->getName()."-copy", $newFlow, $broadcast->getTargeting(), $broadcast->getType(), $broadcast->getPushType(), 1, $broadcast->getTag());
                $em->persist($newBroadcast);
                $em->flush();

                $view = $this->view([
                    'id' => $newBroadcast->getId(),
                    'name' => $newBroadcast->getName(),
                    'created' => $newBroadcast->getCreated(),
                    'status' => $newBroadcast->getStatus()
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Broadcast Not Found"
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
     * @param $broadcastID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Patch("/{broadcastID}/cancel", requirements={"page_id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/broadcast/{broadcastID}/cancel",
     *   tags={"BROADCAST"},
     *   security=false,
     *   summary="CANCEL BROADCAST BY ID BY PAGE_ID (ONLY SCHEDULE)",
     *   description="The method for cancel broadcast by ID by page_id",
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
     *      name="broadcastID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="broadcastID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *              description="id",
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              description="name",
     *          ),
     *          @SWG\Property(
     *              property="created",
     *              type="datetime",
     *              example="2018-09-09",
     *              description="created",
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="integer",
     *              example=1,
     *              description="1=draft 2=schedule 3=history"
     *          )
     *     )
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
    public function cancelByIDAction(Request $request, $page_id, $broadcastID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $broadcast = $em->getRepository("AppBundle:Broadcast")->findOneBy(['page_id'=>$page_id, 'id'=>$broadcastID]);
            if($broadcast instanceof Broadcast){
                if($broadcast->getStatus() == 2){
                    $broadcast->setStatus(1);
                    $broadcast->setCreated(new \DateTime());
                    $em->persist($broadcast);
                    $em->flush();

                    $view = $this->view([
                        'id' => $broadcast->getId(),
                        'name' => $broadcast->getName(),
                        'created' => $broadcast->getCreated(),
                        'status' => $broadcast->getStatus()
                    ], Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"You can cancel only schedule broadcast"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Broadcast Not Found"
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
