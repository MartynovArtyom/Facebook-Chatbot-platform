<?php
/**
 * Created by PhpStorm.
 * Date: 04.10.18
 * Time: 16:58
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\CampaignShare;
use AppBundle\Entity\CustomRefParameter;
use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\Widget;
use AppBundle\Flows\CopyFlow;
use AppBundle\Flows\Flow;
use AppBundle\Helper\Flow\FlowHelper;
use AppBundle\Helper\PageHelper;
use AppBundle\Helper\WidgetHelper;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\GraphNodes\GraphEdge;
use Facebook\GraphNodes\GraphNode;
use Facebook\GraphNodes\GraphNodeFactory;
use FOS\RestBundle\Controller\FOSRestController;
use pimax\FbBotApp;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class WidgetController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/widget")
 */
class WidgetController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/widget/",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="GET WIDGETS BY PAGE_ID",
     *   description="The method for getting widgets by page_id",
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
     *      name="page",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=1,
     *      description="pagination page"
     *   ),
     *   @SWG\Parameter(
     *      name="limit",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      default=20,
     *      description="pagination limit"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      description="id"
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      description="name"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      description="1 = Bar, 2 = SlideIn, 3 = Modal, 4 = Page Takeover, 5 = Button, 6 = Box, 7 = Ref Url, 8 = Ads JSON, 9 = Messenger Code, 10 = Customer Chat, 11 = Comments, 12 = Autoresponder"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean",
     *                      description="status"
     *                  ),
     *                  @SWG\Property(
     *                      property="flow",
     *                      type="object",
     *                          @SWG\Property(
     *                              property="id",
     *                              type="integer",
     *                              example=1
     *                          ),
     *                          @SWG\Property(
     *                              property="name",
     *                              type="string",
     *                              example="flowName"
     *                          ),
     *                          @SWG\Property(
     *                              property="type",
     *                              type="integer",
     *                              example=1,
     *                              description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *                          ),
     *                          @SWG\Property(
     *                              property="folderID",
     *                              type="integer",
     *                          ),
     *                          @SWG\Property(
     *                              property="modified",
     *                              type="datetime",
     *                              example="2018-09-09"
     *                          ),
     *                          @SWG\Property(
     *                              property="status",
     *                              type="boolean",
     *                              example=true
     *                          ),
     *                          @SWG\Property(
     *                              property="draft",
     *                              type="boolean",
     *                              example=true,
     *                              description="true = have draft, false = not have draft"
     *                          ),
     *                  ),
     *                  @SWG\Property(
     *                      property="shows",
     *                      type="integer",
     *                      example=0
     *                  ),
     *                  @SWG\Property(
     *                      property="optIn",
     *                      type="integer",
     *                      example=0
     *                  ),
     *                  @SWG\Property(
     *                      property="sent",
     *                      type="integer",
     *                      example=0
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
     *              )
     *          ),
     *          @SWG\Property(
     *              type="object",
     *              property="pagination",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="current_page_number"
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="total_count"
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
            $widgets = $em->getRepository("AppBundle:Widget")->findBy(['page_id' => $page->getPageId()], ['id' => 'DESC']);

            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $widgets,
                ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
            );
            $view = $this->view([
                'items'=> WidgetHelper::generateWidgetsResponse($em, $widgets),
                'pagination' => [
                    'current_page_number' => $pagination->getCurrentPageNumber(),
                    'total_count' => $pagination->getTotalItemCount(),
                ]
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
     * @throws \Exception
     *
     * @Rest\Post("/", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/widget/",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="CREATE WIDGET BY PAGE_ID",
     *   description="The method for creating widgets by page_id",
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
     *              example="widgetName"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = Bar, 2 = SlideIn, 3 = Modal, 4 = Page Takeover, 5 = Button, 6 = Box, 7 = Ref Url, 8 = Ads JSON, 9 = Messenger Code, 10 = Customer Chat, 11 = Comments, 12 = Autoresponder"
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
     *              description="id"
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              description="name"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              description="1 = Bar, 2 = SlideIn, 3 = Modal, 4 = Page Takeover, 5 = Button, 6 = Box, 7 = Ref Url, 8 = Ads JSON, 9 = Messenger Code, 10 = Customer Chat, 11 = Comments, 12 = Autoresponder"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              description="status"
     *          ),
     *          @SWG\Property(
     *              property="flow",
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  example="flowName"
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="integer",
     *                  example=1,
     *                  description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *              ),
     *              @SWG\Property(
     *                  property="folderID",
     *                  type="integer",
     *              ),
     *              @SWG\Property(
     *                  property="modified",
     *                  type="datetime",
     *                  example="2018-09-09"
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @SWG\Property(
     *                  property="draft",
     *                  type="boolean",
     *                  example=true,
     *                  description="true = have draft, false = not have draft"
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="shows",
     *              type="integer",
     *              example=0
     *          ),
     *          @SWG\Property(
     *              property="optIn",
     *              type="integer",
     *              example=0
     *          ),
     *          @SWG\Property(
     *              property="sent",
     *              type="integer",
     *              example=0
     *          ),
     *          @SWG\Property(
     *              property="delivered",
     *              type="integer",
     *              example=0
     *          ),
     *          @SWG\Property(
     *              property="opened",
     *              type="integer",
     *              example=0
     *          ),
     *          @SWG\Property(
     *              property="clicked",
     *              type="integer",
     *              example=0
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
            if($page->getUser()->getProduct() instanceof DigistoreProduct){
                $countWidget = $em->getRepository("AppBundle:Widget")->countAllByUserId($page->getUser()->getId());
                if(is_null($page->getUser()->getProduct()->getLimitCompany()) || $page->getUser()->getProduct()->getLimitCompany() > $countWidget){
                    if($request->request->has('name') && !empty($request->request->get('name')) && $request->request->has('type') && $request->request->getInt('type',0)>0) {
                        if($request->request->getInt('type', 0) == 11 && $page->getUser()->getProduct()->getComments() == false){
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Diese Funktion ist für deinen Plan nicht freigeschaltet",
                                    'type'=>'version'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        $flow = new Flows($page->getPageId(), $request->request->get('name'), Flows::FLOW_TYPE_WIDGET);
                        $em->persist($flow);
                        $em->flush();

                        $widget = new Widget($page->getPageId(), $flow, $request->request->get('name'), $request->request->getInt('type',0));
                        $em->persist($widget);
                        $em->flush();

                        if(in_array($widget->getType(),[1,2,3,4,5,6,10])){
                            $fs = new Filesystem();
                            $fs->dumpFile('widget/' . $page->getPageId() . '/' . $widget->getId() . '.js', '');
                        }

                        $view = $this->view(WidgetHelper::generateWidgetResponse($em, $widget), Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"name and type is required and should be not empty"
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
                    'message'=>"Access denied"
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @param $widgetID
     * @return Response
     *
     * @Rest\Get("/{widgetID}", requirements={"page_id"="\d+", "widgetID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/widget/{widgetID}",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="GET WIDGET BY ID BY PAGE_ID",
     *   description="The method for getting widget by id by page_id",
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
     *      name="widgetID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="widgetID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="details",
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="id"
     *              ),
     *              @SWG\Property(
     *                  property="flow",
     *                  type="object",
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
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  description="name"
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="integer",
     *                  description="1 = Bar, 2 = SlideIn, 3 = Modal, 4 = Page Takeover, 5 = Button, 6 = Box, 7 = Ref Url, 8 = Ads JSON, 9 = Messenger Code, 10 = Customer Chat, 11 = Comments, 12 = Autoresponder"
     *              ),
     *              @SWG\Property(
     *                  property="options",
     *                  type="object"
     *              ),
     *              @SWG\Property(
     *                  property="sequenceID",
     *                  type="integer",
     *                  description="sequenceID"
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="boolean",
     *                  description="status"
     *              ),
     *              @SWG\Property(
     *                  property="postID",
     *                  type="string",
     *                  description="postID"
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="allSequences",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="sequenceID",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="sequenceName",
     *                  )
     *              )
     *          ),
     *          @SWG\Property(
     *              property="website",
     *              type="array",
     *              @SWG\Items(
     *                  type="string"
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
    public function getByIDAction(Request $request, $page_id, $widgetID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $widget = $em->getRepository("AppBundle:Widget")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$widgetID]);
            if($widget instanceof Widget){
                $allSequences = $em->getRepository("AppBundle:Sequences")->findBy(['page_id'=>$page->getPageId()]);
                $sequences = [];
                if(!empty($allSequences)){
                    foreach ($allSequences as $sequence){
                        if($sequence instanceof Sequences){
                            $sequences[] = [
                                'sequenceID' => $sequence->getId(),
                                'name' => $sequence->getTitle()
                            ];
                        }
                    }
                }

                $fbBot = new FbBotApp($page->getAccessToken());
                $domainWhiteList = $fbBot->getDomainWhitelist();
                $websites = [];
                if(isset($domainWhiteList['data'][0]['whitelisted_domains'])){
                    $websites = $domainWhiteList['data'][0]['whitelisted_domains'];
                }

                if($request->getSchemeAndHttpHost() == 'https://api.chatbo.de'){
                    $filePath = 'https://widget.chatbo.de/' . $page->getPageId() . '/' . $widget->getId() . '.js';
                }
                else{
                    $filePath = $request->getSchemeAndHttpHost().'/widget/' . $page->getPageId() . '/' . $widget->getId() . '.js';
                }

                $view = $this->view([
                    'details' => [
                        'id' => $widget->getId(),
                        'flow' => FlowHelper::getFlowResponse($em, $widget->getFlow()),
                        'name' => $widget->getName(),
                        'type' => $widget->getType(),
                        'options' => $widget->getOptions(),
                        'sequenceID' => ($widget->getSequence() instanceof Sequences) ? $widget->getSequence()->getId() : null,
                        'status' => $widget->getStatus(),
                        'filePath' => $filePath,
                        'postId' => $widget->getPostId()
                    ],
                    'allSequences' => $sequences,
                    'website' => $websites
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Widget Not Found"
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
     * @param $widgetID
     * @return Response
     *
     * @Rest\Put("/{widgetID}", requirements={"page_id"="\d+", "widgetID"="\d+"})
     * @SWG\Put(path="/v2/page/{page_id}/widget/{widgetID}",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="EDIT WIDGET BY ID BY PAGE_ID",
     *   description="The method for editing widget by id by page_id",
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
     *      name="widgetID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="widgetID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="options",
     *              type="object",
     *              example={
     *                   "hide_bar":true,
     *                  "button_type":"checkbox",
     *                   "color_background":"#ffffff",
     *                   "color_headline":"#000000",
     *                   "color_button_background":"#0084ff",
     *                   "color_button_text":"#ffffff",
     *                   "it_display":0,
     *                   "display_seconds":30,
     *                   "show_widget_after":"always",
     *                   "show_widget_after_value":3,
     *                   "closed_user_show_after":"always",
     *                   "closed_user_show_after_value":3,
     *                   "check_button_bg":"#0084ff",
     *                   "check_button_color":"#ffffff",
     *                   "check_button_text":"Facebook Plugin",
     *                   "after_submit":"show",
     *                   "after_submit_color_background":"#0eb514",
     *                   "after_submit_color_headline":"#ffffff",
     *                   "after_submit_color_button_background":"#ffffff",
     *                   "after_submit_color_button_text":"#000000",
     *                   "url_open_after_submission":"",
     *                   "open_this_url":"0",
     *                   "devices":"0",
     *                   "bar_title":"Ich bin Deine Widget Ãœberschrift. Klicken um mich zu bearbeiten",
     *                   "bar_button_title":"Schick mir",
     *                   "bar_submit_title":"Danke fÃ¼r Deine Kontaktaufnahme",
     *                   "bar_submit_button_title":"Im Messenger"
     *               }
     *          ),
     *          @SWG\Property(
     *              property="sequenceID",
     *              type="integer",
     *              example=null
     *          ),
     *          @SWG\Property(
     *              property="postId",
     *              type="string",
     *              description="postId"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success. RESPONSE FOR REF URL",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="refUrl",
     *              type="string",
     *              example="refUrl"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success. RESPONSE OTHER"
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
    public function editByIDAction(Request $request, $page_id, $widgetID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $widget = $em->getRepository("AppBundle:Widget")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$widgetID]);
            if($widget instanceof Widget){
                if($request->request->has('options') && !empty($request->request->get('options'))){
                    if($widget->getType() == 7){
                        $options = $request->request->get('options');
                        $customRef = $em->getRepository("AppBundle:CustomRefParameter")->findOneBy(['page_id'=>$page->getPageId(),'widget'=>$widget]);
                        if(!empty($options['custom_ref'])){
                            $refParameter = $options['custom_ref'];
                            $issetRef = $em->getRepository("AppBundle:CustomRefParameter")->findOneBy(['page_id'=>$page->getPageId(), 'parameter'=>$options['custom_ref']]);
                            if($issetRef instanceof CustomRefParameter && $issetRef->getWidget()->getId() != $widget->getId()){
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>"Custom Ref already use"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                            if($customRef instanceof CustomRefParameter){
                                $customRef->setParameter($options['custom_ref']);
                                $em->persist($customRef);
                                $em->flush();
                            }
                            else{
                                $customRef = new CustomRefParameter($page->getPageId(), $widget, $options['custom_ref']);
                                $em->persist($customRef);
                                $em->flush();
                            }
                        }
                        else{
                            $refParameter = $widget->getId();
                            if($customRef instanceof CustomRefParameter){
                                $em->remove($customRef);
                                $em->flush();
                            }
                        }

                        $widget->setOptions($request->request->get('options'));
                        $sequence = null;
                        if(!empty($request->request->get('sequenceID'))){
                            $checkSequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$request->request->get('sequenceID')]);
                            if($checkSequence instanceof Sequences){
                                $sequence = $checkSequence;
                            }
                        }
                        $widget->setSequence($sequence);
                        $em->persist($widget);
                        $em->flush();

                        $view = $this->view([
                            "refUrl" => "https://m.me/".$page->getPageId()."?ref=".$refParameter
                        ], Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    elseif ($widget->getType() == 11){
                        if($request->request->has('postId') && !empty($request->request->get('postId'))){
                            $widget->setPostId($request->request->get('postId'));
                        }
                        else{
                            if($widget->getStatus() == true){
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>"postId is required"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                            else{
                                $widget->setPostId(null);
                            }
                        }
                    }
                    $widget->setOptions($request->request->get('options'));
                    $sequence = null;
                    if(!empty($request->request->get('sequenceID'))){
                        $checkSequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$request->request->get('sequenceID')]);
                        if($checkSequence instanceof Sequences){
                            $sequence = $checkSequence;
                        }
                    }
                    $widget->setSequence($sequence);
                    $em->persist($widget);
                    $em->flush();

                    WidgetHelper::generateWidgetFile($widget, $page->getPageId());

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"options is required"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Widget Not Found"
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
     * @param $widgetID
     * @return Response
     *
     * @Rest\Patch("/{widgetID}", requirements={"page_id"="\d+", "widgetID"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/widget/{widgetID}",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="UPDATE WIDGET STATUS BY ID BY PAGE_ID",
     *   description="The method for updating widget status by id by page_id",
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
     *      name="widgetID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="widgetID"
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
     *              description="one required"
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              example="widgetName",
     *              description="one required"
     *          ),
     *          @SWG\Property(
     *              property="flowID",
     *              type="integer",
     *              example=1,
     *              description="one required"
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
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
    public function updateByIDAction(Request $request, $page_id, $widgetID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $widget = $em->getRepository("AppBundle:Widget")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$widgetID]);
            if($widget instanceof Widget){
                //UPDATE STATUS
                if($request->request->has('status')){
                    if(is_bool($request->request->get('status'))){
                        $widget->setStatus($request->request->get('status'));
                        $em->persist($widget);
                        $em->flush();

                        WidgetHelper::generateWidgetFile($widget, $page->getPageId());

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"status should be boolean type"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                //UPDATE NAME
                elseif($request->request->has('name')){
                    if(!empty($request->request->get('name'))){
                        $widget->setName($request->request->get('name'));
                        $em->persist($widget);
                        $em->flush();

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"name should be not empty"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                //UPDATE FLOW
                elseif ($request->request->has('flowID') && !empty($request->request->get('flowID'))){
                    $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(),'id'=>$request->request->get('flowID')]);
                    if($flow instanceof Flows){
                        $flow->setType(Flows::FLOW_TYPE_WIDGET);
                        $em->persist($flow);
                        $em->flush();
                        $widget->setFlow($flow);
                        $em->persist($widget);
                        $em->flush();

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                        return $this->handleView($view);
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
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"status or name or flowID is required"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Widget Not Found"
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
     * @param $widgetID
     * @return Response
     *
     * @Rest\Delete("/{widgetID}", requirements={"page_id"="\d+", "widgetID"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/widget/{widgetID}",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="REMOVE WIDGET BY ID BY PAGE_ID",
     *   description="The method for remove widget by id by page_id",
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
     *      name="widgetID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="widgetID"
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
    public function removeByIDAction(Request $request, $page_id, $widgetID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $widget = $em->getRepository("AppBundle:Widget")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$widgetID]);
            if($widget instanceof Widget){

                $em->remove($widget->getFlow());
                $em->remove($widget);
                $share = $em->getRepository("AppBundle:CampaignShare")->findOneBy(['campaignID'=>$widget->getId()]);
                if($share instanceof CampaignShare){
                    $em->remove($share);
                }
                if(in_array($widget->getType(),[1,2,3,4,5,6,10])){
                    $fs = new Filesystem();
                    $fs->remove('widget/' . $page->getPageId() . '/' . $widget->getId() . '.js');
                }
                $em->flush();
                $view = $this->view([], Response::HTTP_NO_CONTENT);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Widget Not Found"
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
     * @param $widgetID
     * @return Response
     *
     * @Rest\Get("/{widgetID}/flow", requirements={"page_id"="\d+", "widgetID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/widget/{widgetID}/flow",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="GET WIDGET FLOW BY ID BY PAGE_ID",
     *   description="The method for getting widget flow by id by page_id",
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
     *      name="widgetID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="widgetID"
     *   ),
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *              description="widgetID"
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              description="widgetName"
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
    public function getFlowByIDAction(Request $request, $page_id, $widgetID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $widget = $em->getRepository("AppBundle:Widget")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$widgetID]);
            if($widget instanceof Widget){

                $view = $this->view([
                    'id' => $widget->getId(),
                    'name' => $widget->getName(),
                    'flow' => FlowHelper::getFlowDataResponse($em, $widget->getFlow())
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Widget Not Found"
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
     * @param $widgetID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Post("/{widgetID}/copy", requirements={"page_id"="\d+", "widgetID"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/widget/{widgetID}/copy",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="COPY WIDGET BY ID BY PAGE_ID",
     *   description="The method for copy widget by id by page_id",
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
     *      name="widgetID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="widgetID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *              description="id"
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              description="name"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              description="1 = Bar, 2 = SlideIn, 3 = Modal, 4 = Page Takeover, 5 = Button, 6 = Box, 7 = Ref Url, 8 = Ads JSON, 9 = Messenger Code, 10 = Customer Chat, 11 = Comments"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              description="status"
     *          ),
     *          @SWG\Property(
     *              property="flow",
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *                  example="flowName"
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="integer",
     *                  example=1,
     *                  description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *              ),
     *              @SWG\Property(
     *                  property="folderID",
     *                  type="integer",
     *              ),
     *              @SWG\Property(
     *                  property="modified",
     *                  type="datetime",
     *                  example="2018-09-09"
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @SWG\Property(
     *                  property="draft",
     *                  type="boolean",
     *                  example=true,
     *                  description="true = have draft, false = not have draft"
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="shows",
     *              type="integer",
     *              example=0
     *          ),
     *          @SWG\Property(
     *              property="optIn",
     *              type="integer",
     *              example=0
     *          ),
     *          @SWG\Property(
     *              property="opened",
     *              type="integer",
     *              example=0
     *          ),
     *          @SWG\Property(
     *              property="clicked",
     *              type="integer",
     *              example=0
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
    public function copyByIDAction(Request $request, $page_id, $widgetID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($page->getUser()->getProduct() instanceof DigistoreProduct) {
                $countWidget = $em->getRepository("AppBundle:Widget")->countAllByUserId($page->getUser()->getId());
                if (is_null($page->getUser()->getProduct()->getLimitCompany()) || $page->getUser()->getProduct()->getLimitCompany() > $countWidget) {
                    $widget = $em->getRepository("AppBundle:Widget")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$widgetID]);
                    if($widget instanceof Widget && $widget->getFlow() instanceof Flows){
                        if($widget->getType() == 11 && $page->getUser()->getProduct()->getComments() == false){
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Diese Funktion ist für deinen Plan nicht freigeschaltet",
                                    'type'=>'version'
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        
                        $copyFlow = new CopyFlow($em, $page, $widget->getFlow());
                        $newFlow = $copyFlow->copy(Flows::FLOW_TYPE_WIDGET);

                        $options = $widget->getOptions();
                        if ($widget->getType() == 7){
                            $options = [];
                        }
                        if($widget->getType() == 11){
                            $options['selected_post_item'] = null;
                        }

                        $newWidget = new Widget($page->getPageId(), $newFlow, $widget->getName()."-copy", $widget->getType(), $options, $widget->getSequence());
                        $em->persist($newWidget);
                        $em->flush();

                        WidgetHelper::generateWidgetFile($newWidget, $page->getPageId());

                        $view = $this->view(WidgetHelper::generateWidgetResponse($em, $newWidget), Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Widget Not Found"
                            ]
                        ], Response::HTTP_NOT_FOUND);
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
                    'message'=>"Access denied"
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @param $widgetID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/{widgetID}/adsJson", requirements={"page_id"="\d+", "widgetID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/widget/{widgetID}/adsJson",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="GET ADS JSON FOR WIDGET BY ID BY PAGE_ID NOT COMPLETE",
     *   description="The method for getting ads json for widget by id by page_id",
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
     *      name="widgetID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="widgetID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="result",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="adsJSON",
     *              type="object"
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
    public function getByIDAdsJSONAction(Request $request, $page_id, $widgetID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $widget = $em->getRepository("AppBundle:Widget")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$widgetID]);
            if($widget instanceof Widget){
                if($widget->getFlow() instanceof Flows){
                    $flowJSON = new Flow($em, $widget->getFlow(), '111111');
                    $view = $this->view($flowJSON->getJSON(), Response::HTTP_OK);
                    return $this->handleView($view);
                }
                $view = $this->view([
                    'error'=>[
                        'message'=>"Flow Not Found"
                    ]
                ], Response::HTTP_NOT_FOUND);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Widget Not Found"
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
     * @param $widgetID
     * @return Response
     *
     * @Rest\Get("/{widgetID}/refUrl", requirements={"page_id"="\d+", "widgetID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/widget/{widgetID}/refUrl",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="GET MESSENGER REF URL FOR WIDGET BY ID BY PAGE_ID NOT COMPLETE",
     *   description="The method for getting messenger ref url for widget by id by page_id",
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
     *      name="widgetID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="widgetID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="refUrl",
     *              type="string",
     *              example="refUrl"
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
    public function getByIDRefUrlAction(Request $request, $page_id, $widgetID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $widget = $em->getRepository("AppBundle:Widget")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$widgetID]);
            if($widget instanceof Widget){
                $refParameter = $widget->getId();
                $customRef = $em->getRepository("AppBundle:CustomRefParameter")->findOneBy(['page_id'=>$page->getPageId(),'widget'=>$widget]);
                if($customRef instanceof CustomRefParameter && !empty($customRef->getParameter())){
                    $refParameter = $customRef->getParameter();
                }
                $view = $this->view([
                    "refUrl" => "https://m.me/".$page->getPageId()."?ref=".$refParameter
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Widget Not Found"
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
     * @Rest\Get("/websites", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/widget/websites",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="GET WHITE LIST WEBSITES BY PAGE_ID",
     *   description="The method for getting while list websites by page_id",
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
     *              type="string",
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
    public function getWhiteListWebsitesAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $fbBot = new FbBotApp($page->getAccessToken());
            $domainWhiteList = $fbBot->getDomainWhitelist();
            $websites = [];
            if(isset($domainWhiteList['data'][0]['whitelisted_domains'])){
                $websites = $domainWhiteList['data'][0]['whitelisted_domains'];
            }

            $view = $this->view($websites, Response::HTTP_OK);
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
     * @Rest\Post("/websites", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/widget/websites",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="ADD WEBSITE TO WHITE LIST BY PAGE_ID",
     *   description="The method for add website to white list by page_id",
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
     *              property="website",
     *              type="string",
     *              example="website"
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="array",
     *          @SWG\Items(
     *              type="string"
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
    public function addWhiteListWebsitesAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('website') && !empty($request->request->get('website'))){
                $fbBot = new FbBotApp($page->getAccessToken());
                $domainWhiteList = $fbBot->getDomainWhitelist();
                if(isset($domainWhiteList['data'][0]['whitelisted_domains'])){
                    $domains = $domainWhiteList['data'][0]['whitelisted_domains'];
                    if(!in_array($request->request->get('website'), $domains)){
                        $domains[]=$request->request->get('website');
                        $result = $fbBot->setDomainWhitelist($domains);

                        if(isset($result['result']) && $result['result'] == 'success'){
                            $view = $this->view($domains, Response::HTTP_OK);
                            return $this->handleView($view);
                        }
                        else{
                            $view = $this->view($result, Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>'Website already use'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view($domainWhiteList, Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"website is required and should be empty"
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
     * @Rest\Patch("/websites", requirements={"page_id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/widget/websites",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="REMOVE WEBSITE TO WHITE LIST BY PAGE_ID",
     *   description="The method for remove website to white list by page_id",
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
     *              property="website",
     *              type="string",
     *              example="website"
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="array",
     *          @SWG\Items(
     *              type="string"
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
    public function removeWhiteListWebsitesAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('website') && !empty($request->request->get('website'))){
                $fbBot = new FbBotApp($page->getAccessToken());
                $domainWhiteList = $fbBot->getDomainWhitelist();
                if(isset($domainWhiteList['data'][0]['whitelisted_domains'])){
                    $domains = $domainWhiteList['data'][0]['whitelisted_domains'];
                    if(in_array($request->request->get('website'), $domains)){
                        $newDomains = [];
                        foreach ($domains as $domain){
                            if($domain != $request->request->get('website')){
                                $newDomains[]=$domain;
                            }
                        }
                        $result = $fbBot->setDomainWhitelist($newDomains);

                        if(isset($result['result']) && $result['result'] == 'success'){
                            $view = $this->view($newDomains, Response::HTTP_OK);
                            return $this->handleView($view);
                        }
                        else{
                            $view = $this->view($result, Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>'Website not added'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view($domainWhiteList, Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"website is required and should be empty"
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
     * @Rest\Post("/upload/file", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/widget/upload/file",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="UPLOAD FILE FOR WIDGET BY PAGE_ID",
     *   description="The method for upload file for widget by page_id",
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
     *              property="file",
     *              type="file",
     *              example="file.jpg"
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
    public function uploadFileAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->files->has('file')){
                $fileUpload = $request->files->get('file');
                if($fileUpload instanceof UploadedFile){
                    $fileName = uniqid().".".$fileUpload->getClientOriginalExtension();
                    try {
                        $fileUpload->move("widget/".$page->getPageId()."/upload",$fileName);
                    } catch (\Exception $e) {
                        $view = $this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                        return $this->handleView($view);
                    }

                    $view = $this->view([
                        'url' => $request->getSchemeAndHttpHost()."/widget/".$page->getPageId()."/upload/".$fileName,
                        'name' => $fileUpload->getClientOriginalName()
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
                    'message'=>"Access denied"
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @param $type
     * @return Response
     * @throws FacebookSDKException
     *
     * @Rest\Get("/post/{type}", requirements={"page_id"="\d+", "type"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/widget/post/{type}",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="GET POSTS BY PAGE_ID",
     *   description="The method for getting posts by page_id",
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
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="1",
     *      description="1=Publish,2=Schedule,3=Promotion"
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
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="created_time",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="full_picture",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="is_hidden",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="is_published",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="sting"
     *              ),
     *              @SWG\Property(
     *                  property="permalink_url",
     *                  type="sting"
     *              ),
     *              @SWG\Property(
     *                  property="picture",
     *                  type="sting"
     *              ),
     *              @SWG\Property(
     *                  property="promotable_id",
     *                  type="sting"
     *              ),
     *              @SWG\Property(
     *                  property="status_type",
     *                  type="sting"
     *              ),
     *              @SWG\Property(
     *                  property="is_eligible_for_promotion",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="link",
     *                  type="sting"
     *              ),
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad Request",
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
    public function getPostAction(Request $request, $page_id, $type){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if(in_array($type, [1,2,3])){
                $posts = [];
                $fb = new Facebook([
                    'app_id' => $this->container->getParameter('facebook_id'),
                    'app_secret' => $this->container->getParameter('facebook_secret'),
                    'default_graph_version' => 'v3.3'
                ]);
                try {
                    if($type == 1){
                        $response = $fb->get(
                            "/".$page->getPageId()."/posts?limit=100&fields=id,created_time,full_picture,is_hidden,is_published,message,permalink_url,picture,promotable_id,status_type,is_eligible_for_promotion,link",
                            $page->getAccessToken()
                        );
                    }
                    elseif ($type == 2){
                        $response = $fb->get(
                            "/".$page->getPageId()."/scheduled_posts?limit=100&fields=id,created_time,full_picture,is_hidden,is_published,message,permalink_url,picture,promotable_id,status_type,is_eligible_for_promotion,link",
                            $page->getAccessToken()
                        );
                    }
                    else{
                        $response = $fb->get(
                            "/".$page->getPageId()."/promotable_posts?limit=100&fields=id,created_time,full_picture,is_hidden,is_published,message,permalink_url,picture,promotable_id,status_type,is_eligible_for_promotion,link",
                            $page->getAccessToken()
                        );
                    }
                } catch(FacebookResponseException $e) {
                    $view = $this->view([
                        'error'=>[
                            'message'=>$e->getMessage()
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                } catch(FacebookSDKException $e) {
                    $view = $this->view([
                        'error'=>[
                            'message'=>$e->getMessage()
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
                $graphEdge = $response->getGraphEdge();
                if($graphEdge instanceof GraphEdge){
                    $postsData = $graphEdge->all();
                    if(!empty($postsData)){
                        foreach ($postsData as $postData){
                            if($postData instanceof GraphNode){
                                $posts[] = $postData->all();
                            }
                        }
                    }

                    $posts = $this->getAllNextPost($fb, $graphEdge, $posts);
                }


                $view = $this->view($posts, Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"type is invalid"
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
     * @throws FacebookSDKException
     *
     * @Rest\Get("/post/search", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/widget/post/search",
     *   tags={"WIDGET"},
     *   security=false,
     *   summary="SEARCH POST BY PAGE_ID",
     *   description="The method for search post by page_id",
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
     *      name="value",
     *      in="query",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="URL OR ID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="created_time",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="full_picture",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="is_hidden",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="is_published",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="sting"
     *              ),
     *              @SWG\Property(
     *                  property="permalink_url",
     *                  type="sting"
     *              ),
     *              @SWG\Property(
     *                  property="picture",
     *                  type="sting"
     *              ),
     *              @SWG\Property(
     *                  property="promotable_id",
     *                  type="sting"
     *              ),
     *              @SWG\Property(
     *                  property="status_type",
     *                  type="sting"
     *              ),
     *              @SWG\Property(
     *                  property="is_eligible_for_promotion",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="link",
     *                  type="sting"
     *              )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad Request",
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
    public function searchPostAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->query->has('value') && !empty($request->query->get('value'))){
                $postID = null;
                $parseValue = parse_url($request->query->get('value'));
                if(isset($parseValue['query']) && !empty($parseValue['query'])){
                    parse_str($parseValue['query'], $parseQuery);
                    if(isset($parseQuery['story_fbid']) && !empty($parseQuery['story_fbid']) && isset($parseQuery['id']) && !empty($parseQuery['id'])){
                        $postID = $parseQuery['id']."_".$parseQuery['story_fbid'];
                    }
                }

                if(is_null($postID) && isset($parseValue['path']) && !empty($parseValue['path'])){
                    $parsePath = explode('/', $parseValue['path']);
                    if(count($parsePath)>3 && in_array($parsePath[2], ['posts','videos']) && is_numeric($parsePath[1])  && is_numeric($parsePath[3])){
                        $postID = $parsePath[1]."_".$parsePath[3];
                    }
                }

                if(is_null($postID) && isset($parseValue['path']) && !empty($parseValue['path'])){
                    $parsePostID = explode('_', $parseValue['path']);
                    if(count($parsePostID) == 2 && is_numeric($parsePostID[0]) && is_numeric($parsePostID[1])){
                        $postID = $parsePostID[0]."_".$parsePostID[1];
                    }
                    elseif (count($parsePostID) == 1 && is_numeric($parsePostID[0])){
                        $postID = $page->getPageId()."_".$parsePostID[0];
                    }
                }

                if(!is_null($postID)){
                    $checkPostID = explode("_", $postID);
                    if($checkPostID[0] == $page->getPageId()){
                        $fb = new Facebook([
                            'app_id' => $this->container->getParameter('facebook_id'),
                            'app_secret' => $this->container->getParameter('facebook_secret'),
                            'default_graph_version' => 'v3.3'
                        ]);
                        try {
                            $response = $fb->get(
                                "/".$postID."/?fields=id,created_time,full_picture,is_hidden,is_published,message,permalink_url,picture,promotable_id,status_type,is_eligible_for_promotion,link",
                                $page->getAccessToken()
                            );
                        } catch(FacebookResponseException $e) {
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Unbekannte URL oder ID, bitte versuchen Sie es mit einer anderen"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        } catch(FacebookSDKException $e) {
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Unbekannte URL oder ID, bitte versuchen Sie es mit einer anderen"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        $graphNode = $response->getGraphNode();
                        if(!empty($graphNode->asArray())){
                            $view = $this->view($graphNode->asArray(), Response::HTTP_OK);
                            return $this->handleView($view);
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Unbekannte URL oder ID, bitte versuchen Sie es mit einer anderen"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Unbekannte URL oder ID, bitte versuchen Sie es mit einer anderen"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Unbekannte URL oder ID, bitte versuchen Sie es mit einer anderen"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"value is required"
                    ]
                ], Response::HTTP_FORBIDDEN);
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
     * @param Facebook $fb
     * @param GraphEdge $graphEdge
     * @param array $posts
     * @return array
     * @throws FacebookSDKException
     */
    private function getAllNextPost(Facebook $fb, GraphEdge $graphEdge, $posts=[]){
        if(!empty($graphEdge->getNextPageRequest())){
            $nextGraphEdge = $fb->next($graphEdge);
            if($nextGraphEdge instanceof GraphEdge){
                $postsData = $nextGraphEdge->all();
                if(!empty($postsData)){
                    foreach ($postsData as $postData){
                        if($postData instanceof GraphNode){
                            $posts[] = $postData->all();
                        }
                    }
                }
            }

            $posts = $this->getAllNextPost($fb, $nextGraphEdge, $posts);
        }

        return $posts;
    }
}
