<?php
/**
 * Created by PhpStorm.
 * Date: 20.12.18
 * Time: 12:47
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\Conversation;
use AppBundle\Entity\ConversationMessages;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Helper\MyFbBotApp;
use AppBundle\Helper\PageHelper;
use AppBundle\Helper\Subscriber\SubscriberActionHelper;
use AppBundle\Helper\Webhook\ZapierHelper;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use pimax\Messages\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class ConversationController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/conversation")
 */
class ConversationController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/conversation/",
     *   tags={"CONVERSATION"},
     *   security=false,
     *   summary="GET CONVERSATIONS BY PAGE_ID",
     *   description="The method for getting conversations by page_id",
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
     *      name="status",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="true",
     *      description="search by status, true | false"
     *   ),
     *   @SWG\Parameter(
     *      name="_sort",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="desc",
     *      description="sort by created, desc | asc"
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
     *                      description="conversationID"
     *                  ),
     *                  @SWG\Property(
     *                      property="subscriber",
     *                      type="object",
     *                      @SWG\Property(
     *                          property="id",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="subscriber_id",
     *                          type="string",
     *                          description="user for url"
     *                      ),
     *                      @SWG\Property(
     *                          property="firstName",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="lastName",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="avatar",
     *                          type="string"
     *                      ),
     *                  ),
     *                  @SWG\Property(
     *                      property="message",
     *                      type="object",
     *                      @SWG\Property(
     *                          property="items",
     *                          type="object",
     *                      ),
     *                      @SWG\Property(
     *                          property="text",
     *                          type="string",
     *                      ),
     *                      @SWG\Property(
     *                          property="type",
     *                          type="integer",
     *                      ),
     *                      @SWG\Property(
     *                          property="created",
     *                          type="datetime",
     *                      ),
     *                  ),
     *                  @SWG\Property(
     *                      property="updated",
     *                      type="datetime"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean"
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
            $status = true;
            if($request->query->has('status') && ($request->query->get('status') == 'false' || $request->query->get('status') == false)){
                $status = false;
            }
            $sort = 'DESC';
            if($request->query->has('_sort') && $request->query->get('_sort') == 'asc'){
                $sort = 'ASC';
            }
            $conversations = $em->getRepository('AppBundle:Conversation')->findBy(['page_id'=>$page->getPageId(), 'status'=>$status], ['updated'=>$sort]);
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $conversations,
                ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
            );
            $view = $this->view([
                'items' => $this->generateConversationResponse($em, $pagination->getItems()),
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
     * @SWG\Get(path="/v2/page/{page_id}/conversation/search",
     *   tags={"CONVERSATION"},
     *   security=false,
     *   summary="SEARCH CONVERSATIONS MESSAGE BY PAGE_ID",
     *   description="The method for search conversations message by page_id",
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
     *      name="search",
     *      in="query",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="search"
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
     *                      type="integer",
     *                      description="conversationID"
     *                  ),
     *                  @SWG\Property(
     *                      property="subscriber",
     *                      type="object",
     *                      @SWG\Property(
     *                          property="id",
     *                          type="integer"
     *                      ),
     *                      @SWG\Property(
     *                          property="subscriber_id",
     *                          type="string",
     *                          description="user for url"
     *                      ),
     *                      @SWG\Property(
     *                          property="firstName",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="lastName",
     *                          type="string"
     *                      ),
     *                      @SWG\Property(
     *                          property="avatar",
     *                          type="string"
     *                      ),
     *                  ),
     *                  @SWG\Property(
     *                      property="message",
     *                      type="object",
     *                      @SWG\Property(
     *                          property="items",
     *                          type="object",
     *                      ),
     *                      @SWG\Property(
     *                          property="text",
     *                          type="string",
     *                      ),
     *                      @SWG\Property(
     *                          property="type",
     *                          type="integer",
     *                      ),
     *                      @SWG\Property(
     *                          property="created",
     *                          type="datetime",
     *                      ),
     *                  ),
     *                  @SWG\Property(
     *                      property="updated",
     *                      type="datetime"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean"
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
    public function searchAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->query->has('search') && !empty($request->query->get('search'))){
                $conversationMessages = $em->getRepository("AppBundle:ConversationMessages")->findMessagesByPageId($page->getPageId(), $request->query->get('search'));
                $paginator  = $this->get('knp_paginator');
                $pagination = $paginator->paginate(
                    $conversationMessages,
                    ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                    ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
                );
                $view = $this->view([
                    'items' => $this->generateConversationSearchResponse($pagination->getItems()),
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
                        'message'=>"search is required"
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
     * @Rest\Patch("/all", requirements={"page_id"="\d+","subscriberID"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/conversation/all",
     *   tags={"CONVERSATION"},
     *   security=false,
     *   summary="CHANGE ALL CONVERSATIONS STATUS BY PAGE_ID",
     *   description="The method for change all conversations status by page_id",
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
     *              description="required"
     *          )
     *      ),
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
     *   )
     * )
     */
    public function changeAllAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('status') && is_bool($request->request->get('status'))){
                $em->getRepository("AppBundle:Conversation")->changeAllStatusByPageID($page->getPageId(), $request->request->get('status'));

                $view = $this->view([], Response::HTTP_NO_CONTENT);
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
                    'message'=>"Access denied"
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @param $subscriberID
     * @return Response
     *
     * @Rest\Get("/{subscriberID}", requirements={"page_id"="\d+","subscriberID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/conversation/{subscriberID}",
     *   tags={"CONVERSATION"},
     *   security=false,
     *   summary="GET CONVERSATIONS MESSAGES BY SUBSCRIBER ID BY PAGE_ID",
     *   description="The method for get conversations messages by subscriberID by page_id",
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
     *      name="subscriberID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="subscriberID"
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
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="firstName",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="lastName",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="avatar",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="message",
     *                      type="object"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      description="1=user | 2=page"
     *                  ),
     *                  @SWG\Property(
     *                      property="created",
     *                      type="datetime"
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
    public function getMessageBySubscriberIdAction(Request $request, $page_id, $subscriberID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(), 'subscriber_id'=>$subscriberID]);
            if($subscriber instanceof Subscribers){
                $conversation = $em->getRepository("AppBundle:Conversation")->findOneBy(['subscriber'=>$subscriber]);
                if($conversation instanceof Conversation){
                    $conversationMessages = $em->getRepository("AppBundle:ConversationMessages")->findBy(['conversation'=>$conversation],['created'=>'DESC']);
                    $paginator  = $this->get('knp_paginator');
                    $pagination = $paginator->paginate(
                        $conversationMessages,
                        ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                        ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
                    );
                    $view = $this->view([
                        'items' => $this->generateConversationMessageResponse($pagination->getItems()),
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
                            'message'=>"Conversation Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Subscriber Not Found"
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
     * @param $subscriberID
     * @return Response
     *
     * @Rest\Patch("/{subscriberID}", requirements={"page_id"="\d+","subscriberID"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/conversation/{subscriberID}",
     *   tags={"CONVERSATION"},
     *   security=false,
     *   summary="CHANGE CONVERSATION STATUS BY SUBSCRIBER ID BY PAGE_ID",
     *   description="The method for change conversation status by subscriberID by page_id",
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
     *      name="subscriberID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="subscriberID"
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
     *              description="required"
     *          )
     *      ),
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
     *   )
     * )
     */
    public function changeBySubscriberIdAction(Request $request, $page_id, $subscriberID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(), 'subscriber_id'=>$subscriberID]);
            if($subscriber instanceof Subscribers){
                $conversation = $em->getRepository("AppBundle:Conversation")->findOneBy(['subscriber'=>$subscriber]);
                if($conversation instanceof Conversation){
                    if($request->request->has('status') && is_bool($request->request->get('status'))){
                        $needTrigger = false;
                        if($request->request->get('status') == true && $conversation->getStatus() == false){
                            $needTrigger = true;
                        }
                        $conversation->setStatus($request->request->get('status'));
                        $em->persist($conversation);
                        $em->flush();

                        if($needTrigger == true){
                            //ZAPIER TRIGGER
                            ZapierHelper::triggerChatOpen($em, $page, $subscriber);
                        }

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
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
                            'message'=>"Conversation Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Subscriber Not Found"
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
     * @param $subscriberID
     * @return Response
     *
     * @Rest\Post("/{subscriberID}/send", requirements={"page_id"="\d+","subscriberID"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/conversation/{subscriberID}/send",
     *   tags={"CONVERSATION"},
     *   security=false,
     *   summary="SEND MESSAGE BY SUBSCRIBER ID BY PAGE_ID",
     *   description="The method for send message by subscriberID by page_id",
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
     *      name="subscriberID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="subscriberID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="message",
     *              description="required"
     *          )
     *      ),
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
     *   )
     * )
     */
    public function sendBySubscriberIdAction(Request $request, $page_id, $subscriberID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(), 'subscriber_id'=>$subscriberID]);
            if($subscriber instanceof Subscribers){
                $conversation = $em->getRepository("AppBundle:Conversation")->findOneBy(['subscriber'=>$subscriber]);
                if($conversation instanceof Conversation){
                    if($request->request->has('message') && !empty($request->request->get('message'))){

                        $result_send = SubscriberActionHelper::sendMessageText($page, $subscriber, $request->request->get('message'));
                        if(isset($result_send['message_id'])){
                            $view = $this->view([], Response::HTTP_NO_CONTENT);
                        }
                        else{
                            $view = $this->view($result_send, Response::HTTP_BAD_REQUEST);
                        }
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"message is required and should be not empty"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Conversation Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Subscriber Not Found"
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
     * @param EntityManager $em
     * @param array $conversations
     * @return array
     */
    private function generateConversationResponse(EntityManager $em, array $conversations){
        $items = [];
        if(!empty($conversations)){
            foreach ($conversations as $conversation){
                if($conversation instanceof Conversation){
                    $message = $em->getRepository("AppBundle:ConversationMessages")->findOneBy(['conversation'=>$conversation, 'type'=>1], ['created'=>'DESC']);
                    $items[] = [
                        "id" => $conversation->getId(),
                        "subscriber" => [
                            "id" => $conversation->getSubscriber()->getId(),
                            "subscriber_id" => $conversation->getSubscriber()->getSubscriberId(),
                            "firstName" => $conversation->getSubscriber()->getFirstName(),
                            "lastName" =>$conversation->getSubscriber()->getLastName(),
                            "avatar" => $conversation->getSubscriber()->getAvatar()
                        ],
                        "message" => [
                            'items' => ($message instanceof ConversationMessages) ? $message->getItems() : null,
                            'text' => ($message instanceof ConversationMessages) ? $message->getText() : null,
                            'type' => ($message instanceof ConversationMessages) ? $message->getType() : null,
                            'created' => ($message instanceof ConversationMessages) ? $message->getCreated() : null,
                        ],
                        "updated" => $conversation->getUpdated(),
                        "status" => $conversation->getStatus()
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * @param array $items
     * @return array
     */
    private function generateConversationSearchResponse(array $items){
        $result = [];
        if(!empty($items)){
            foreach ($items as $item){
                $result[] = [
                    "id" => $item['id'],
                    "subscriber" => [
                        "id" => $item['sub_id'],
                        "subscriber_id" => $item['subscriber_id'],
                        "firstName" => $item['firstName'],
                        "lastName" => $item['lastName'],
                        "avatar" => $item['avatar'],
                    ],
                    "message" => [
                        'items' => json_decode($item['items'], true),
                        'text' => $item['text'],
                        'type' => $item['type'],
                        'created' => $item['created']
                    ],
                    "updated" => $item['created'],
                    "status" => $item['status']
                ];
            }
        }

        return $result;
    }

    /**
     * @param array $items
     * @return array
     */
    private function generateConversationMessageResponse(array $items){
        $conversationMessages = [];
        if(!empty($items)){
            foreach ($items as $item){
                if($item instanceof ConversationMessages){
                    $conversationMessages[] = [
                        'id' => $item->getId(),
                        'firstName' => $item->getConversation()->getSubscriber()->getFirstName(),
                        'lastName' => $item->getConversation()->getSubscriber()->getLastName(),
                        'avatar' => $item->getConversation()->getSubscriber()->getAvatar(),
                        'message' => $item->getItems(),
                        'type' => $item->getType(),
                        'created' => $item->getCreated()
                    ];
                }
            }
        }
        return $conversationMessages;
    }
}
