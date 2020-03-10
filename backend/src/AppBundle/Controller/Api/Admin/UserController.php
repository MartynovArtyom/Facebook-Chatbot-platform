<?php
/**
 * Created by PhpStorm.
 * Date: 15.11.18
 * Time: 13:06
 */

namespace AppBundle\Controller\Api\Admin;

use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\Page;
use AppBundle\Entity\User;
use AppBundle\Helper\MyFbBotApp;
use AppBundle\Helper\UserHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class UserController
 * @package AppBundle\Controller\Api\Admin
 *
 * @Rest\Route("/user")
 */
class UserController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/v2/admin/user/",
     *   tags={"ADMIN USER"},
     *   security=false,
     *   summary="GET USERs FOR ADMIN",
     *   description="The method for getting user for admin",
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
     *   @SWG\Parameter(
     *      name="admin",
     *      in="query",
     *      required=false,
     *      type="boolean",
     *      description="only true"
     *   ),
     *   @SWG\Parameter(
     *      name="productId",
     *      in="query",
     *      required=false,
     *      type="integer",
     *      description="productId"
     *   ),
     *   @SWG\Parameter(
     *      name="createdFrom",
     *      in="query",
     *      required=false,
     *      type="string",
     *      description="createdFrom"
     *   ),
     *   @SWG\Parameter(
     *      name="createdTo",
     *      in="query",
     *      required=false,
     *      type="string",
     *      description="createdTo"
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
     *                      property="status",
     *                      type="boolean"
     *                  ),
     *                  @SWG\Property(
     *                      property="countPages",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="countSubscribers",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="limitSubscribers",
     *                      type="integer"
     *                  ),
     *                  @SWG\Property(
     *                      property="productLabel",
     *                      type="string"
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
        $users = $em->getRepository("AppBundle:User")->getAllForAdmin($request->query->all());

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users,
            ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
            ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
        );
        $view = $this->view([
            'items'=>UserHelper::getUsersResponse($em, $pagination->getItems()),
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
     * @SWG\Get(path="/v2/admin/user/{id}",
     *   tags={"ADMIN USER"},
     *   security=false,
     *   summary="GET USER BY ID FOR ADMIN",
     *   description="The method for getting user by id for admin",
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
     *              property="firstName",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="lastName",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="email",
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
     *              property="role",
     *              type="string",
     *              description="ROLE_USER OR ROLE_ADMIN"
     *          ),
     *          @SWG\Property(
     *              property="created",
     *              type="datetime",
     *          ),
     *          @SWG\Property(
     *              property="lastLogin",
     *              type="datetime",
     *          ),
     *          @SWG\Property(
     *              property="countPages",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="countFlows",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="countSubscribers",
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
     *              property="trialEnd",
     *              type="datetime"
     *          ),
     *          @SWG\Property(
     *              property="pages",
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
     *              )
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
     *              property="orderId",
     *              type="string"
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
     *          @SWG\Property(
     *              property="quentnId",
     *              type="string"
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
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User){

            $view = $this->view(UserHelper::getUserResponseByUser($em, $user), Response::HTTP_OK);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"User Not Found"
                ]
            ], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Put("/{id}", requirements={"id"="\d+"})
     * @SWG\Put(path="/v2/admin/user/{id}",
     *   tags={"ADMIN USER"},
     *   security=false,
     *   summary="EDIT USER BY ID FOR ADMIN",
     *   description="The method for edit user by id for admin",
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
     *              property="limitSubscribers",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="orderId",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="productId",
     *              type="integer",
     *              description="if not product send null"
     *          ),
     *          @SWG\Property(
     *              property="quentnId",
     *              type="string"
     *          ),
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
     *              property="firstName",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="lastName",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="email",
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
     *              property="role",
     *              type="string",
     *              description="ROLE_USER OR ROLE_ADMIN"
     *          ),
     *          @SWG\Property(
     *              property="countPages",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="countFlows",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="countSubscribers",
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
     *              property="trialEnd",
     *              type="datetime"
     *          ),
     *          @SWG\Property(
     *              property="pages",
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
     *              )
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
     *              property="orderId",
     *              type="string"
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
     *          @SWG\Property(
     *              property="quentnId",
     *              type="string"
     *          ),
     *     )
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
     *      description="Not Found",
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
    public function editByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User){
            if(
                $request->request->has('limitSubscribers') && !empty($request->request->has('limitSubscribers'))
                && $request->request->has('orderId') && $request->request->has('productId') && $request->request->has('quentnId')
            ){
                $user->setLimitSubscribers($request->request->get('limitSubscribers'));
                $user->setOrderId($request->request->get('orderId'));
                $user->setQuentnId($request->request->get('quentnId'));
                if(!empty($request->request->get('productId'))){
                    $product = $em->getRepository("AppBundle:DigistoreProduct")->find($request->request->get('productId'));
                    if($product instanceof DigistoreProduct){
                        $user->setProduct($product);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Product Not Found"
                            ]
                        ], Response::HTTP_NOT_FOUND);
                        return $this->handleView($view);
                    }
                }
                else{
                    $user->setProduct(null);
                }
                $em->persist($user);
                $em->flush();

                $view = $this->view(UserHelper::getUserResponseByUser($em, $user), Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"limitSubscribers and orderId and productId and quentnId is required"
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"User Not Found"
                ]
            ], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Patch("/{id}", requirements={"id"="\d+"})
     * @SWG\Patch(path="/v2/admin/user/{id}",
     *   tags={"ADMIN USER"},
     *   security=false,
     *   summary="UPDATE USER BY ID FOR ADMIN",
     *   description="The method for update user by id for admin",
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
     *          ),
     *          @SWG\Property(
     *              property="role",
     *              type="string",
     *              description="ROLE_USER OR ROLE_ADMIN"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
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
     *      description="Not Found",
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
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User){
            if($request->request->has('status')){
                if(is_bool($request->request->get('status'))){
                    $user->setEnabled($request->request->get('status'));
                    $em->persist($user);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"status should be boolean"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            elseif ($request->request->has('role')){
                if(in_array($request->request->get('role'), ['ROLE_USER','ROLE_ADMIN'])){
                    if($request->request->get('role') == 'ROLE_ADMIN'){
                        $user->addRole("ROLE_ADMIN");
                    }
                    else{
                        $user->removeRole("ROLE_ADMIN");
                    }
                    $em->persist($user);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"role should be ROLE_USER OR ROLE_ADMIN"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"one is required field status OR role"
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"User Not Found"
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
     * @SWG\Delete(path="/v2/admin/user/{id}",
     *   tags={"ADMIN USER"},
     *   security=false,
     *   summary="REMOVE USER BY ID FOR ADMIN",
     *   description="The method for remove user by id for admin",
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
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User){
            $pages = $em->getRepository("AppBundle:Page")->findBy(['user'=>$user]);
            if(!empty($pages)){
                $fb =new \Facebook\Facebook([
                    'app_id' => $this->container->getParameter('facebook_id'),
                    'app_secret' => $this->container->getParameter('facebook_secret'),
                    'default_graph_version' => 'v3.3'
                ]);
                foreach ($pages as $page){
                    if($page instanceof Page){
                        try{
                            $bot = new MyFbBotApp($page->getAccessToken());
                            $bot->deletePersistentMenu();
                        }
                        catch(\Exception $e){}

                        if($page->getStatus() == true){
                            try {
                                $response = $fb->delete('/me/subscribed_apps?access_token='.$page->getAccessToken());
                            }
                            catch(\Facebook\Exceptions\FacebookResponseException $e) {}
                            catch(\Facebook\Exceptions\FacebookSDKException $e) {}
                        }

                        $em->getRepository("AppBundle:Page")->removeByPageId($page->getPageId());
                    }
                }
            }
            $em->remove($user);
            $em->flush();

            $view = $this->view([], Response::HTTP_NO_CONTENT);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"User Not Found"
                ]
            ], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Get("/{id}/token", requirements={"id"="\d+"})
     * @SWG\Get(path="/v2/admin/user/{id}/token",
     *   tags={"ADMIN USER"},
     *   security=false,
     *   summary="GET TOKEN FOR USER BY ID FOR ADMIN",
     *   description="The method for getting token for user by id for admin",
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
     *              property="token",
     *              type="string",
     *              description="JWT TOKEN",
     *          )
     *     )
     *  )
     * )
     */
    public function getTokenByIdAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);
        if($user instanceof User){
            $JWTToken = $this->get('lexik_jwt_authentication.jwt_manager')->create($user);
            $view = $this->view([
                'token' => $JWTToken
            ], Response::HTTP_OK);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"User Not Found"
                ]
            ], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }
}
