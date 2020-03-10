<?php


namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\Page;
use AppBundle\Entity\PageShare;
use AppBundle\Pages\SharePage;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SharePageController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/sharePage")
 */
class SharePageController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/v2/sharePage/",
     *   tags={"SHARE PAGE"},
     *   security=false,
     *   summary="CHECK SHARE PAGE TOKEN",
     *   description="The method for check share page token",
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
     *      name="token",
     *      in="query",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="token"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="pageID",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="pageName",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="widgets"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="sequences"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="keywords"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="welcomeMessage"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="defaultReply"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="mainMenu"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="flows"
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
    public function checkTokenAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if($request->query->has('token') && !empty($request->query->get('token'))){
            $token = $request->query->get('token');
            $pageShare = $em->getRepository("AppBundle:PageShare")->findOneBy(['token'=>$token]);
            if($pageShare instanceof PageShare){
                if($pageShare->getStatus() == true){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$pageShare->getPageId()]);
                    if($page instanceof Page){
                        $view = $this->view([
                            'id' => $pageShare->getId(),
                            'pageID' => $pageShare->getPageId(),
                            'pageName' => $page->getTitle(),
                            'widgets' => $pageShare->getWidgets(),
                            'sequences' => $pageShare->getSequences(),
                            'keywords' => $pageShare->getKeywords(),
                            'welcomeMessage' => $pageShare->getWelcomeMessage(),
                            'defaultReply' => $pageShare->getDefaultReply(),
                            'mainMenu' => $pageShare->getMainMenu(),
                            'flows' => $pageShare->getFlows()
                        ], Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Link Teilen Deaktiviert"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
        }

        $view = $this->view([
            'error'=>[
                'message'=>"Ungültiger Link zum Teilen"
            ]
        ], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $shareID
     * @return Response
     *
     * @Rest\Post("/{shareID}", requirements={"shareID"="\d+"})
     * @SWG\Post(path="/v2/sharePage/{shareID}",
     *   tags={"SHARE PAGE"},
     *   security=false,
     *   summary="SHARE PAGE",
     *   description="The method for share page",
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
     *      name="shareID",
     *      in="path",
     *      required=true,
     *      type="integer",
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
     *              property="pageID",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              type="object",
     *              property="options",
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="widgets"
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="sequences"
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="keywords"
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="welcomeMessage"
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="defaultReply"
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="mainMenu"
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="flows"
     *              ),
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="page_id",
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
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="string",
     *              )
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=500,
     *      description="HTTP_INTERNAL_SERVER_ERROR",
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
     *   )
     * )
     */
    public function shareAction(Request $request, $shareID){
        $em = $this->getDoctrine()->getManager();
        $pageShare = $em->getRepository("AppBundle:PageShare")->find($shareID);
        if($pageShare instanceof PageShare) {
            if ($pageShare->getStatus() == true) {
                $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$pageShare->getPageId()]);
                if($page instanceof Page){
                    if($request->request->has('pageID') && !empty($request->request->get('pageID')) && $request->request->has('options') && !empty($request->request->get('options'))){
                        $sharePage = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$request->request->get('pageID')]);
                        if($sharePage instanceof Page){
                            if($page->getPageId() != $sharePage->getPageId()){
                                if($sharePage->getUser()->getProduct() instanceof DigistoreProduct){
                                    $countWidgetAll = $em->getRepository("AppBundle:Widget")->countAllByUserId($sharePage->getUser()->getId());
                                    $countSequenceAll = $em->getRepository("AppBundle:Sequences")->countAllByUserId($sharePage->getUser()->getId());
                                    $countWidget = $em->getRepository("AppBundle:Widget")->count(['page_id'=>$page->getPageId()]);
                                    $countSequence = $em->getRepository("AppBundle:Sequences")->count(['page_id'=>$page->getPageId()]);
                                    if(
                                        (
                                            is_null($sharePage->getUser()->getProduct()->getLimitSequences())
                                            || $sharePage->getUser()->getProduct()->getLimitSequences() >= ($countSequenceAll + $countSequence)
                                        )
                                        && (
                                            is_null($sharePage->getUser()->getProduct()->getLimitCompany())
                                            || $sharePage->getUser()->getProduct()->getLimitCompany() >= ($countWidgetAll + $countWidget)
                                        )
                                    ){
                                        if($sharePage->getUser()->getProduct()->getComments() == false){
                                            $checkComments = $em->getRepository("AppBundle:Widget")->count(['page_id'=>$page->getPageId(), 'type'=>11]);
                                            if($checkComments>0){
                                                $view = $this->view([
                                                    'error'=>[
                                                        'message'=>"Diese Funktion ist für deinen Plan nicht freigeschaltet",
                                                        'type'=>'version'
                                                    ]
                                                ], Response::HTTP_BAD_REQUEST);
                                                return $this->handleView($view);
                                            }
                                        }

                                        try{
                                            $handlerShare = new SharePage($em, $page, $sharePage);
                                            $handlerShare->share($request->request->get('options'));

                                            $view = $this->view([
                                                'page_id' => $sharePage->getPageId()
                                            ], Response::HTTP_OK);
                                            return $this->handleView($view);
                                        }
                                        catch (\Exception $e){
                                            $view = $this->view([
                                                'error'=>[
                                                    'message'=> $e->getMessage(),
                                                    'type'=>'internal_server'
                                                ]
                                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                                        'message'=>"Sie können nicht dieselbe Seite teilen",
                                        'type'=>'bad_request'
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                            }
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Page Not Found",
                                    'type'=>'not_found'
                                ]
                            ], Response::HTTP_NOT_FOUND);
                            return $this->handleView($view);
                        }
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Ungültiger Link zum Teilen",
                            'type'=>'invalid_link'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Link Teilen Deaktiviert",
                        'type'=>'invalid_link'
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }

        $view = $this->view([
            'error'=>[
                'message'=>"Ungültiger Link zum Teilen",
                'type'=>'invalid_link'
            ]
        ], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);
    }
}
