<?php
/**
 * Created by PhpStorm.
 * Date: 19.07.18
 * Time: 12:45
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\GreetingText;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Page;
use AppBundle\Entity\ZapierApiKey;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use pimax\FbBotApp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class SettingsController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/settings")
 */
class SettingsController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/general", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/settings/general",
     *   tags={"SETTINGS"},
     *   security=false,
     *   summary="GET GENERAL SETTINGS BY PAGE_ID",
     *   description="The method for getting general settings by page_id",
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
     *              property="greetingText",
     *              type="string",
     *              description="Greeting Text",
     *          ),
     *          @SWG\Property(
     *              property="mainData",
     *              type="boolean",
     *              description="mainData",
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
     *   )
     * )
     */
    public function getGeneralAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $greetingText = $em->getRepository("AppBundle:GreetingText")->findOneBy(['page_id'=>$page->getPageId()]);
            $text = '';
            if($greetingText instanceof GreetingText){
                $text = $greetingText->getText();
            }

            $view = $this->view(['greetingText'=>$text, 'mainData'=>$page->getMainData()], Response::HTTP_OK);
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
     * @Rest\Patch("/general", requirements={"page_id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/settings/general",
     *   tags={"SETTINGS"},
     *   security=false,
     *   summary="UPDATE GENERAL SETTINGS BY PAGE_ID",
     *   description="The method for updating general settings by page_id",
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
     *              property="greetingText",
     *              type="string",
     *              example="Greeting text",
     *              description="one is required, max length 160 symbols"
     *          ),
     *          @SWG\Property(
     *              property="mainData",
     *              type="boolean",
     *              example=false,
     *              description="one is required, true or false"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
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
    public function updateGeneralAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            //----GREETING TEXT----
            if($request->request->has('greetingText')){
                $greetingText = $em->getRepository("AppBundle:GreetingText")->findOneBy(['page_id'=>$page->getPageId()]);
                if(!$greetingText instanceof GreetingText){
                    $greetingText = new GreetingText($page->getPageId(), $request->request->get('greetingText'));
                }
                else{
                    $greetingText->setText($request->request->get('greetingText'));
                }
                $errors = $this->get('validator')->validate($greetingText, null, array('greetingText'));
                if(count($errors) === 0){
                    $bot = new FbBotApp($page->getAccessToken());
                    $result = $bot->setGreetingText([
                        [
                            "locale" => "default",
                            "text" => $greetingText->getText()
                        ]
                    ]);
                    if(isset($result['result']) && $result['result'] == 'success'){
                        $em->persist($greetingText);
                        $em->flush();

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                        return $this->handleView($view);

                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Oops... Something went wrong!"
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
            //----MAIN DATA----
            elseif($request->request->has('mainData')){
                if(is_bool($request->request->get('mainData'))){
                    $page->setMainData($request->request->get('mainData'));
                    $em->persist($page);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"mainData is required and should be boolean"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"greetingText or mainData is required"
                    ]
                ], Response::HTTP_BAD_REQUEST);
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

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/notification", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/settings/notification",
     *   tags={"SETTINGS"},
     *   security=false,
     *   summary="GET NOTIFICATION SETTINGS BY PAGE_ID",
     *   description="The method for getting notification settings by page_id",
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
     *              property="email",
     *              type="string",
     *              description="email",
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              description="type",
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              description="status",
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
     *   )
     * )
     */
    public function getNotificationAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $notification = $em->getRepository('AppBundle:Notification')->findOneBy(['page_id'=>$page->getPageId(), 'user'=>$this->getUser()]);
            if(!$notification instanceof Notification){
                $notification = new Notification($page->getPageId(), $this->getUser(), $this->getUser()->getEmail());
                $em->persist($notification);
                $em->flush();
            }

            $view = $this->view([
                'email' => $notification->getEmail(),
                'type' => $notification->getType(),
                'status' => $notification->getStatus()
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
     * @Rest\Patch("/notification", requirements={"page_id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/settings/notification",
     *   tags={"SETTINGS"},
     *   security=false,
     *   summary="UPDATE NOTIFICATION SETTINGS BY PAGE_ID",
     *   description="The method for updating notification settings by page_id",
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
     *              property="email",
     *              type="string",
     *              example="email",
     *              description="one is required, max length 160 symbols"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="integer",
     *              example=1,
     *              description="one is required, 1 = Daily, 2 = Weekly, 3 = Monthly"
     *          ),
     *          @SWG\Property(
     *              property="status",
     *              type="boolean",
     *              example=false,
     *              description="one is required, true or false"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
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
    public function updateNotificationAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $notification = $em->getRepository("AppBundle:Notification")->findOneBy(['page_id'=>$page->getPageId(), 'user'=>$this->getUser()]);
            if($notification instanceof Notification){
                //----EMAIL---
                if($request->request->has('email')){
                    $notification->setEmail($request->request->get('email'));
                }
                //----TYPE----
                elseif ($request->request->has('type')){
                    $notification->setType($request->request->get('type'));
                }
                //------STATUS----
                elseif ($request->request->has('status')){
                    $notification->setStatus($request->request->get('status'));
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"email or type or status is required"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }

                $errors = $this->get('validator')->validate($notification, null, array('notification'));
                if(count($errors) === 0){
                    $em->persist($notification);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
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
                        'message'=>"Notification Not Found"
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

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Post("/setStartButton", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/settings/setStartButton",
     *   tags={"SETTINGS"},
     *   security=false,
     *   summary="SET GET START BUTTON BY PAGE_ID",
     *   description="The method for set get start button by page_id",
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
     *     response=204,
     *     description="Success."
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
    public function setGetStartButtonAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $bot = new FbBotApp($page->getAccessToken());
            $result = $bot->setGetStartedButton('WELCOME_MESSAGE');
            if(isset($result['result']) && $result['result'] == 'success'){
                $view = $this->view([], Response::HTTP_NO_CONTENT);
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
     * @Rest\Get("/zapier", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/settings/zapier",
     *   tags={"SETTINGS"},
     *   security=false,
     *   summary="GET ZAPIER SETTINGS BY PAGE_ID",
     *   description="The method for getting zapier settings by page_id",
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
     *              property="api_key",
     *              type="string",
     *              description="if null show button generate API KEY",
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
    public function zapierGetAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($page->getUser()->getProduct() instanceof DigistoreProduct && $page->getUser()->getProduct()->getZapier() == true){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['page_id'=>$page->getPageId()]);
                $api_key = null;
                if($zapierKey instanceof ZapierApiKey){
                    $api_key = $zapierKey->getToken();
                }
                $view = $this->view(['api_key' => $api_key], Response::HTTP_OK);
                return $this->handleView($view);
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
     * @return Response
     *
     * @Rest\Post("/zapier", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/settings/zapier",
     *   tags={"SETTINGS"},
     *   security=false,
     *   summary="CREATE ZAPIER SETTINGS BY PAGE_ID",
     *   description="The method for creating zapier settings by page_id",
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
     *              property="api_key",
     *              type="string"
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
    public function createZapierAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($page->getUser()->getProduct() instanceof DigistoreProduct && $page->getUser()->getProduct()->getZapier() == true){
                $api_key = bin2hex(openssl_random_pseudo_bytes(24));
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['page_id'=>$page->getPageId()]);
                if($zapierKey instanceof ZapierApiKey){
                    $zapierKey->setToken($api_key);
                }
                else{
                    $zapierKey = new ZapierApiKey($page->getPageId(), $api_key);
                }
                $em->persist($zapierKey);
                $em->flush();

                $view = $this->view(['api_key' => $api_key], Response::HTTP_OK);
                return $this->handleView($view);
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
     * @return Response
     *
     * @Rest\Delete("/zapier", requirements={"page_id"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/settings/zapier",
     *   tags={"SETTINGS"},
     *   security=false,
     *   summary="DELETE ZAPIER SETTINGS BY PAGE_ID",
     *   description="The method for deleting zapier settings by page_id",
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
     *   )
     * )
     */
    public function deleteZapierAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['page_id'=>$page->getPageId()]);
            if($zapierKey instanceof ZapierApiKey){
                $em->remove($zapierKey);
                $em->flush();
            }

            $view = $this->view([], Response::HTTP_NO_CONTENT);
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


}
