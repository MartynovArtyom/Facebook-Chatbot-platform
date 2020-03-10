<?php
/**
 * Created by PhpStorm.
 * Date: 23.07.18
 * Time: 15:33
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\CustomFields;
use AppBundle\Entity\Page;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class CustomFieldsController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/custom_fields")
 */
class CustomFieldsController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/custom_fields/",
     *   tags={"CUSTOM FIELDS"},
     *   security=false,
     *   summary="GET CUSTOM FIELDS BY PAGE_ID",
     *   description="The method for getting custom fields by page_id",
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
     *      name="status",
     *      in="query",
     *      required=false,
     *      type="boolean",
     *      default="",
     *      description="sort by status"
     *   ),
     *   @SWG\Parameter(
     *      name="search",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="search"
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
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="page_id",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="integer",
     *                  description="
     *                      1 = Text
     *                      2 = Number
     *                      3 = Date
     *                      4 = DateTime
     *                      5 = Boolean
     *                  "
     *              ),
     *              @SWG\Property(
     *                  property="description",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="boolean"
     *              ),
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
     *   )
     * )
     */
    public function getAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $customFields = $em->getRepository("AppBundle:CustomFields")->getAllByPageID($page->getPageId(),$request->query->all());

            $view = $this->view($customFields, Response::HTTP_OK);
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
     * @SWG\Post(path="/v2/page/{page_id}/custom_fields/",
     *   tags={"CUSTOM FIELDS"},
     *   security=false,
     *   summary="CREATE CUSTOM FIELD BY PAGE_ID",
     *   description="The method for creating custom field by page_id",
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
     *              example="name",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description=" required,
     *                      1 = Text
     *                      2 = Number
     *                      3 = Date
     *                      4 = DateTime
     *                      5 = Boolean
     *                  "
     *          ),
     *          @SWG\Property(
     *              property="description",
     *              type="string",
     *              example="description",
     *              description="not required"
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
     *              property="page_id",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              description="
     *                      1 = Text
     *                      2 = Number
     *                      3 = Date
     *                      4 = DateTime
     *                      5 = Boolean
     *                  "
     *          ),
     *          @SWG\Property(
     *              property="description",
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
            $customField = new CustomFields(
                $page->getPageId(),
                $request->request->get('name'),
                $request->request->get('type'),
                $request->request->get('description'),
                true
            );
            $errors = $this->get('validator')->validate($customField, null, array('customFields'));
            if(count($errors) === 0){
                $checkName = $em->getRepository("AppBundle:CustomFields")->findOneBy(['page_id'=>$page->getPageId(),'name'=>$customField->getName()]);
                if(!$checkName instanceof CustomFields){
                    $em->persist($customField);
                    $em->flush();

                    $view = $this->view($customField, Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>["Name already use"]
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else {
                $error_description = [];
                foreach ($errors as $er) {
                    $error_description[] = $er->getMessage();
                }
                $view = $this->view([
                    'error'=>[
                        'message'=>$error_description
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
     * @param $id
     * @return Response
     *
     * @Rest\Get("/{id}", requirements={"page_id"="\d+","id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/custom_fields/{id}",
     *   tags={"CUSTOM FIELDS"},
     *   security=false,
     *   summary="GET CUSTOM FIELDS BY ID",
     *   description="The method for getting custom fields by id",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="custom field id"
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
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              description="
     *                      1 = Text
     *                      2 = Number
     *                      3 = Date
     *                      4 = DateTime
     *                      5 = Boolean
     *                  "
     *          ),
     *          @SWG\Property(
     *              property="description",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean"
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
     *      description="Not found",
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
    public function getByIdAction(Request $request, $page_id, $id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['id'=>$id, 'page_id'=>$page->getPageId()]);
            if($customField instanceof CustomFields){
                $view = $this->view($customField, Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Custom field Not Found"
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
     * @param $id
     * @return Response
     *
     * @Rest\Patch("/{id}", requirements={"page_id"="\d+","id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/custom_fields/{id}",
     *   tags={"CUSTOM FIELDS"},
     *   security=false,
     *   summary="EDIT CUSTOM FIELD BY ID",
     *   description="The method for getting custom fields by id",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="custom field id"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      description="Use one field at a time",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              example="name",
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="description",
     *              type="string",
     *              example="description",
     *              description="not required"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=false,
     *              description="required, if true check FOR FREE LIMIT 3 CUSTOM FIELD"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
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
     *      description="Not found",
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
    public function updateByIdAction(Request $request, $page_id, $id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['id'=>$id, 'page_id'=>$page->getPageId()]);
            if($customField instanceof CustomFields){
                if($request->request->has('status') || $request->request->has('name') || $request->request->has('description')){
                    if($request->request->has('status')){
                        if(is_bool($request->request->get('status'))){
                            $customField->setStatus($request->request->get('status'));
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"status should be boolean"
                                ]
                            ], Response::HTTP_NOT_FOUND);
                            return $this->handleView($view);
                        }
                    }
                    if ($request->request->has('name')){
                        if(!empty($request->request->get('name'))){
                            $customField->setName($request->request->get('name'));
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"name should not be empty"
                                ]
                            ], Response::HTTP_NOT_FOUND);
                            return $this->handleView($view);
                        }
                    }
                    if ($request->request->has('description')){
                        $customField->setDescription($request->request->get('description'));
                    }

                    $em->persist($customField);
                    $em->flush();
                    $view = $this->view([], Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"One of the fields is required"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Custom field Not Found"
                    ]
                ], Response::HTTP_NOT_FOUND);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Access Denied"
                ]
            ], Response::HTTP_FORBIDDEN);
            return $this->handleView($view);
        }
    }
}