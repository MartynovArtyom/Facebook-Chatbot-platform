<?php
/**
 * Created by PhpStorm.
 * Date: 15.11.18
 * Time: 11:22
 */

namespace AppBundle\Controller\Api\Admin;

use AppBundle\Entity\Page;
use AppBundle\Helper\MyFbBotApp;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class PageController
 * @package AppBundle\Controller\Api\Admin
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
     * @SWG\Get(path="/v2/admin/page/",
     *   tags={"ADMIN PAGE"},
     *   security=false,
     *   summary="GET PAGEs FOR ADMIN",
     *   description="The method for getting pages for admin",
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
     *      description="search"
     *   ),
     *   @SWG\Parameter(
     *      name="status",
     *      in="query",
     *      required=false,
     *      type="boolean",
     *      description="status"
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
     *                      property="page_id",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="title",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="avatar",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean"
     *                  ),
     *                  @SWG\Property(
     *                      property="created",
     *                      type="datetime",
     *                      example="2018-09-09"
     *                  ),
     *                  @SWG\Property(
     *                      property="userID",
     *                      type="integer"
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
     *                      property="countSubscribers",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="limitSubscribers",
     *                      type="integer"
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
    public function getAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository("AppBundle:Page")->getAllForAdmin($request->query->all());

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $pages,
            ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
            ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
        );
        $view = $this->view([
            'items'=> PageHelper::getPagesResponse($em, $pagination->getItems()),
            'pagination' => [
                'current_page_number' => $pagination->getCurrentPageNumber(),
                'total_count' => $pagination->getTotalItemCount(),
            ]
        ], Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Get("/{id}", requirements={"id"="\d+"})
     * @SWG\Get(path="/v2/admin/page/{id}",
     *   tags={"ADMIN PAGE"},
     *   security=false,
     *   summary="GET PAGE BY ID FOR ADMIN",
     *   description="The method for getting page by id for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="id"
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
     *              property="page_id",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="title",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="avatar",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean"
     *          ),
     *          @SWG\Property(
     *              property="created",
     *              type="datetime",
     *              example="2018-09-09"
     *          ),
     *          @SWG\Property(
     *              property="userID",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="firstName",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="lastName",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="countSubscribers",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="countFlows",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="countWidgets",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="countSequences",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="mapSubscribers",
     *              type="object"
     *          ),
     *          @SWG\Property(
     *              property="limitSubscribers",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="product",
     *              type="object",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="id",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="productId",
     *              ),
     *              @SWG\Property(
     *                  type="string",
     *                  property="name",
     *              ),
     *              @SWG\Property(
     *                  type="string",
     *                  property="label",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="limitSubscribers",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="limitCompany",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="limitSequences",
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="comments",
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="downloadPsid",
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="zapier",
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="admins",
     *              ),
     *              @SWG\Property(
     *                  type="string",
     *                  property="quentnUrl",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="limitedQuentn",
     *              )
     *          ),
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
    public function getByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository("AppBundle:Page")->find($id);
        if($page instanceof Page){

            $view = $this->view(PageHelper::getPageResponse($em, $page), Response::HTTP_OK);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Page Not Found"
                ]
            ], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     *
     * @Rest\Patch("/{id}", requirements={"id"="\d+"})
     * @SWG\Patch(path="/v2/admin/page/{id}",
     *   tags={"ADMIN PAGE"},
     *   security=false,
     *   summary="UPDATE PAGE BY ID FOR ADMIN",
     *   description="The method for update page by id for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="id"
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
    public function updateByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository("AppBundle:Page")->find($id);
        if($page instanceof Page){
            //DISCONNECT
            if($request->request->has('status') && $request->request->get('status') == false){
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
                    $view = $this->view($decodeBody, Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"status is required and should be false"
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Page Not Found"
                ]
            ], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     *
     * @Rest\Delete("/{id}", requirements={"id"="\d+"})
     * @SWG\Delete(path="/v2/admin/page/{id}",
     *   tags={"ADMIN PAGE"},
     *   security=false,
     *   summary="REMOVE PAGE BY ID FOR ADMIN",
     *   description="The method for remove page by id for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="id"
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
    public function removeByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository("AppBundle:Page")->find($id);
        if($page instanceof Page){
            if($page->getStatus() == true){
                try{
                    $bot = new MyFbBotApp($page->getAccessToken());
                    $bot->deletePersistentMenu();
                } catch(\Exception $e){}

                $fb =new \Facebook\Facebook([
                    'app_id' => $this->container->getParameter('facebook_id'),
                    'app_secret' => $this->container->getParameter('facebook_secret'),
                    'default_graph_version' => 'v3.3'
                ]);

                try {
                    $response = $fb->delete('/me/subscribed_apps?access_token='.$page->getAccessToken());
                } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                }
            }

            $em->getRepository("AppBundle:Page")->removeByPageId($page->getPageId());

            $view = $this->view([], Response::HTTP_NO_CONTENT);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Page Not Found"
                ]
            ], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }
}
