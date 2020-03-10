<?php
/**
 * Created by PhpStorm.
 * Date: 18.10.18
 * Time: 13:46
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\DefaultReply;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Page;
use AppBundle\Helper\Flow\FlowHelper;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class DefaultReplyController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/automation/default")
 */
class DefaultReplyController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/automation/default/",
     *   tags={"DEFAULT REPLY"},
     *   security=false,
     *   summary="GET DEFAULT REPLY BY PAGE_ID",
     *   description="The method for get default reply by page_id",
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
     *              property="status",
     *              type="boolean",
     *              example=true,
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = Once per 24h,2 = Every time"
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
            $defaultReply = $em->getRepository("AppBundle:DefaultReply")->findOneBy(['page_id'=>$page_id]);
            if(!$defaultReply instanceof DefaultReply){
                $defaultReplyFlow = new Flows($page->getPageId(), 'Default Reply', Flows::FLOW_TYPE_DEFAULT_REPLY);
                $em->persist($defaultReplyFlow);
                $em->flush();
                $defaultReply = new DefaultReply($page->getPageId(),$defaultReplyFlow);
                $em->persist($defaultReply);
                $em->flush();
            }
            $view = $this->view([
                'status' => $defaultReply->getStatus(),
                'type' => $defaultReply->getType(),
                'flow' => FlowHelper::getFlowDataResponse($em, $defaultReply->getFlow())
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
     * @Rest\Patch("/", requirements={"page_id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/automation/default/",
     *   tags={"DEFAULT REPLY"},
     *   security=false,
     *   summary="UPDATE DEFAULT REPLY BY PAGE_ID",
     *   description="The method for update default reply by page_id",
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
     *              example=true,
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = Once per 24h,2 = Every time"
     *          ),
     *          @SWG\Property(
     *              property="flowID",
     *              type="integer",
     *              example=1
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true,
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = Once per 24h,2 = Every time"
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
            $defaultReply = $em->getRepository("AppBundle:DefaultReply")->findOneBy(['page_id'=>$page_id]);
            if($defaultReply instanceof DefaultReply){
                if($request->request->has('status') || $request->request->has('type') || $request->request->has('flowID')){
                    //UPDATE STATUS
                    if($request->request->has('status')) {
                        if (is_bool($request->request->get('status'))) {
                            $defaultReply->setStatus($request->request->get('status'));
                            $em->persist($defaultReply);
                            $em->flush();
                        } else {
                            $view = $this->view([
                                'error' => [
                                    'message' => 'Status is required and should be boolean type'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    //UPDATE TYPE
                    if ($request->request->has('type')){
                        if(in_array($request->request->get('type'),[1,2])){
                            $defaultReply->setType($request->request->get('type'));
                            $em->persist($defaultReply);
                            $em->flush();
                        }
                        else{
                            $view = $this->view([
                                'error' => [
                                    'message' => 'Type is required and should be 1 or 2'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    //UPDATE FLOW
                    if ($request->request->has('flowID') && !empty($request->request->get('flowID'))){
                        $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(),'id'=>$request->request->get('flowID')]);
                        if($flow instanceof Flows){
                            $flow->setType(Flows::FLOW_TYPE_DEFAULT_REPLY);
                            $em->persist($flow);
                            $em->flush();
                            $defaultReply->setFlow($flow);
                            $em->persist($defaultReply);
                            $em->flush();
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>'Flow Not Found'
                                ]
                            ], Response::HTTP_NOT_FOUND);
                            return $this->handleView($view);
                        }
                    }

                    $view = $this->view([
                        'status' => $defaultReply->getStatus(),
                        'type' => $defaultReply->getType(),
                        'flow' => FlowHelper::getFlowDataResponse($em, $defaultReply->getFlow())
                    ], Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error' => [
                            'message' => 'type or status or flowID is required'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>'Default Reply Not Found'
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