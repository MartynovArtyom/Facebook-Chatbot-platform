<?php
/**
 * Created by PhpStorm.
 * Date: 25.07.18
 * Time: 15:08
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\Autoposting;
use AppBundle\Entity\Page;
use AppBundle\Helper\OtherHelper;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class AutopostingController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/autoposting")
 */
class AutopostingController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/autoposting/",
     *   tags={"AUTOPOSTING"},
     *   security=false,
     *   summary="GET ALL AUTOPOSTINGS BY PAGE_ID",
     *   description="The method for getting all autopostings by page_id",
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
     *                      property="type",
     *                      type="integer",
     *                      description="
     *                          1=RSS
     *                          2=Youtube
     *                          3=Twitter
     *                          4=Facebook
     *                      "
     *                  ),
     *                  @SWG\Property(
     *                      property="account",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="url",
     *                      type="string"
     *                  ),
     *                  @SWG\Property(
     *                      property="typePush",
     *                      type="integer",
     *                      description="
     *                          1=Regular Push
     *                          2=Silent Push
     *                          3=Silent
     *                      "
     *                  ),
     *                  @SWG\Property(
     *                      property="targeting",
     *                      type="array",
     *                      @SWG\Items(type="string"),
     *                      description="if null = Disabled filter, else Enabled filter"
     *                  ),
     *                  @SWG\Property(
     *                      property="status",
     *                      type="boolean"
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
            $autopostings = $em->getRepository("AppBundle:Autoposting")->findBy(['page_id'=>$page->getPageId()]);
            $autoposting = [];
            foreach ($autopostings as $item){
                if($item instanceof Autoposting){
                    $autoposting[] = [
                        'id' => $item->getId(),
                        'title' => $item->getTitle(),
                        'type' => $item->getType(),
                        'account' => $item->getAccount(),
                        'url' => $item->getUrl(),
                        'typePush' => $item->getTypePush(),
                        'targeting' => $item->getTargeting(),
                        'status' => $item->getStatus()
                    ];
                }
            }
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $autoposting,
                ($request->query->getInt('page', 1) > 0) ? $request->query->getInt('page', 1) : 1,
                ($request->query->getInt('limit', 20) > 0) ? $request->query->getInt('limit', 20) : 20
            );
            $view = $this->view([
                'items'=>$pagination->getItems(),
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
     * @param $autoposting_id
     * @return Response
     *
     * @Rest\Get("/{autoposting_id}", requirements={"page_id"="\d+","autoposting_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/autoposting/{autoposting_id}",
     *   tags={"AUTOPOSTING"},
     *   security=false,
     *   summary="GET AUTOPOSTING BY AUTOPOSTING_ID",
     *   description="The method for getting autoposting by autoposting_id",
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
     *      name="autoposting_id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="autoposting_id"
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
     *              property="type",
     *              type="integer",
     *              description="
     *                      1=RSS
     *                      2=Youtube
     *                      3=Twitter
     *                      4=Facebook
     *                  "
     *          ),
     *          @SWG\Property(
     *              property="account",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="url",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="typePush",
     *              type="integer",
     *              description="
     *                      1=Regular Push
     *                      2=Silent Push
     *                      3=Silent
     *                  "
     *          ),
     *          @SWG\Property(
     *              property="targeting",
     *              type="array",
     *              @SWG\Items(type="string"),
     *              description="if null = Disabled filter, else Enabled filter"
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
    public function getByIDAction(Request $request, $page_id, $autoposting_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $autoposting = $em->getRepository("AppBundle:Autoposting")->find($autoposting_id);
            if($autoposting instanceof Autoposting){
                $view = $this->view([
                    'id' => $autoposting->getId(),
                    'title' => $autoposting->getTitle(),
                    'type' => $autoposting->getType(),
                    'account' => $autoposting->getAccount(),
                    'url' => $autoposting->getUrl(),
                    'typePush' => $autoposting->getTypePush(),
                    'targeting' => $autoposting->getTargeting(),
                    'status' => $autoposting->getStatus()
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Not Found"
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
     * @throws \Facebook\Exceptions\FacebookSDKException
     *
     * @Rest\Post("/", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/autoposting/",
     *   tags={"AUTOPOSTING"},
     *   security=false,
     *   summary="CREATE AUTOPOSTING BY PAGE_ID",
     *   description="The method for creating autoposting by page_id",
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
     *              description=" required,
     *                      1=RSS
     *                      2=Youtube
     *                      3=Twitter
     *                      4=Facebook
     *                  "
     *          ),
     *          @SWG\Property(
     *              property="url",
     *              type="string",
     *              example="url",
     *              description="required"
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
     *              property="title",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              description="
     *                      1=RSS
     *                      2=Youtube
     *                      3=Twitter
     *                      4=Facebook
     *                  "
     *          ),
     *          @SWG\Property(
     *              property="account",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="url",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="typePush",
     *              type="integer",
     *              description="
     *                      1=Regular Push
     *                      2=Silent Push
     *                      3=Silent
     *                  "
     *          ),
     *          @SWG\Property(
     *              property="targeting",
     *              type="array",
     *              @SWG\Items(type="string"),
     *              description="if null = Disabled filter, else Enabled filter"
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
            if(
                $request->request->has('type') && !empty($request->request->get('type'))
                && $request->request->has('url') && !empty($request->request->get('url'))
            ){
                switch ($request->request->get('type')){
                    case 1:
                        $checkIsset = $em->getRepository("AppBundle:Autoposting")->findOneBy(['page_id'=>$page_id, 'url'=>$request->request->get('url')]);
                        if(!$checkIsset instanceof Autoposting){
                            $feeds = OtherHelper::getFeed($request->request->get('url'));
                            if($feeds){
                                $autoposting = new Autoposting($page_id, 'RSS Feed', $request->request->get('type'), $request->request->get('url'), $request->request->get('url'));
                            }
                            else{
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>"Canal is invalid"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Canal already use"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        break;
                    case 2:
                        $urlArray = preg_split("/\//i",$request->request->get('url'), NULL,PREG_SPLIT_NO_EMPTY);
                        $channel_id = $urlArray[count($urlArray)-1];
                        $urlFeed = "https://www.youtube.com/feeds/videos.xml?channel_id=".$channel_id;
                        $checkIsset = $em->getRepository("AppBundle:Autoposting")->findOneBy(['page_id'=>$page_id, 'url'=>$urlFeed]);
                        if(!$checkIsset instanceof Autoposting){
                            $feeds = OtherHelper::getFeed($urlFeed);
                            if($feeds){
                                $autoposting = new Autoposting($page_id, 'Youtube Kanal', $request->request->get('type'), $urlFeed, $urlFeed);
                            }
                            else{
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>"Canal is invalid"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Canal already use"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        break;
                    case 3:
                        $urlArray = preg_split("/\//i",$request->request->get('url'), NULL,PREG_SPLIT_NO_EMPTY);
                        $channel_id = $urlArray[count($urlArray)-1];
                        $urlFeed = "https://twitrss.me/twitter_user_to_rss/?user=".$channel_id;
                        $checkIsset = $em->getRepository("AppBundle:Autoposting")->findOneBy(['page_id'=>$page_id, 'url'=>$urlFeed]);
                        if(!$checkIsset instanceof Autoposting){
                            $feeds = OtherHelper::getFeed($urlFeed);
                            if($feeds){
                                $autoposting = new Autoposting($page_id, 'Twitter Account', $request->request->get('type'), $channel_id, $urlFeed);
                            }
                            else{
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>"Canal is invalid"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Canal already use"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        break;
                    case 4:
                        $urlArray = preg_split("/\//i",$request->request->get('url'), NULL,PREG_SPLIT_NO_EMPTY);
                        $channel_id = $urlArray[count($urlArray)-1];
                        $urlFeed = $channel_id."/feed";
                        $checkIsset = $em->getRepository("AppBundle:Autoposting")->findOneBy(['page_id'=>$page_id, 'account'=>$channel_id]);
                        if(!$checkIsset instanceof Autoposting){
                            $fb = new \Facebook\Facebook([
                                'app_id' => $this->container->getParameter('facebook_id'),
                                'app_secret' => $this->container->getParameter('facebook_secret'),
                                'default_graph_version' => 'v3.3'
                            ]);
                            try {
                                $response = $fb->get($urlFeed.'?access_token='.$page->getAccessToken());
                            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>"Canal is invalid"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>"Canal is invalid"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                            if($response->getHttpStatusCode() == 200){
                                $autoposting = new Autoposting($page_id, 'Facebook Account', $request->request->get('type'), $channel_id, $urlFeed);
                            }
                            else{
                                $view = $this->view([
                                    'error'=>[
                                        'message'=>"Canal is invalid"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Canal already use"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        break;
                }

                if(isset($autoposting) && $autoposting instanceof Autoposting){
                    $errors = $this->get('validator')->validate($autoposting, null, array('autoposting'));
                    if(count($errors) === 0){
                        $em->persist($autoposting);
                        $em->flush();

                        $view = $this->view([
                            'id' => $autoposting->getId(),
                            'title' => $autoposting->getTitle(),
                            'type' => $autoposting->getType(),
                            'account' => $autoposting->getAccount(),
                            'url' => $autoposting->getUrl(),
                            'typePush' => $autoposting->getTypePush(),
                            'targeting' => $autoposting->getTargeting(),
                            'status' => $autoposting->getStatus()
                        ], Response::HTTP_OK);
                        return $this->handleView($view);
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
                            'message'=>"Canal is invalid"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"type and url is required"
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
     * @param $autoposting_id
     * @return Response
     *
     * @Rest\Put("/{autoposting_id}", requirements={"page_id"="\d+","autoposting_id"="\d+"})
     * @SWG\Put(path="/v2/page/{page_id}/autoposting/{autoposting_id}",
     *   tags={"AUTOPOSTING"},
     *   security=false,
     *   summary="UPDATE AUTOPOSTING BY AUTOPOSTING_ID",
     *   description="The method for updating autoposting by autoposting_id",
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
     *      name="autoposting_id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="autoposting_id"
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
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=true,
     *              description="required"
     *          ),
     *          @SWG\Property(
     *              property="typePush",
     *              type="integer",
     *              example=1,
     *              description=" required,
     *                      1=Regular Push
     *                      2=Silent Push
     *                      3=Silent
     *                  "
     *          ),
     *          @SWG\Property(
     *              property="targeting",
     *              type="array",
     *              example=null,
     *              @SWG\Items(type="string"),
     *              description="required"
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
    public function editByIDAction(Request $request, $page_id, $autoposting_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $autoposting = $em->getRepository("AppBundle:Autoposting")->find($autoposting_id);
            if($autoposting instanceof Autoposting){
                $autoposting->update(
                    $request->request->get('title'),
                    $request->request->get('status'),
                    $request->request->get('typePush'),
                    $request->request->get('targeting')
                );
                $errors = $this->get('validator')->validate($autoposting, null, array('autoposting'));
                if(count($errors) === 0){
                    $em->persist($autoposting);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_OK);
                    return $this->handleView($view);
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
                        'message'=>"Not Found"
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
     * @param $autoposting_id
     * @return Response
     *
     * @Rest\Delete("/{autoposting_id}", requirements={"page_id"="\d+","autoposting_id"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/autoposting/{autoposting_id}",
     *   tags={"AUTOPOSTING"},
     *   security=false,
     *   summary="DELETE AUTOPOSTING BY AUTOPOSTING_ID",
     *   description="The method for deleting autoposting by autoposting_id",
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
     *      name="autoposting_id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="autoposting_id"
     *   ),
     *   @SWG\Response(
     *     response=200,
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
    public function deleteByIDAction(Request $request, $page_id, $autoposting_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $autoposting = $em->getRepository("AppBundle:Autoposting")->find($autoposting_id);
            if($autoposting instanceof Autoposting){
                $em->remove($autoposting);
                $em->flush();

                $view = $this->view([], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Not Found"
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
