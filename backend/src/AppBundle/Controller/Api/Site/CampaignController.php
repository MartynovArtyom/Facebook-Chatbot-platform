<?php


namespace AppBundle\Controller\Api\Site;


use AppBundle\Entity\CampaignShare;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\Widget;
use AppBundle\Helper\PageHelper;
use AppBundle\Helper\SequencesHelper;
use AppBundle\Helper\WidgetHelper;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CampaignController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/campaign")
 */
class CampaignController extends FOSRestController
{

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/campaign/",
     *   tags={"CAMPAIGN"},
     *   security=false,
     *   summary="GET CAMPAIGN BY PAGE_ID",
     *   description="The method for getting campaign by page_id",
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
     *              property="sequences",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="title",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="countSubscribers",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="countItems",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="openRate",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="ctr",
     *                      type="integer"
     *                  ),
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="widgets",
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
     *                      description="1 = Bar, 2 = SlideIn, 3 = Modal, 4 = Page Takeover, 5 = Button, 6 = Box, 7 = Ref Url, 8 = Ads JSON, 9 = Messenger Code, 10 = Customer Chat, 11 = Comments"
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
    public function listAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequences = $em->getRepository("AppBundle:Sequences")->findBy(['page_id' => $page->getPageId()], ['id' => 'DESC']);
            $widgets = $em->getRepository("AppBundle:Widget")->findBy(['page_id' => $page->getPageId()], ['id' => 'DESC']);

            $view = $this->view([
                'sequences' => SequencesHelper::generateSequenceResponse($em, $sequences),
                'widgets' => WidgetHelper::generateWidgetsResponse($em, $widgets)
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
     * @Rest\Get("/search", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/campaign/search",
     *   tags={"CAMPAIGN"},
     *   security=false,
     *   summary="SEARCH CAMPAIGN BY PAGE_ID",
     *   description="The method for search campaign by page_id",
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
     *   @SWG\Parameter(
     *      name="search",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="search by name"
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
     *                      property="campaign_type",
     *                      type="string",
     *                      example="widget",
     *                      description="widget OR sequence"
     *                  ),
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
    public function searchAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $resultSearch = [];

            $widgets = $em->getRepository("AppBundle:Widget")->getWidgetsByPageId($page->getPageId(), $request->query->all());
            if(!empty($widgets)){
                foreach ($widgets as $widget){
                    if($widget instanceof Widget){
                        $widgetData = WidgetHelper::generateWidgetResponse($em, $widget);
                        $widgetData['campaign_type'] = 'widget';
                        $resultSearch[] = $widgetData;
                    }
                }
            }

            $sequences = $em->getRepository("AppBundle:Sequences")->getAllByPageID($page->getPageId(), $request->query->all());
            if(!empty($sequences)){
                foreach ($sequences as $sequence){
                    if($sequence instanceof Sequences){
                        $sequenceData = SequencesHelper::generateSequenceOneResponse($em, $sequence);
                        $sequenceData['campaign_type'] = 'sequence';
                        $resultSearch[] = $sequenceData;
                    }
                }
            }

            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $resultSearch,
                ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
            );
            $view = $this->view([
                'items' => $pagination->getItems(),
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
     * @param $campaignID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/{campaignID}/share", requirements={"page_id"="\d+", "campaignID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/campaign/{campaignID}/share",
     *   tags={"CAMPAIGN"},
     *   security=false,
     *   summary="GET SHARE CAMPAIGN BY CAMPAIGNID BY PAGE_ID",
     *   description="The method for getting share campaign by campaignID by page_id",
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
     *      name="campaignID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="campaignID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="campaignID",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="token",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean"
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
    public function shareAction(Request $request, $page_id, $campaignID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $campaign = $em->getRepository("AppBundle:Widget")->findOneBy(['page_id'=>$page->getPageId(), 'id' => $campaignID]);
            if($campaign instanceof Widget){
                $campaignShare = $em->getRepository("AppBundle:CampaignShare")->findOneBy(['campaignID' => $campaign->getId()]);
                if(!$campaignShare instanceof CampaignShare){
                    $campaignShare = new CampaignShare($campaign->getId());
                    $em->persist($campaignShare);
                    $em->flush();
                }

                $view = $this->view([
                    'id' => $campaignShare->getId(),
                    'campaignID' => $campaignShare->getCampaignID(),
                    'token' => $campaignShare->getToken(),
                    'status' => $campaignShare->getStatus()
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Campaign Not Found"
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
     * @param $shareID
     * @return Response
     *
     * @Rest\Patch("/{shareID}/share", requirements={"page_id"="\d+", "shareID"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/campaign/{shareID}/share",
     *   tags={"CAMPAIGN"},
     *   security=false,
     *   summary="UPDATE SHARE CAMPAIGN BY shareID BY PAGE_ID",
     *   description="The method for update share campaign by shareID by page_id",
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
     *      name="shareID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="shareID"
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
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="campaignID",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="token",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean"
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
     *   )
     * )
     */
    public function changeShareAction(Request $request, $page_id, $shareID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $campaignShare = $em->getRepository("AppBundle:CampaignShare")->find($shareID);
            if($campaignShare instanceof CampaignShare){
                if($request->request->has('status') && is_bool($request->request->get('status'))){
                    $campaignShare->setStatus($request->request->get('status'));
                    $em->persist($campaignShare);
                    $em->flush();

                    $view = $this->view([
                        'id' => $campaignShare->getId(),
                        'campaignID' => $campaignShare->getCampaignID(),
                        'token' => $campaignShare->getToken(),
                        'status' => $campaignShare->getStatus()
                    ], Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"status is required and should be boolean type"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Campaign Not Found"
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
