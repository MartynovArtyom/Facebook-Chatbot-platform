<?php
/**
 * Created by PhpStorm.
 * Date: 19.10.18
 * Time: 13:54
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\Flows;
use AppBundle\Entity\Folders;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\SequencesItems;
use AppBundle\Entity\Subscribers;
use AppBundle\Flows\CopyFlow;
use AppBundle\Flows\Flow;
use AppBundle\Helper\Flow\FlowHelper;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class FlowsController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/flow")
 */
class FlowsController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/flow/",
     *   tags={"FLOWS"},
     *   security=false,
     *   summary="GET FLOWS BY PAGE_ID",
     *   description="The method for getting flows by page_id",
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
     *      name="modified",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="DESC",
     *      description="sort by modified = DESC OR ASC"
     *   ),
     *   @SWG\Parameter(
     *      name="status",
     *      in="query",
     *      required=false,
     *      type="boolean",
     *      default=true,
     *      description="search by status"
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
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="flowName"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      example=1,
     *                      description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *                  ),
     *                  @SWG\Property(
     *                      property="folderID",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="modified",
     *                      type="datetime",
     *                      example="2018-09-09"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean",
     *                      example=true
     *                  ),
     *                  @SWG\Property(
     *                      property="draft",
     *                      type="boolean",
     *                      example=true,
     *                      description="true = have draft, false = not have draft"
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
    public function getAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $resultFlows = $em->getRepository("AppBundle:Flows")->getFlowsByPageId($page->getPageId(), $request->query->all());

            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $resultFlows,
                ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
            );
            $view = $this->view([
                'items'=>FlowHelper::getFlowsArrayResponse($em, $pagination->getItems()),
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
     *
     * @Rest\Get("/search", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/flow/search",
     *   tags={"FLOWS"},
     *   security=false,
     *   summary="SEARCH FLOWS BY PAGE_ID",
     *   description="The method for search flows by page_id",
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
     *      description="search by title"
     *   ),
     *   @SWG\Parameter(
     *      name="modified",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="DESC",
     *      description="sor by modified = DESC OR ASC"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="flowName"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      example=1,
     *                      description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *                  ),
     *                  @SWG\Property(
     *                      property="folderID",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="modified",
     *                      type="datetime",
     *                      example="2018-09-09"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean",
     *                      example=true
     *                  ),
     *                  @SWG\Property(
     *                      property="draft",
     *                      type="boolean",
     *                      example=true,
     *                      description="true = have draft, false = not have draft"
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
     *         )
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
            $flows = $em->getRepository("AppBundle:Flows")->getFlowsByPageId($page->getPageId(), $request->query->all());

            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $flows,
                ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
            );
            $view = $this->view([
                'items'=>FlowHelper::getFlowsArrayResponse($em, $pagination->getItems()),
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
     *
     * @Rest\Get("/type", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/flow/type",
     *   tags={"FLOWS"},
     *   security=false,
     *   summary="GET FLOWS BY TYPE BY PAGE_ID",
     *   description="The method for getting flows by type by page_id",
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
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="2 = default_reply 3 = welcome_message 4 = keywords 6 = menu 7 = widget"
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
     *      name="modified",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="DESC",
     *      description="sor by modified = DESC OR ASC"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="flowName"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      example=1,
     *                      description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *                  ),
     *                  @SWG\Property(
     *                      property="folderID",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="modified",
     *                      type="datetime",
     *                      example="2018-09-09"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean",
     *                      example=true
     *                  ),
     *                  @SWG\Property(
     *                      property="draft",
     *                      type="boolean",
     *                      example=true,
     *                      description="true = have draft, false = not have draft"
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
    public function getByTypeAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $resultFlows = $em->getRepository("AppBundle:Flows")->getFlowsByPageId($page->getPageId(), $request->query->all());

            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $resultFlows,
                ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
            );
            $view = $this->view([
                'items'=>FlowHelper::getFlowsArrayResponse($em, $pagination->getItems()),
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
     * @param $sequenceID
     * @return Response
     *
     * @Rest\Get("/sequences/{sequenceID}", requirements={"page_id"="\d+", "sequenceID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/flow/sequences/{sequenceID}",
     *   tags={"FLOWS"},
     *   security=false,
     *   summary="GET FLOWS BY TYPE BY PAGE_ID",
     *   description="The method for getting flows by type by page_id",
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
     *      name="sequenceID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="sequenceID"
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
     *      name="modified",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="DESC",
     *      description="sor by modified = DESC OR ASC"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="flowName"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      example=1,
     *                      description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *                  ),
     *                  @SWG\Property(
     *                      property="folderID",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="modified",
     *                      type="datetime",
     *                      example="2018-09-09"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean",
     *                      example=true
     *                  ),
     *                  @SWG\Property(
     *                      property="draft",
     *                      type="boolean",
     *                      example=true,
     *                      description="true = have draft, false = not have draft"
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
    public function getBySequenceIDAction(Request $request, $page_id, $sequenceID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$sequenceID]);
            if($sequence instanceof Sequences){
                $sequenceItems = $em->getRepository("AppBundle:SequencesItems")->findBy(['sequence'=>$sequence]);
                $flows = [];
                if(!empty($sequenceItems)){
                    foreach ($sequenceItems as $sequenceItem){
                        if($sequenceItem instanceof  SequencesItems && $sequenceItem->getFlow() instanceof Flows){
                            $flows[] = FlowHelper::getFlowResponse($em, $sequenceItem->getFlow());
                        }
                    }
                }
                //SORTING
                if($request->query->has('modified') && $request->query->get('modified') == 'ASC'){
                    usort($flows, function($a, $b) {
                        if(isset($a['modified']) && $a['modified'] instanceof \DateTime && isset($b['modified']) && $b['modified'] instanceof \DateTime){
                            return $a['modified']->format('Y-m-d H:i:s') > $b['modified']->format('Y-m-d H:i:s');
                        }
                        else{
                            return true;
                        }
                    });
                }
                else{
                    usort($flows, function($a, $b) {
                        if(isset($a['modified']) && $a['modified'] instanceof \DateTime && isset($b['modified']) && $b['modified'] instanceof \DateTime){
                            return $a['modified']->format('Y-m-d H:i:s') < $b['modified']->format('Y-m-d H:i:s');
                        }
                        else{
                            return true;
                        }
                    });
                }


                $paginator  = $this->get('knp_paginator');
                $pagination = $paginator->paginate(
                    $flows,
                    ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                    ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
                );
                $view = $this->view([
                    'items'=>$flows,
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
                        'message'=>"Sequences Not Found"
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
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/trash", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/flow/trash",
     *   tags={"FLOWS"},
     *   security=false,
     *   summary="GET TRASH FLOWS BY PAGE_ID",
     *   description="The method for getting trash flows by page_id",
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
     *      name="modified",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="DESC",
     *      description="sor by modified = DESC OR ASC"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="items",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="flowName"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      example=1,
     *                      description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *                  ),
     *                  @SWG\Property(
     *                      property="folderID",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="modified",
     *                      type="datetime",
     *                      example="2018-09-09"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean",
     *                      example=true
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
    public function getTrashAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $resultFlows = $em->getRepository("AppBundle:Flows")->getFlowsByPageId($page->getPageId(), array_merge(['status'=>'false'],$request->query->all()));

            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $resultFlows,
                ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
            );
            $view = $this->view([
                'items'=>FlowHelper::getFlowsArrayResponse($em, $pagination->getItems()),
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
     * @SWG\Post(path="/v2/page/{page_id}/flow/",
     *   tags={"FLOWS"},
     *   security=false,
     *   summary="CREATE FLOW BY PAGE_ID",
     *   description="The method for create flow by page_id",
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
     *              example="flow name"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *          ),
     *          @SWG\Property(
     *              property="folderID",
     *              type="integer",
     *              description="folderID"
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
     *              example=1
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              example="flowName"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *          ),
     *          @SWG\Property(
     *              property="folderID",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="modified",
     *              type="datetime",
     *              example="2018-09-09"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true
     *          ),
     *          @SWG\Property(
     *              property="draft",
     *              type="boolean",
     *              example=true,
     *              description="true = have draft, false = not have draft"
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
    public function createAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('name') && !empty($request->request->get('name'))
                && $request->request->has('type') && in_array($request->request->get('type'),[1,2,3,4,5,6,7,8])
            ){
                $folder = null;
                if($request->request->has('folderID') && !empty($request->request->get('folderID'))){
                    $folder = $em->getRepository("AppBundle:Folders")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$request->request->get('folderID')]);
                    if(!$folder instanceof Folders){
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Folder Not Found"
                            ]
                        ], Response::HTTP_NOT_FOUND);
                        return $this->handleView($view);
                    }
                }
                $flow = new Flows($page->getPageId(),$request->request->get('name'), $request->request->get('type'), $folder);
                $em->persist($flow);
                $em->flush();

                $view = $this->view(FlowHelper::getFlowResponse($em, $flow), Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"name and type is required field and should be not empty"
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
     * @param $flowID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Patch("/{flowID}", requirements={"page_id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/flow/{flowID}",
     *   tags={"FLOWS"},
     *   security=false,
     *   summary="UPDATE FLOW BY ID BY PAGE_ID",
     *   description="The method for update flow by id by page_id",
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
     *              property="name",
     *              type="string",
     *              example="flow name"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              description="true = active , false = trash"
     *          ),
     *          @SWG\Property(
     *              property="folder",
     *              type="integer",
     *              example=1
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
    public function updateByIdAction(Request $request, $page_id, $flowID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){
                //UPDATE NAME
                if($request->request->has('name') || $request->request->has('status') || $request->request->has('folder')) {
                    if ($request->request->has('name')) {
                        if (!empty($request->request->get('name'))) {
                            $flow->setName($request->request->get('name'));
                            $flow->setModified(new \DateTime());
                            $em->persist($flow);
                            $em->flush();
                        } else {
                            $view = $this->view([
                                'error' => [
                                    'message' => "name should be not empty"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    //UPDATE STATUS
                    if ($request->request->has('status')) {
                        if (is_bool($request->request->get('status'))) {
                            $flow->setStatus($request->request->get('status'));
                            $flow->setModified(new \DateTime());
                            $em->persist($flow);
                            $em->flush();
                        } else {
                            $view = $this->view([
                                'error' => [
                                    'message' => "status is invalid value"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    //UPDATE FOLDER
                    if ($request->request->has('folder')){
                        if(empty($request->request->get('folder'))){
                            $folder = null;
                        }
                        else{
                            $folder = $em->getRepository("AppBundle:Folders")->find($request->request->get('folder'));
                            if(!$folder instanceof Folders){
                                $view = $this->view([
                                    'error' => [
                                        'message' => "Folder not found"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        $flow->setFolder($folder);
                        $em->persist($flow);
                        $em->flush();
                    }

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"name or status or folder is required field"
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
     * @param Request $request
     * @param $page_id
     * @param $flowID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Delete("/{flowID}", requirements={"page_id"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/flow/{flowID}",
     *   tags={"FLOWS"},
     *   security=false,
     *   summary="DELETE FLOW BY ID BY PAGE_ID",
     *   description="The method for delete flow by id by page_id",
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
     *      name="trash",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="true",
     *      description="trash or remove"
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
    public function removeByIdAction(Request $request, $page_id, $flowID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){
                if($request->query->has('trash') && $request->query->get('trash') == 'false'){
                    $em->remove($flow);
                    $em->flush();
                }
                else{
                    if($flow->getStatus() == true){
                        $flow->setStatus(false);
                        $flow->setModified(new \DateTime());
                        $em->persist($flow);
                        $em->flush();
                    }
                    else{
                        $em->remove($flow);
                        $em->flush();
                    }
                }

                $view = $this->view([], Response::HTTP_NO_CONTENT);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Flow Not Found"
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
     * @param Request $request
     * @param $page_id
     * @param $flowID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Post("/{flowID}/copy", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/flow/{flowID}/copy",
     *   tags={"FLOWS"},
     *   security=false,
     *   summary="COPY FLOW BY ID BY PAGE_ID",
     *   description="The method for copy flow by id by page_id",
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
     *              example=1
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              example="flowName"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = content 2 = default_reply 3 = welcome_message 4 = keywords 5 = sequences 6 = menu 7 = widget 8 = broadcast"
     *          ),
     *          @SWG\Property(
     *              property="folderID",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="modified",
     *              type="datetime",
     *              example="2018-09-09"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true
     *          ),
     *          @SWG\Property(
     *              property="draft",
     *              type="boolean",
     *              example=true,
     *              description="true = have draft, false = not have draft"
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
    public function copyByIdAction(Request $request, $page_id, $flowID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){
                $copyFlow = new CopyFlow($em, $page, $flow);
                $newFlow = $copyFlow->copy();

                $view = $this->view(FlowHelper::getFlowResponse($em, $newFlow), Response::HTTP_OK);
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/{flowID}/preview", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/flow/{flowID}/preview",
     *   tags={"FLOWS"},
     *   security=false,
     *   summary="SEND PREVIEW FLOW BY ID BY PAGE_ID",
     *   description="The method for send preview flow by id by page_id",
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
     *      response=204,
     *      description="Success.",
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAD REQUEST",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="result",
     *              type="boolean"
     *          ),
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
    public function previewByIdAction(Request $request, $page_id, $flowID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$flowID]);
            if($flow instanceof Flows){
                $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(), 'firstName'=>$this->getUser()->getFirstName(), 'lastName'=>$this->getUser()->getLastName()]);
                if($subscriber instanceof Subscribers){
                    $flowsSend = new Flow($em, $flow, $subscriber);
                    $result = $flowsSend->sendStartStep();
                    if(isset($result['result']) && is_bool($result['result'])){
                        if($result['result'] == true){
                            $view = $this->view([], Response::HTTP_NO_CONTENT);
                            return $this->handleView($view);
                        }
                        else{
                            if(isset($result['fb_id']) && isset($result['fb_id']['error']) && is_array($result['fb_id']['error'])){
                                $view = $this->view(['result'=>false, 'error'=>$result['fb_id']['error']], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                            else{
                                $view = $this->view($result, Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                    }
                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"subscriber_not_found"
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
