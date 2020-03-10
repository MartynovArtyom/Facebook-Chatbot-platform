<?php
/**
 * Created by PhpStorm.
 * Date: 16.01.19
 * Time: 17:30
 */

namespace AppBundle\Controller\Api\WebHook\Zapier;

use AppBundle\Entity\CustomFields;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\SubscribersCustomFields;
use AppBundle\Entity\Tag;
use AppBundle\Entity\ZapierApiKey;
use AppBundle\Helper\Subscriber\SubscriberActionHelper;
use AppBundle\Helper\Webhook\ZapierHelper;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * Class ZapierTrigger
 * @package AppBundle\Controller\Api\WebHook\Zapier
 *
 * @Rest\Route("/zapier/trigger")
 */
class ZapierTrigger extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/connect")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/connect",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function connectAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        return $this->handleView($this->view([
                            "page" => [
                                "page_id" => $page->getPageId(),
                                "title" => $page->getTitle()
                            ]
                        ], Response::HTTP_OK));
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: Connect ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/get_user")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/get_user",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function getUserAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        $limit = 10;
                        if($request->query->has('page')){
                            $limit = $limit * $request->query->getInt('page',1);
                        }
                        $usersResult = $em->getRepository("AppBundle:Subscribers")->findBy(['page_id'=>$page->getPageId(), 'status'=>true],['lastInteraction'=>"DESC"],$limit);
                        $users = [];
                        if(!empty($usersResult)){
                            foreach ($usersResult as $user){
                                if($user instanceof Subscribers){
                                    $users[] = [
                                        'id' => $user->getSubscriberId(),
                                        'full_name' => $user->getFirstName()." ".$user->getLastName()
                                    ];
                                }
                            }
                        }

                        return $this->handleView($this->view($users, Response::HTTP_OK));
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: Get User ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/get_tag")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/get_tag",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function getTagAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        $limit = 10;
                        if($request->query->has('page')){
                            $limit = $limit * $request->query->getInt('page',1);
                        }
                        $tagsResult = $em->getRepository("AppBundle:Tag")->findBy(['page_id'=>$page->getPageId()], ['id'=>'DESC'], $limit);
                        $tags = [];
                        if(!empty($tagsResult)){
                            foreach ($tagsResult as $tag){
                                if($tag instanceof Tag){
                                    $tags[] = [
                                        'id' => $tag->getId(),
                                        'name' => $tag->getName()
                                    ];
                                }
                            }
                        }

                        return $this->handleView($this->view($tags, Response::HTTP_OK));
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: Get Tag ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/get_sequence")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/get_sequence",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function getSequenceAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        $limit = 10;
                        if($request->query->has('page')){
                            $limit = $limit * $request->query->getInt('page',1);
                        }
                        $sequencesResult = $em->getRepository("AppBundle:Sequences")->findBy(['page_id'=>$page->getPageId()], ["id"=>"DESC"], $limit);
                        $sequences = [];
                        if(!empty($sequencesResult)){
                            foreach ($sequencesResult as $sequence){
                                if($sequence instanceof Sequences){
                                    $sequences[] = [
                                        'id' => $sequence->getId(),
                                        'title' => $sequence->getTitle()
                                    ];
                                }
                            }
                        }

                        return $this->handleView($this->view($sequences, Response::HTTP_OK));
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: Get Sequence ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/get_custom_field")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/get_custom_field",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function getCustomFieldAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        $limit = 10;
                        if($request->query->has('page')){
                            $limit = $limit * $request->query->getInt('page',1);
                        }
                        $customFieldsResult = $em->getRepository("AppBundle:CustomFields")->findBy(['page_id'=>$page->getPageId(),'status'=>true], ['id'=>'DESC'], $limit);
                        $customFields = [];
                        if(!empty($customFieldsResult)){
                            foreach ($customFieldsResult as $customField){
                                if($customField instanceof CustomFields){
                                    $customFields[] = [
                                        'id' => $customField->getId(),
                                        'name' => $customField->getName(),
                                        'type' => $customField->getType()
                                    ];
                                }
                            }
                        }

                        return $this->handleView($this->view($customFields, Response::HTTP_OK));
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: Get Custom Field ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/get_flow")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/get_flow",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function getFlowAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        $limit = 10;
                        if($request->query->has('page')){
                            $limit = $limit * $request->query->getInt('page',1);
                        }
                        $flowsResult = $em->getRepository("AppBundle:Flows")->findBy(['page_id'=>$page->getPageId(),'status'=>true], ['modified'=>'DESC'], $limit);
                        $flows = [];
                        if(!empty($flowsResult)){
                            foreach ($flowsResult as $flow){
                                if($flow instanceof Flows){
                                    $flows[] = [
                                        'id' => $flow->getId(),
                                        'name' => $flow->getName()
                                    ];
                                }
                            }
                        }

                        return $this->handleView($this->view($flows, Response::HTTP_OK));
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: Get Flow ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/new_subscriber")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/new_subscriber",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function newSubscriberAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(),'status'=>true],['lastInteraction'=>'DESC']);
                        if($subscriber instanceof Subscribers){
                            $result = [
                                'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                            ];

                            return $this->handleView($this->view([$result], Response::HTTP_OK));
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'Subscriber not found'], Response::HTTP_BAD_REQUEST));
                        }
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: New Subscriber ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/set_custom_field")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/set_custom_field",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function setCustomFieldAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->query->has('custom_field') && !empty($request->query->get('custom_field'))){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(),'status'=>true],['lastInteraction'=>'DESC']);
                            $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['id'=>$request->query->get('custom_field'), 'page_id'=>$page->getPageId()]);
                            if($subscriber instanceof Subscribers){
                                if($customField instanceof CustomFields){
                                    $value = null;
                                    $subscriberCustomField = $em->getRepository("AppBundle:SubscribersCustomFields")->findOneBy(['subscriber'=>$subscriber, 'customField'=>$customField]);
                                    if($subscriberCustomField instanceof SubscribersCustomFields){
                                        $value = $subscriberCustomField->getValue();
                                    }
                                    $result = [
                                        'field_value' => $value,
                                        'custom_field' => [
                                            'id' => $customField->getId(),
                                            'name' => $customField->getName(),
                                            'type' => $customField->getType(),
                                            'description' => $customField->getDescription()
                                        ],
                                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                    ];

                                    return $this->handleView($this->view([$result], Response::HTTP_OK));
                                }
                                else{
                                    return $this->handleView($this->view(['error'=>'Custom Field not found'], Response::HTTP_BAD_REQUEST));
                                }
                            }
                            else{
                                return $this->handleView($this->view(['error'=>'User not found'], Response::HTTP_BAD_REQUEST));
                            }
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'custom_field is required'], Response::HTTP_BAD_REQUEST));
                        }
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: Set Custom Field ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/new_tag")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/new_tag",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function newTagAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->query->has('tag') && !empty($request->query->get('tag'))){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(),'status'=>true],['lastInteraction'=>'DESC']);
                            $tag = $em->getRepository("AppBundle:Tag")->findOneBy(['id'=>$request->query->get('tag'), 'page_id'=>$page->getPageId()]);
                            if($subscriber instanceof Subscribers){
                                if($tag instanceof Tag){
                                    $result = [
                                        'tag' => [
                                            'id' => $tag->getId(),
                                            'name' => $tag->getName()
                                        ],
                                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                    ];
                                    return $this->handleView($this->view([$result], Response::HTTP_OK));
                                }
                                else{
                                    return $this->handleView($this->view(['error'=>'Tag not found'], Response::HTTP_BAD_REQUEST));
                                }
                            }
                            else{
                                return $this->handleView($this->view(['error'=>'User not found'], Response::HTTP_BAD_REQUEST));
                            }
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'custom_field is required'], Response::HTTP_BAD_REQUEST));
                        }
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: NEW TAG ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/new_sequence")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/new_sequence",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function subscribeSequenceAction(Request  $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->query->has('sequence') && !empty($request->query->get('sequence'))){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(),'status'=>true],['lastInteraction'=>'DESC']);
                            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['id'=>$request->query->get('sequence'), 'page_id'=>$page->getPageId()]);
                            if($subscriber instanceof Subscribers){
                                if($sequence instanceof Sequences){
                                    $result = [
                                        'sequence' => [
                                            'id' => $sequence->getId(),
                                            'title' => $sequence->getTitle()
                                        ],
                                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                    ];
                                    return $this->handleView($this->view([$result], Response::HTTP_OK));
                                }
                                else{
                                    return $this->handleView($this->view(['error'=>'Sequence not found'], Response::HTTP_BAD_REQUEST));
                                }
                            }
                            else{
                                return $this->handleView($this->view(['error'=>'User not found'], Response::HTTP_BAD_REQUEST));
                            }
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'custom_field is required'], Response::HTTP_BAD_REQUEST));
                        }
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: Subscribe Sequence ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/open_chat")
     * @SWG\Get(path="/v2/webhook/zapier/trigger/open_chat",
     *   tags={"ZAPIER TRIGGER"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function openChatAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(),'status'=>true],['lastInteraction'=>'DESC']);
                        if($subscriber instanceof Subscribers){
                            $result = [
                                'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                            ];
                            return $this->handleView($this->view([$result], Response::HTTP_OK));
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'Subscriber not found'], Response::HTTP_BAD_REQUEST));
                        }
                    }
                    else{
                        return $this->handleView($this->view(['error'=>'Bot page inactive'], Response::HTTP_BAD_REQUEST));
                    }
                }
                else{
                    return $this->handleView($this->view(['error'=>'api_key is invalid'], Response::HTTP_BAD_REQUEST));
                }
            }
            else{
                return $this->handleView($this->view(['error'=>'api_key is required'], Response::HTTP_BAD_REQUEST));
            }
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"TRIGGER: Open Chat ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}