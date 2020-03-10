<?php
/**
 * Created by PhpStorm.
 * Date: 12.10.18
 * Time: 14:18
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\Flows;
use AppBundle\Entity\Keywords;
use AppBundle\Entity\Page;
use AppBundle\Helper\Flow\FlowHelper;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class KeywordsController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/automation/keywords")
 */
class KeywordsController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/automation/keywords/",
     *   tags={"KEYWORDS"},
     *   security=false,
     *   summary="GET KEYWORDS BY PAGE_ID",
     *   description="The method for getting keywords by page_id",
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
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="command",
     *                  type="string",
     *                  example="command, command1"
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="integer",
     *                  example=1,
     *                  description="1 = is 2 = contains 3 = begins with"
     *              ),
     *              @SWG\Property(
     *                  property="flow",
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
     *              ),
     *              @SWG\Property(
     *                  property="actions",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object"
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @SWG\Property(
     *                  property="main",
     *                  type="boolean",
     *                  example=true,
     *                  description="if true can not change type and status and can not remove"
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
     *   )
     * )
     */
    public function getAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $keywordsResult = $em->getRepository("AppBundle:Keywords")->findBy(['page_id'=>$page->getPageId()],['main'=>'DESC','id'=>'ASC']);
            $keywords = [];
            if(!empty($keywordsResult)){
                foreach ($keywordsResult as $keyword){
                    if($keyword instanceof Keywords){
                        $flowResponse = null;
                        if($keyword->getFlow() instanceof Flows){
                            $flowResponse = FlowHelper::getFlowResponse($em, $keyword->getFlow());
                        }

                        $keywords[] = [
                            'id' => $keyword->getId(),
                            'command' => $keyword->getCommand(),
                            'type' => $keyword->getType(),
                            'flow' => $flowResponse,
                            'actions' => $keyword->getActions(),
                            'status' => $keyword->getStatus(),
                            'main' => $keyword->getMain()
                        ];
                    }
                }
            }
            $view = $this->view($keywords, Response::HTTP_OK);
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
     * @SWG\Post(path="/v2/page/{page_id}/automation/keywords/",
     *   tags={"KEYWORDS"},
     *   security=false,
     *   summary="CREATE KEYWORDS BY PAGE_ID",
     *   description="The method for create keywords by page_id",
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
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = is 2 = contains 3 = begins with"
     *          ),
     *          @SWG\Property(
     *              property="command",
     *              type="string",
     *              example="command, command1"
     *          ),
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
     *              property="command",
     *              type="string",
     *              example="command, command1"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="1 = is 2 = contains 3 = begins with"
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
     *              )
     *          ),
     *          @SWG\Property(
     *              property="actions",
     *              type="array",
     *              @SWG\Items(
     *                  type="object"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true
     *          ),
     *          @SWG\Property(
     *              property="main",
     *              type="boolean",
     *              example=true,
     *              description="if true can not change type and status and can not remove"
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
     *   )
     * )
     */
    public function createAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('type') && $request->request->getInt('type',0) > 0 && in_array($request->request->getInt('type',0),[1,2,3])
                && $request->request->has('command') && !empty($request->request->get('command'))
            ){
                $keyword = new Keywords($page->getPageId(), $request->request->get('command'), $request->request->getInt('type',0));
                $em->persist($keyword);
                $em->flush();

                $view = $this->view([
                    'id' => $keyword->getId(),
                    'command' => $keyword->getCommand(),
                    'type' => $keyword->getType(),
                    'flow' =>
                        ($keyword->getFlow() instanceof Flows) ?
                            [
                                'id' => $keyword->getFlow()->getId(),
                                'name' => $keyword->getFlow()->getName()
                            ]
                            : null,
                    'actions' => $keyword->getActions(),
                    'status' => $keyword->getStatus(),
                    'main' => $keyword->getMain()
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>'type and command is required and should be not empty'
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
     * @param $keywordID
     * @return Response
     *
     * @Rest\Get("/{keywordID}", requirements={"page_id"="\d+","keywordID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/automation/keywords/{keywordID}",
     *   tags={"KEYWORDS"},
     *   security=false,
     *   summary="GET KEYWORDS BY ID BY PAGE_ID",
     *   description="The method for get keywords by id by page_id",
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
     *      name="keywordID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="keywordID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *              example=1,
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
    public function getByIdAction(Request $request, $page_id, $keywordID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $keyword = $em->getRepository("AppBundle:Keywords")->findOneBy(['page_id'=>$page->getPageId(),'id'=>$keywordID]);
            if($keyword instanceof Keywords){
                if($keyword->getFlow() instanceof Flows){
                    $view = $this->view([
                        'id' => $keyword->getId(),
                        'flow' => FlowHelper::getFlowDataResponse($em, $keyword->getFlow())
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
                        'message'=>'Keyword Not Found'
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
     * @param $keywordID
     * @return Response
     *
     * @Rest\Patch("/{keywordID}", requirements={"page_id"="\d+","keywordID"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/automation/keywords/{keywordID}",
     *   tags={"KEYWORDS"},
     *   security=false,
     *   summary="UPDATE KEYWORDS BY ID BY PAGE_ID",
     *   description="The method for update keywords by id by page_id",
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
     *      name="keywordID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="keywordID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="command",
     *              type="string",
     *              example="command, command1",
     *              description="one of more required"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="one of more required. 1 = is 2 = contains 3 = begins with"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true,
     *              description="one of more required"
     *          ),
     *          @SWG\Property(
     *              property="actions",
     *              type="array",
     *              description="one of more required",
     *              @SWG\Items(
     *                  type="object"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="flowID",
     *              type="integer",
     *              example=1,
     *              description="one of more required."
     *          ),
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
     *              example=1,
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
    public function updateByIdAction(Request $request, $page_id, $keywordID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $keyword = $em->getRepository("AppBundle:Keywords")->findOneBy(['page_id'=>$page->getPageId(),'id'=>$keywordID]);
            if($keyword instanceof Keywords){
                //UPDATE COMMAND
                if($request->request->has('command') && !empty($request->request->get('command'))){
                    $keyword->setCommand($request->request->get('command'));
                    $em->persist($keyword);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                //UPDATE TYPE
                elseif ($request->request->has('type') && $request->request->getInt('type',0) > 0 && in_array($request->request->getInt('type',0),[1,2,3])){
                    if($keyword->getMain() != true){
                        $keyword->setType($request->request->getInt('type',0));
                        $em->persist($keyword);
                        $em->flush();

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>'You can not update this keyword'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                //UPDATE STATUS
                elseif ($request->request->has('status') && is_bool($request->request->get('status'))){
                    if($keyword->getMain() != true) {
                        $keyword->setStatus($request->request->get('status'));
                        $em->persist($keyword);
                        $em->flush();

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>'You can not update this keyword'
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                //UPDATE ACTIONS
                elseif ($request->request->has('actions')){
                    $keyword->setActions($request->request->get('actions'));
                    $em->persist($keyword);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                //UPDATE FLOW
                elseif ($request->request->has('flowID') && !empty($request->request->get('flowID'))){
                    $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$page->getPageId(),'id'=>$request->request->get('flowID')]);
                    if($flow instanceof Flows){
                        $flow->setType(Flows::FLOW_TYPE_KEYWORDS);
                        $em->persist($flow);
                        $em->flush();
                        $keyword->setFlow($flow);
                        $em->persist($keyword);
                        $em->flush();

                        $view = $this->view([
                            'id' => $keyword->getId(),
                            'flow' => FlowHelper::getFlowDataResponse($em, $keyword->getFlow())
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
                            'message'=>'invalid value'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>'Keyword Not Found'
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
     * @param $keywordID
     * @return Response
     *
     * @Rest\Delete("/{keywordID}", requirements={"page_id"="\d+","keywordID"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/automation/keywords/{keywordID}",
     *   tags={"KEYWORDS"},
     *   security=false,
     *   summary="DELETE KEYWORDS BY ID BY PAGE_ID",
     *   description="The method for delete keywords by id by page_id",
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
     *      name="keywordID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="keywordID"
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
    public function deleteByIdAction(Request $request, $page_id, $keywordID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $keyword = $em->getRepository("AppBundle:Keywords")->findOneBy(['page_id'=>$page->getPageId(),'id'=>$keywordID]);
            if($keyword instanceof Keywords){
                if($keyword->getFlow() instanceof Flows){
                    $em->remove($keyword->getFlow());
                }
                $em->remove($keyword);
                $em->flush();

                $view = $this->view([], Response::HTTP_NO_CONTENT);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>'Keyword Not Found'
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
