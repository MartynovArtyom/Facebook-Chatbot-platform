<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 * Time: 17:09
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\CustomFields;
use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\Tag;
use AppBundle\Helper\PageHelper;
use AppBundle\Helper\Subscriber\SubscriberActionHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class SubscribersActionController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/subscriber/action")
 */
class SubscribersActionController extends FOSRestController
{

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/subscriber/action/",
     *   tags={"SUBSCRIBER ACTION"},
     *   security=false,
     *   summary="GET NEED DATAs FOR ACTION TO SUBSCRIBERS",
     *   description="The method for getting need datas for action to subscribers",
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
     *              property="tags",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="tagID",
     *                      type="integer",
     *                      example=0
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="name"
     *                  )
     *              )
     *          ),
     *          @SWG\Property(
     *              property="sequences",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="sequenceID",
     *                      type="integer",
     *                      example=0
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="name"
     *                  )
     *              )
     *          ),
     *          @SWG\Property(
     *              property="customFields",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="customFieldID",
     *                      type="integer",
     *                      example=0
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="name"
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      example=1,
     *                      description="1 = Text, 2 = Number, 3 = Date, 4 = DateTime, 5 = Boolean"
     *                  )
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
    public function getAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            //-------TAGS----------------
            $allTags = $em->getRepository("AppBundle:Tag")->findBy(['page_id'=>$page->getPageId()]);
            $tags = [];
            if(!empty($allTags)){
                foreach ($allTags as $tag){
                    if($tag instanceof Tag){
                        $tags[] = [
                            'tagID' => $tag->getId(),
                            'name' => $tag->getName()
                        ];
                    }
                }
            }
            //----------SEQUENCE----------------------
            $allSequences = $em->getRepository("AppBundle:Sequences")->findBy(['page_id'=>$page->getPageId()]);
            $sequences = [];
            if(!empty($allSequences)){
                foreach ($allSequences as $sequence){
                    if($sequence instanceof Sequences){
                        $sequences[] = [
                            'sequenceID' => $sequence->getId(),
                            'name' => $sequence->getTitle()
                        ];
                    }
                }
            }

            //-----------CUSTOM FIELD------------------
            $allCustomFields = $em->getRepository("AppBundle:CustomFields")->findBy(['page_id'=>$page->getPageId(),'status'=>true]);
            $customFields = [];
            if(!empty($allCustomFields)){
                foreach ($allCustomFields as $customField){
                    if($customField instanceof CustomFields){
                        $customFields[] = [
                            'customFieldID' => $customField->getId(),
                            'name' => $customField->getName(),
                            'type' => $customField->getType()
                        ];
                    }
                }
            }

            $view = $this->view([
                'tags' => $tags,
                'sequences' => $sequences,
                'customFields' => $customFields
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/addTag", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/subscriber/action/addTag",
     *   tags={"SUBSCRIBER ACTION"},
     *   security=false,
     *   summary="ADD TAG TO SUBSCRIBERS",
     *   description="The method for add tag to subscribers ",
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
     *              property="tagID",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="subscribers",
     *              type="array",
     *              @SWG\Items(
     *                  type="integer"
     *              )
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="counter",
     *              type="integer",
     *              example="0"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAR REQUEST",
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
    public function addTagAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('tagID') && !empty($request->request->get('tagID'))){
                $tag = $em->getRepository("AppBundle:Tag")->findOneBy(['id'=>$request->request->get('tagID'), 'page_id'=>$page->getPageId()]);
                if($tag instanceof Tag){
                    if($request->request->has('subscribers') && !empty($request->request->get('subscribers'))){
                        $counter = SubscriberActionHelper::addTag($em, $page, $tag->getId(), $request->request->get('subscribers'));

                        $view = $this->view(['counter'=>$counter], Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Subscribers is required and should be empty"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Tag Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"tagID is required"
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/removeTag", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/subscriber/action/removeTag",
     *   tags={"SUBSCRIBER ACTION"},
     *   security=false,
     *   summary="REMOVE TAG TO SUBSCRIBERS",
     *   description="The method for remove tag to subscribers",
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
     *              property="tagID",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="subscribers",
     *              type="array",
     *              @SWG\Items(
     *                  type="integer"
     *              )
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="counter",
     *              type="integer",
     *              example="0"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAR REQUEST",
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
    public function removeTagAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('tagID') && !empty($request->request->get('tagID'))){
                $tag = $em->getRepository("AppBundle:Tag")->findOneBy(['id'=>$request->request->get('tagID'), 'page_id'=>$page->getPageId()]);
                if($tag instanceof Tag){
                    if($request->request->has('subscribers') && !empty($request->request->get('subscribers'))){
                        $counter = SubscriberActionHelper::removeTag($em, $page, $tag->getId(), $request->request->get('subscribers'));

                        $view = $this->view(['counter'=>$counter], Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Subscribers is required and should be empty"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Tag Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"tagID is required"
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     *
     * @Rest\Post("/subscribeSequence", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/subscriber/action/subscribeSequence",
     *   tags={"SUBSCRIBER ACTION"},
     *   security=false,
     *   summary="SUBSCRIBE TO SEQUENCE TO SUBSCRIBERS",
     *   description="The method for subscribe to sequence to subscribers ",
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
     *              property="sequenceID",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="subscribers",
     *              type="array",
     *              @SWG\Items(
     *                  type="integer"
     *              )
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="counter",
     *              type="integer",
     *              example="0"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAR REQUEST",
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
    public function subscribeToSequenceAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('sequenceID') && !empty($request->request->get('sequenceID'))){
                $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['id'=>$request->request->get('sequenceID'), 'page_id'=>$page->getPageId()]);
                if($sequence instanceof Sequences){
                    if($request->request->has('subscribers') && !empty($request->request->get('subscribers'))){
                        $counter = SubscriberActionHelper::subscribeSequence($em, $page, $sequence->getId(), $request->request->get('subscribers'));

                        $view = $this->view(['counter'=>$counter], Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Subscribers is required and should be empty"
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
                        'message'=>"sequenceID is required"
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/ubSubscribeSequence", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/subscriber/action/ubSubscribeSequence",
     *   tags={"SUBSCRIBER ACTION"},
     *   security=false,
     *   summary="UNSUBSCRIBE FROM SEQUENCE TO SUBSCRIBERS",
     *   description="The method for unsubscribe from sequence to subscribers ",
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
     *              property="sequenceID",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="subscribers",
     *              type="array",
     *              @SWG\Items(
     *                  type="integer"
     *              )
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="counter",
     *              type="integer",
     *              example="0"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAR REQUEST",
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
    public function unSubscribeFromSequenceAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('sequenceID') && !empty($request->request->get('sequenceID'))){
                $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['id'=>$request->request->get('sequenceID'), 'page_id'=>$page->getPageId()]);
                if($sequence instanceof Sequences){
                    if($request->request->has('subscribers') && !empty($request->request->get('subscribers'))){
                        $counter = SubscriberActionHelper::unSubscribeSequence($em, $page, $sequence->getId(), $request->request->get('subscribers'));

                        $view = $this->view(['counter'=>$counter], Response::HTTP_OK);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Subscribers is required and should be empty"
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
                        'message'=>"sequenceID is required"
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/setCustomField", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/subscriber/action/setCustomField",
     *   tags={"SUBSCRIBER ACTION"},
     *   security=false,
     *   summary="SET CUSTOM FIELD TO SUBSCRIBERS",
     *   description="The method for set custom field to subscribers ",
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
     *              property="customFieldID",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="value",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="subscribers",
     *              type="array",
     *              @SWG\Items(
     *                  type="integer"
     *              )
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAR REQUEST",
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
    public function setCustomFieldsAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('customFieldID') && !empty($request->request->get('customFieldID'))){
                $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['id'=>$request->request->get('customFieldID'), 'page_id'=>$page->getPageId()]);
                if($customField instanceof CustomFields){
                    if($request->request->has('value') && !empty($request->request->get('value'))){
                        if($request->request->has('subscribers') && !empty($request->request->get('subscribers'))){
                            SubscriberActionHelper::setCustomField($em, $page, $customField->getId(), $request->request->get('subscribers'), $request->request->get('value'));

                            $view = $this->view([], Response::HTTP_NO_CONTENT);
                            return $this->handleView($view);
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Subscribers is required and should be empty"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"value is required and should be empty"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Custom Field Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"customFieldID is required"
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/clearCustomField", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/subscriber/action/clearCustomField",
     *   tags={"SUBSCRIBER ACTION"},
     *   security=false,
     *   summary="CLEAR CUSTOM FIELD TO SUBSCRIBERS",
     *   description="The method for clear custom field to subscribers ",
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
     *              property="customFieldID",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="subscribers",
     *              type="array",
     *              @SWG\Items(
     *                  type="integer"
     *              )
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAR REQUEST",
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
    public function clearCustomFieldAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('customFieldID') && !empty($request->request->get('customFieldID'))){
                $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['id'=>$request->request->get('customFieldID'), 'page_id'=>$page->getPageId()]);
                if($customField instanceof CustomFields){
                    if($request->request->has('subscribers') && !empty($request->request->get('subscribers'))){
                        SubscriberActionHelper::clearCustomField($em, $page, $customField->getId(), $request->request->get('subscribers'));

                        $view = $this->view([], Response::HTTP_NO_CONTENT);
                        return $this->handleView($view);
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Subscribers is required and should be empty"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Custom Field Not Found"
                        ]
                    ], Response::HTTP_NOT_FOUND);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"customFieldID is required"
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/unsubscribe", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/subscriber/action/unsubscribe",
     *   tags={"SUBSCRIBER ACTION"},
     *   security=false,
     *   summary="Unsubscribe FROM BOT SUBSCRIBERS BY PAGE_ID ",
     *   description="The method for Unsubscribe from bot subscribers by page_id",
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
     *              property="subscribers",
     *              type="array",
     *              @SWG\Items(
     *                  type="integer"
     *              )
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="counter",
     *              type="integer",
     *              example="0"
     *          )
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAR REQUEST",
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
    public function unSubscribeBotAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('subscribers') && !empty($request->request->get('subscribers'))){
                $counter = SubscriberActionHelper::unSubscribeBot($em, $page, $request->request->get('subscribers'));

                $view = $this->view(['counter'=>$counter], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Subscribers required and should be empty"
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @Rest\Post("/remove", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/subscriber/action/remove",
     *   tags={"SUBSCRIBER ACTION"},
     *   security=false,
     *   summary="REMOVE SUBSCRIBERS FROM BOT BY PAGE_ID ",
     *   description="The method for removing subscribers by page_id",
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
     *              property="subscribers",
     *              type="array",
     *              @SWG\Items(
     *                  type="integer"
     *              )
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="counterAll",
     *              type="integer",
     *              example=0
     *          ),
     *          @SWG\Property(
     *              property="counterSubscriber",
     *              type="integer",
     *              example=0
     *          ),
     *          @SWG\Property(
     *              property="counterUnSubscriber",
     *              type="integer",
     *              example=0
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAR REQUEST",
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
    public function removeFromBotAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('subscribers') && !empty($request->request->get('subscribers'))){
                $result = SubscriberActionHelper::removeFromBot($em, $page, $request->request->get('subscribers'));

                $view = $this->view($result, Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Subscribers required and should be empty"
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
     * @Rest\Post("/export", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/subscriber/action/export",
     *   tags={"SUBSCRIBER ACTION"},
     *   security=false,
     *   summary="EXPORT PSIDs",
     *   description="The method for export PSIDs ",
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
     *              property="subscribers",
     *              type="array",
     *              @SWG\Items(
     *                  type="integer"
     *              )
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(
     *                  property="psid",
     *                  type="integer",
     *              )
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="BAR REQUEST",
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
    public function exportAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($page->getUser()->getProduct() instanceof DigistoreProduct && $page->getUser()->getProduct()->getDownloadPsid() == true){
                if($request->request->has('subscribers') && !empty($request->request->get('subscribers'))){

                    $view = $this->view(SubscriberActionHelper::exportToPSID($em, $page, $request->request->get('subscribers')), Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Subscribers is required and should be empty"
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
}
