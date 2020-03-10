<?php
/**
 * Created by PhpStorm.
 * Date: 24.09.18
 * Time: 13:04
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\SequenceShare;
use AppBundle\Entity\SequencesItems;
use AppBundle\Flows\CopyFlow;
use AppBundle\Helper\Flow\FlowHelper;
use AppBundle\Helper\PageHelper;
use AppBundle\Helper\SequencesHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class SequenceController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/automation/sequence")
 */
class SequenceController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/automation/sequence/",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="GET SEQUENCES BY PAGE_ID",
     *   description="The method for getting sequences by page_id",
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
    public function getAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $resultSequences = $em->getRepository("AppBundle:Sequences")->findBy(['page_id' => $page->getPageId()], ['id' => 'DESC']);

            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $resultSequences,
                ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
            );
            $view = $this->view([
                'items'=> SequencesHelper::generateSequenceResponse($em, $pagination->getItems()),
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
     * @Rest\Post("/", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/automation/sequence/",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="CREATE SEQUENCE BY PAGE_ID",
     *   description="The method for creating sequence by page_id",
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
     *              property="title",
     *              type="string",
     *              example="title",
     *              description="required"
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
     *              property="title",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="countSubscribers",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="countItems",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="openRate",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="ctr",
     *              type="integer"
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
     *                  type="array",
     *                  @SWG\Items(
     *                      type="string"
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="type",
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
     *   )
     * )
     */
    public function createAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($page->getUser()->getProduct() instanceof DigistoreProduct){
                $countSequence = $em->getRepository("AppBundle:Sequences")->countAllByUserId($page->getUser()->getId());
                if(is_null($page->getUser()->getProduct()->getLimitSequences()) || $page->getUser()->getProduct()->getLimitSequences() > $countSequence){
                    if($request->request->has('title') && !empty($request->request->get('title'))){
                        $sequence = new Sequences($page->getPageId(), $request->request->get('title'));
                        $em->persist($sequence);
                        $em->flush();

                        $view = $this->view(SequencesHelper::generateSequenceOneResponse($em, $sequence), Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else {
                        $view = $this->view([
                            'error'=>[
                                'message'=>'title is required and should be not empty'
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
     * @param $sequenceID
     * @return Response
     *
     * @Rest\Get("/{sequenceID}", requirements={"page_id"="\d+", "sequenceID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/automation/sequence/{sequenceID}",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="GET SEQUENCES BY SEQUENCE ID",
     *   description="The method for getting sequence by sequenceID",
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
     *      default=0,
     *      description="sequenceID"
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
     *              property="title",
     *              type="string"
     *          ),
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
     *                      property="delay",
     *                      type="object",
     *                      @SWG\Property(
     *                          property="type",
     *                          type="string",
     *                          example="days",
     *                          description="immediately minutes hours days"
     *                      ),
     *                      @SWG\Property(
     *                          property="value",
     *                          type="integer",
     *                          example=1
     *                      ),
     *                  ),
     *                  @SWG\Property(
     *                      property="number",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean",
     *                      example=true
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
     *                  ),
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
    public function getByIdAction(Request $request, $page_id, $sequenceID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$sequenceID]);
            if($sequence instanceof Sequences){
                $sequencesItemsResult = $em->getRepository("AppBundle:SequencesItems")->findBy(['sequence'=>$sequence],['number'=>'ASC']);
                $sequencesItems = [];
                if(!empty($sequencesItemsResult)){
                    foreach ($sequencesItemsResult as $sequencesItem){
                        if($sequencesItem instanceof SequencesItems){
                            $sequencesItems[] = SequencesHelper::generateSequenceItemResponse($em, $sequencesItem);
                        }
                    }
                }
                $view = $this->view([
                    'id' => $sequence->getId(),
                    'title' => $sequence->getTitle(),
                    'items' => $sequencesItems
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Sequence Not Found"
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
     * @param $sequenceID
     * @return Response
     *
     * @Rest\Patch("/{sequenceID}", requirements={"page_id"="\d+", "sequenceID"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/automation/sequence/{sequenceID}",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="EDIT SEQUENCES BY SEQUENCE ID",
     *   description="The method for edit sequence by sequenceID",
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
     *      default=0,
     *      description="sequenceID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="title",
     *              type="string",
     *              example="title",
     *              description="required"
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
    public function editByIdAction(Request $request, $page_id, $sequenceID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$sequenceID]);
            if($sequence instanceof Sequences){
                if($request->request->has('title') && !empty($request->request->get('title'))){
                    $sequence->setTitle($request->request->get('title'));
                    $em->persist($sequence);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"title is required and should be not empty"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Sequence Not Found"
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
     * @param $sequenceID
     * @return Response
     *
     * @Rest\Delete("/{sequenceID}", requirements={"page_id"="\d+", "sequenceID"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/automation/sequence/{sequenceID}",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="REMOVE SEQUENCES BY SEQUENCE ID",
     *   description="The method for remove sequence by sequenceID",
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
     *      default=0,
     *      description="sequenceID"
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
    public function removeByIdAction(Request $request, $page_id, $sequenceID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$sequenceID]);
            if($sequence instanceof Sequences){
                $em->remove($sequence);
                $em->flush();

                $view = $this->view([], Response::HTTP_NO_CONTENT);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Sequence Not Found"
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
     * @param $sequenceID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Post("/{sequenceID}/copy", requirements={"page_id"="\d+", "sequenceID"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/automation/sequence/{sequenceID}/copy",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="CODY SEQUENCES BY SEQUENCE ID",
     *   description="The method for copy sequence by sequenceID",
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
     *      default=0,
     *      description="sequenceID"
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
     *              property="title",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="countSubscribers",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="countItems",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="openRate",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="ctr",
     *              type="integer"
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
    public function copyByIdAction(Request $request, $page_id, $sequenceID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($page->getUser()->getProduct() instanceof DigistoreProduct) {
                $countSequence = $em->getRepository("AppBundle:Sequences")->countAllByUserId($page->getUser()->getId());
                if (is_null($page->getUser()->getProduct()->getLimitSequences()) || $page->getUser()->getProduct()->getLimitSequences() > $countSequence) {
                    $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$sequenceID]);
                    if($sequence instanceof Sequences){
                        $newSequence = new Sequences($page_id,$sequence->getTitle().'-copy');
                        $em->persist($newSequence);
                        $em->flush();

                        $sequencesItems = $em->getRepository("AppBundle:SequencesItems")->findBy(['sequence'=>$sequence]);
                        if(!empty($sequencesItems)){
                            foreach ($sequencesItems as $sequencesItem){
                                if($sequencesItem instanceof SequencesItems){
                                    $newFlow = null;
                                    if($sequencesItem->getFlow() instanceof Flows){
                                        $copyFlow = new CopyFlow($em, $page, $sequencesItem->getFlow());
                                        $newFlow = $copyFlow->copy(Flows::FLOW_TYPE_SEQUENCES);

                                    }
                                    $newSequencesItem = new SequencesItems($newSequence, $sequencesItem->getNumber(), $newFlow, $sequencesItem->getDelay(), $sequencesItem->getStatus());
                                    $em->persist($newSequencesItem);
                                    $em->flush();
                                }
                            }
                        }

                        $view = $this->view(SequencesHelper::generateSequenceOneResponse($em, $newSequence), Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Sequence Not Found"
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
     * @param $sequenceID
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/{sequenceID}/share", requirements={"page_id"="\d+", "sequenceID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/automation/sequence/{sequenceID}/share",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="GET SHARE SEQUENCE BY SEQUENCEID BY PAGE_ID",
     *   description="The method for getting share sequence by sequenceID by page_id",
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
     *      type="string",
     *      default="",
     *      description="sequenceID"
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
     *              property="sequenceID",
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
    public function shareByIdAction(Request $request, $page_id, $sequenceID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id' => $page->getPageId(), 'id' => $sequenceID]);
            if($sequence instanceof Sequences){
                $sequenceShare = $em->getRepository("AppBundle:SequenceShare")->findOneBy(['sequence' => $sequence]);
                if(!$sequenceShare instanceof SequenceShare){
                    $sequenceShare = new SequenceShare($sequence);
                    $em->persist($sequenceShare);
                    $em->flush();
                }

                $view = $this->view([
                    'id' => $sequenceShare->getId(),
                    'sequenceID' => $sequenceShare->getSequence()->getId(),
                    'token' => $sequenceShare->getToken(),
                    'status' => $sequenceShare->getStatus()
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Autoresponder Not Found"
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
     * @SWG\Patch(path="/v2/page/{page_id}/automation/sequence/{shareID}/share",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="UPDATE SHARE SEQUENCE BY shareID BY PAGE_ID",
     *   description="The method for update share sequence by shareID by page_id",
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
     *              property="sequenceID",
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
    public function changeShareByIdAction(Request $request, $page_id, $shareID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequenceShare = $em->getRepository("AppBundle:SequenceShare")->find($shareID);
            if($sequenceShare instanceof SequenceShare){
                if($request->request->has('status') && is_bool($request->request->get('status'))){
                    $sequenceShare->setStatus($request->request->get('status'));
                    $em->persist($sequenceShare);
                    $em->flush();

                    $view = $this->view([
                        'id' => $sequenceShare->getId(),
                        'sequenceID' => $sequenceShare->getSequence()->getId(),
                        'token' => $sequenceShare->getToken(),
                        'status' => $sequenceShare->getStatus()
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
                        'message'=>"Autoresponder Not Found"
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
     * @param $sequenceID
     * @return Response
     *
     * @Rest\Post("/{sequenceID}/items", requirements={"page_id"="\d+", "sequenceID"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/automation/sequence/{sequenceID}/items",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="CREATE SEQUENCE ITEMS BY SEQUENCE ID",
     *   description="The method for create sequence items by sequenceID",
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
     *      default=0,
     *      description="sequenceID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="number",
     *              type="integer",
     *              example=1,
     *              description="NEW NUMBER"
     *          )
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
     *              example=1
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
     *          ),
     *          @SWG\Property(
     *              property="delay",
     *              type="object",
     *              @SWG\Property(
     *                  property="type",
     *                  type="string",
     *                  example="days",
     *                  description="immediately minutes hours days"
     *              ),
     *              @SWG\Property(
     *                  property="value",
     *                  type="integer",
     *                  example=1
     *              ),
     *          ),
     *          @SWG\Property(
     *              property="number",
     *              type="integer",
     *              example=1
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true
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
    public function createItemsByIdAction(Request $request, $page_id, $sequenceID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$sequenceID]);
            if($sequence instanceof Sequences){
                if($request->request->has('number') && !empty($request->request->get('number'))){
                    $sequencesItem = new SequencesItems($sequence, $request->request->get('number'));
                    $em->persist($sequencesItem);
                    $em->flush();

                    $view = $this->view(SequencesHelper::generateSequenceItemResponse($em, $sequencesItem), Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"number is required and should be not empty"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Sequence Not Found"
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
     * @param $sequenceID
     * @return Response
     *
     * @Rest\Patch("/{sequenceID}/items", requirements={"page_id"="\d+", "sequenceID"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/automation/sequence/{sequenceID}/items",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="UPDATE SEQUENCE ITEMS NUMBER BY SEQUENCE ID",
     *   description="The method for update sequence items number by sequenceID",
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
     *      default=0,
     *      description="sequenceID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *                  example=1,
     *                  description="sequenceItemID"
     *              ),
     *              @SWG\Property(
     *                  property="number",
     *                  type="integer",
     *                  example=1,
     *                  description="NEW NUMBER"
     *              )
     *          )
     *      )
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
    public function updateItemsByIdAction(Request $request, $page_id, $sequenceID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$sequenceID]);
            if($sequence instanceof Sequences){
                if(!empty($request->request->all())){
                    foreach ($request->request->all() as $item){
                        if(isset($item['id']) && !empty($item['id']) && isset($item['number']) && !empty($item['number'])){
                            $sequencesItem = $em->getRepository("AppBundle:SequencesItems")->findOneBy(['sequence'=>$sequence,'id'=>$item['id']]);
                            if($sequencesItem instanceof SequencesItems){
                                $sequencesItem->setNumber($item['number']);
                                $em->persist($sequencesItem);
                                $em->flush();
                            }
                        }
                    }

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"data is required and should be not empty"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Sequence Not Found"
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
     * @param $sequenceID
     * @param $itemID
     * @return Response
     *
     * @Rest\Get("/{sequenceID}/items/{itemID}", requirements={"page_id"="\d+", "sequenceID"="\d+", "itemID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/automation/sequence/{sequenceID}/items/{itemID}",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="GET SEQUENCE ITEM BY ID BY SEQUENCE ID",
     *   description="The method for get sequence item by id by sequenceID",
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
     *      default=0,
     *      description="sequenceID"
     *   ),
     *   @SWG\Parameter(
     *      name="itemID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default=0,
     *      description="itemID"
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
     *          ),
     *          @SWG\Property(
     *              property="sequenceID",
     *              type="integer",
     *              example=1
     *          ),
     *          @SWG\Property(
     *              property="sequenceTitle",
     *              type="string",
     *              example="sequenceTitle"
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
    public function getItemByIdAction(Request $request, $page_id, $sequenceID, $itemID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$sequenceID]);
            if($sequence instanceof Sequences){
                $sequencesItem = $em->getRepository("AppBundle:SequencesItems")->findOneBy(['sequence'=>$sequence,'id'=>$itemID]);
                if($sequencesItem instanceof SequencesItems){
                    if($sequencesItem->getFlow() instanceof Flows){
                        $view = $this->view([
                            'id' => $sequencesItem->getId(),
                            'sequenceID' => $sequencesItem->getSequence()->getId(),
                            'sequenceTitle' => $sequencesItem->getSequence()->getTitle(),
                            'flow' => FlowHelper::getFlowDataResponse($em, $sequencesItem->getFlow())
                        ], Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Need select flow"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Sequences Item Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Sequence Not Found"
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
     * @param $sequenceID
     * @param $itemID
     * @return Response
     *
     * @Rest\Patch("/{sequenceID}/items/{itemID}", requirements={"page_id"="\d+", "sequenceID"="\d+", "itemID"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/automation/sequence/{sequenceID}/items/{itemID}",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="UPDATE SEQUENCE ITEM BY ID BY SEQUENCE ID",
     *   description="The method for update sequence item by id by sequenceID",
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
     *      default=0,
     *      description="sequenceID"
     *   ),
     *   @SWG\Parameter(
     *      name="itemID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default=0,
     *      description="itemID"
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
     *              description="one of both required, send only flow is not empty"
     *          ),
     *          @SWG\Property(
     *              property="delay",
     *              type="object",
     *              description="one of both required",
     *              @SWG\Property(
     *                  property="type",
     *                  type="string",
     *                  example="days",
     *                  description="immediately minutes hours days"
     *              ),
     *              @SWG\Property(
     *                  property="value",
     *                  type="integer",
     *                  example=1
     *             ),
     *          ),
     *          @SWG\Property(
     *              property="flowID",
     *              type="integer",
     *              example=1,
     *              description="one of both required"
     *          )
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
     *              example=1
     *          ),
     *          @SWG\Property(
     *              property="sequenceID",
     *              type="integer",
     *              example=1
     *          ),
     *          @SWG\Property(
     *              property="sequenceTitle",
     *              type="string",
     *              example="sequenceTitle"
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
    public function updateItemByIdAction(Request $request, $page_id, $sequenceID, $itemID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$sequenceID]);
            if($sequence instanceof Sequences){
                $sequencesItem = $em->getRepository("AppBundle:SequencesItems")->findOneBy(['sequence'=>$sequence,'id'=>$itemID]);
                if($sequencesItem instanceof SequencesItems){
                    //UPDATE STATUS
                    if($request->request->has('status') && is_bool($request->request->get('status'))){
                        if($sequencesItem->getFlow() instanceof Flows){
                            if($request->request->get('status') == true){
                                $sequenceFlowStartStep = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$sequencesItem->getFlow(), 'startStep'=>true]);
                                if(!$sequenceFlowStartStep instanceof FlowItems || ($sequenceFlowStartStep instanceof FlowItems && empty($sequenceFlowStartStep->getItems()))){
                                    $view = $this->view([
                                        'error'=>[
                                            'message'=>"Please create or select Flow!"
                                        ]
                                    ], Response::HTTP_BAD_REQUEST);
                                    return $this->handleView($view);
                                }
                            }
                            $sequencesItem->setStatus($request->request->get('status'));
                            $em->persist($sequencesItem);
                            $em->flush();

                            $view = $this->view([], Response::HTTP_NO_CONTENT);
                            return $this->handleView($view);
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Please create or select Flow!"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    //UPDATE DELAY
                    elseif ($request->request->has('delay') && !empty($request->request->get('delay')) && is_array($request->request->get('delay'))){
                        $delay = $request->request->get('delay');
                        if(isset($delay['type']) && in_array($delay['type'],['immediately', 'minutes', 'hours', 'days'])){
                            if($delay['type'] == 'immediately'){
                                $sequencesItem->setDelay($delay);
                                $em->persist($sequencesItem);
                                $em->flush();

                                $view = $this->view([], Response::HTTP_NO_CONTENT);
                                return $this->handleView($view);
                            }
                            else{
                                if(isset($delay['value']) && !empty($delay['value']) && $delay['value'] > 0){
                                    $sequencesItem->setDelay($delay);
                                    $em->persist($sequencesItem);
                                    $em->flush();

                                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                                    return $this->handleView($view);
                                }
                                else{
                                    $view = $this->view([
                                        'error'=>[
                                            'message'=>"delay[value] is invalid"
                                        ]
                                    ], Response::HTTP_BAD_REQUEST);
                                    return $this->handleView($view);
                                }
                            }
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"delay[type] is invalid"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    //UPDATE FLOW
                    elseif ($request->request->has('flowID') && !empty($request->request->get('flowID'))){
                        $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(),'id'=>$request->request->get('flowID')]);
                        if($flow instanceof Flows){
                            $flow->setType(Flows::FLOW_TYPE_DEFAULT_REPLY);
                            $em->persist($flow);
                            $em->flush();
                            $sequencesItem->setFlow($flow);
                            $em->persist($sequencesItem);
                            $em->flush();

                            $view = $this->view([
                                'id' => $sequencesItem->getId(),
                                'sequenceID' => $sequencesItem->getSequence()->getId(),
                                'sequenceTitle' => $sequencesItem->getSequence()->getTitle(),
                                'flow' => FlowHelper::getFlowDataResponse($em, $sequencesItem->getFlow())
                            ], Response::HTTP_OK);
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
                                'message'=>"status or delay or flowID is required and should be valid"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Sequences Item Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Sequence Not Found"
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
     * @param $sequenceID
     * @param $itemID
     * @return Response
     *
     * @Rest\Delete("/{sequenceID}/items/{itemID}", requirements={"page_id"="\d+", "sequenceID"="\d+", "itemID"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/automation/sequence/{sequenceID}/items/{itemID}",
     *   tags={"SEQUENCE"},
     *   security=false,
     *   summary="REMOVE SEQUENCE ITEM BY ID BY SEQUENCE ID",
     *   description="The method for remove sequence item by id by sequenceID",
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
     *      default=0,
     *      description="sequenceID"
     *   ),
     *   @SWG\Parameter(
     *      name="itemID",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default=0,
     *      description="itemID"
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success. AFTER DELETE UPDATE NUMBERS",
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
    public function removeItemByIdAction(Request $request, $page_id, $sequenceID, $itemID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$sequenceID]);
            if($sequence instanceof Sequences){
                $sequencesItem = $em->getRepository("AppBundle:SequencesItems")->findOneBy(['sequence'=>$sequence,'id'=>$itemID]);
                if($sequencesItem instanceof SequencesItems){
                    if($sequencesItem->getFlow() instanceof Flows){
                        $em->remove($sequencesItem->getFlow());
                    }
                    $em->remove($sequencesItem);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Sequences Item Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Sequence Not Found"
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
