<?php


namespace AppBundle\Controller\Api\Site;


use AppBundle\Campaigns\SequencesShare;
use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\SequenceShare;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ShareSequenceController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/shareSequence")
 */
class ShareSequenceController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/v2/shareSequence/",
     *   tags={"SHARE SEQUENCE"},
     *   security=false,
     *   summary="CHECK SHARE SEQUENCE TOKEN",
     *   description="The method for check share sequence token",
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
     *              property="sequenceTitle",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="shareID",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="pageID",
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
            $sequenceShare = $em->getRepository("AppBundle:SequenceShare")->findOneBy(['token'=>$token]);
            if($sequenceShare instanceof SequenceShare){
                if($sequenceShare->getStatus() == true){
                    $view = $this->view([
                        'sequenceTitle' => $sequenceShare->getSequence()->getTitle(),
                        'shareID' => $sequenceShare->getId(),
                        'pageID' => $sequenceShare->getSequence()->getPageId()
                    ], Response::HTTP_OK);
                    return $this->handleView($view);
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
     * @SWG\Post(path="/v2/shareSequence/{shareID}",
     *   tags={"SHARE SEQUENCE"},
     *   security=false,
     *   summary="SHARE SEQUENCE",
     *   description="The method for share sequence",
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
     *              property="page_id",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="title",
     *              type="string"
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
        $sequenceShare = $em->getRepository("AppBundle:SequenceShare")->find($shareID);
        if($sequenceShare instanceof SequenceShare){
            if($sequenceShare->getStatus() == true){
                if($request->request->has('pageID') && !empty($request->request->get('pageID'))){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$request->request->get('pageID')]);
                    if($page instanceof Page){
                        if($page->getPageId() != $sequenceShare->getSequence()->getPageId()){
                            if($page->getUser()->getProduct() instanceof DigistoreProduct){
                                $countSequence = $em->getRepository("AppBundle:Sequences")->countAllByUserId($page->getUser()->getId());
                                if(is_null($page->getUser()->getProduct()->getLimitSequences()) || $page->getUser()->getProduct()->getLimitSequences() > $countSequence){
                                    try{
                                        $sequencesShare = new SequencesShare($em, $page, $sequenceShare->getSequence());
                                        $newSequence = $sequencesShare->share();
                                        if($newSequence instanceof Sequences){
                                            $view = $this->view([
                                                'id' => $newSequence->getId(),
                                                'page_id' => $newSequence->getPageId(),
                                                'type' => 'sequence',
                                                'title' => $newSequence->getTitle()
                                            ], Response::HTTP_OK);
                                            return $this->handleView($view);
                                        }
                                        else{
                                            $view = $this->view([
                                                'error'=>[
                                                    'message'=>"Error!",
                                                    'type'=>'internal_server'
                                                ]
                                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
                                            return $this->handleView($view);
                                        }
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
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"page_id is required",
                            'type'=>'bad_request'
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
