<?php
/**
 * Created by PhpStorm.
 * Date: 16.01.19
 * Time: 14:46
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
use AppBundle\Flows\Flow;
use AppBundle\Helper\Subscriber\SubscriberActionHelper;
use AppBundle\Helper\Webhook\ZapierHelper;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * Class Zapier
 * @package AppBundle\Controller\Api\WebHook
 *
 * @Rest\Route("/zapier/action")
 */
class ZapierAction extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Post("/add_tag")
     * @SWG\Post(path="/v2/webhook/zapier/action/add_tag",
     *   tags={"ZAPIER ACTION"},
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
    public function addTagAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->request->has('user') && !empty($request->request->get('user')) && $request->request->has('tag') && !empty($request->request->get('tag'))){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$request->request->get("user"), "page_id"=>$page->getPageId()]);
                            $tag = $em->getRepository("AppBundle:Tag")->findOneBy(['id'=>$request->request->get('tag'), 'page_id'=>$page->getPageId()]);
                            if($subscriber instanceof Subscribers){
                                if($tag instanceof Tag){
                                    SubscriberActionHelper::addTag($em, $page, $tag->getId(), [$subscriber->getId()]);

                                    $result = [
                                        'tag' => [
                                            'id' => $tag->getId(),
                                            'name' => $tag->getName()
                                        ],
                                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                    ];
                                    return $this->handleView($this->view($result, Response::HTTP_OK));
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
                            return $this->handleView($this->view(['error'=>'user and tag is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"Action: Add Tag ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Post("/remove_tag")
     * @SWG\Post(path="/v2/webhook/zapier/action/remove_tag",
     *   tags={"ZAPIER ACTION"},
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
    public function removeTagAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->request->has('user') && !empty($request->request->get('user')) && $request->request->has('tag') && !empty($request->request->get('tag'))){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$request->request->get("user"), "page_id"=>$page->getPageId()]);
                            $tag = $em->getRepository("AppBundle:Tag")->findOneBy(['id'=>$request->request->get('tag'), 'page_id'=>$page->getPageId()]);
                            if($subscriber instanceof Subscribers){
                                if($tag instanceof Tag){
                                    SubscriberActionHelper::removeTag($em, $page, $tag->getId(), [$subscriber->getId()]);

                                    $result = [
                                        'tag' => [
                                            'id' => $tag->getId(),
                                            'name' => $tag->getName()
                                        ],
                                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                    ];
                                    return $this->handleView($this->view($result, Response::HTTP_OK));
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
                            return $this->handleView($this->view(['error'=>'user and tag is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"Action: Remove Tag ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Rest\Post("/subscribe_sequence")
     * @SWG\Post(path="/v2/webhook/zapier/action/subscribe_sequence",
     *   tags={"ZAPIER ACTION"},
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
    public function subscribeSequenceAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->request->has('user') && !empty($request->request->get('user')) && $request->request->has('sequence') && !empty($request->request->get('sequence'))){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$request->request->get("user"), "page_id"=>$page->getPageId()]);
                            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['id'=>$request->request->get('sequence'), 'page_id'=>$page->getPageId()]);
                            if($subscriber instanceof Subscribers){
                                if($sequence instanceof Sequences){
                                    SubscriberActionHelper::subscribeSequence($em, $page, $sequence->getId(), [$subscriber->getId()]);

                                    $result = [
                                        'sequence' => [
                                            'id' => $sequence->getId(),
                                            'title' => $sequence->getTitle()
                                        ],
                                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                    ];
                                    return $this->handleView($this->view($result, Response::HTTP_OK));
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
                            return $this->handleView($this->view(['error'=>'user and sequence is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"Action: Subscribe Sequence ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Rest\Post("/unsubscribe_sequence")
     * @SWG\Post(path="/v2/webhook/zapier/action/unsubscribe_sequence",
     *   tags={"ZAPIER ACTION"},
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
    public function unSubscribeSequenceAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->request->has('user') && !empty($request->request->get('user')) && $request->request->has('sequence') && !empty($request->request->get('sequence'))){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$request->request->get("user"), "page_id"=>$page->getPageId()]);
                            $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['id'=>$request->request->get('sequence'), 'page_id'=>$page->getPageId()]);
                            if($subscriber instanceof Subscribers){
                                if($sequence instanceof Sequences){
                                    SubscriberActionHelper::unSubscribeSequence($em, $page, $sequence->getId(), [$subscriber->getId()]);

                                    $result = [
                                        'sequence' => [
                                            'id' => $sequence->getId(),
                                            'title' => $sequence->getTitle()
                                        ],
                                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                    ];
                                    return $this->handleView($this->view($result, Response::HTTP_OK));
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
                            return $this->handleView($this->view(['error'=>'user and sequence is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"Action: Unsubscribe Sequence ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Post("/set_custom_field")
     * @SWG\Post(path="/v2/webhook/zapier/action/set_custom_field",
     *   tags={"ZAPIER ACTION"},
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
                        if(
                            $request->request->has('user') && !empty($request->request->get('user'))
                            && $request->request->has('custom_field') && !empty($request->request->get('custom_field'))
                            && $request->request->has('value') && !empty($request->request->get('value'))
                        ){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$request->request->get("user"), "page_id"=>$page->getPageId()]);
                            $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['id'=>$request->request->get('custom_field'), 'page_id'=>$page->getPageId()]);
                            if($subscriber instanceof Subscribers){
                                if($customField instanceof CustomFields){
                                    SubscriberActionHelper::setCustomField($em, $page, $customField->getId(), [$subscriber->getId()], $request->request->get('value'));

                                    $result = [
                                        'field_value' => $request->request->get('value'),
                                        'custom_field' => [
                                            'id' => $customField->getId(),
                                            'name' => $customField->getName(),
                                            'type' => $customField->getType(),
                                            'description' => $customField->getDescription()
                                        ],
                                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                    ];
                                    return $this->handleView($this->view($result, Response::HTTP_OK));
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
                            return $this->handleView($this->view(['error'=>'user and custom_field and value is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"Action: Set Custom Field ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Post("/send_message_text")
     * @SWG\Post(path="/v2/webhook/zapier/action/send_message_text",
     *   tags={"ZAPIER ACTION"},
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
    public function sendMessageTextAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->request->has('user') && !empty($request->request->get('user')) && $request->request->has('text_message') && !empty($request->request->get('text_message'))){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$request->request->get("user"), "page_id"=>$page->getPageId()]);
                            if($subscriber instanceof Subscribers){
                                SubscriberActionHelper::sendMessageText($page, $subscriber, $request->request->get('text_message'));

                                $result = [
                                    'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                ];
                                return $this->handleView($this->view($result, Response::HTTP_OK));
                            }
                            else{
                                return $this->handleView($this->view(['error'=>'User not found'], Response::HTTP_BAD_REQUEST));
                            }
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'user and text_message is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"Action: Send Message Text ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Rest\Post("/send_flow")
     * @SWG\Post(path="/v2/webhook/zapier/action/send_flow",
     *   tags={"ZAPIER ACTION"},
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
    public function sendFlowAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->request->has('user') && !empty($request->request->get('user')) && $request->request->has('flow') && !empty($request->request->get('flow'))){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$request->request->get("user"), "page_id"=>$page->getPageId()]);
                            $flow = $em->getRepository("AppBundle:Flows")->findOneBy(['id'=>$request->request->get('flow'), 'page_id'=>$page->getPageId()]);
                            if($subscriber instanceof Subscribers){
                                if($flow instanceof Flows){
                                    if($flow->getStatus() == true){
                                        $flowSend = new Flow($em, $flow, $subscriber);
                                        $flowSend->sendStartStep();

                                        $result = [
                                            'flow' => [
                                                'id' => $flow->getId(),
                                                'name' => $flow->getName()
                                            ],
                                            'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                        ];
                                        return $this->handleView($this->view($result, Response::HTTP_OK));
                                    }
                                    else{
                                        return $this->handleView($this->view(['error'=>'Flow is inactive'], Response::HTTP_BAD_REQUEST));
                                    }
                                }
                                else{
                                    return $this->handleView($this->view(['error'=>'Flow not found'], Response::HTTP_BAD_REQUEST));
                                }
                            }
                            else{
                                return $this->handleView($this->view(['error'=>'User not found'], Response::HTTP_BAD_REQUEST));
                            }
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'user and flow is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"Action: Send Flow ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/find_by_name")
     * @SWG\Get(path="/v2/webhook/zapier/action/find_by_name",
     *   tags={"ZAPIER ACTION"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Parameter(
     *      name="name",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="name"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function findUserByNameAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->query->has('name') && !empty($request->query->get('name'))){
                            $subscribers = $em->getRepository("AppBundle:Subscribers")->getSubscriberByName($page->getPageId(), $request->query->get('name'));
                            $result = [];
                            if(!empty($subscribers)){
                                foreach ($subscribers as $subscriber){
                                    if($subscriber instanceof Subscribers){
                                        $result[] = [
                                            'id' => $subscriber->getSubscriberId(),
                                            'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                        ];
                                    }
                                }
                            }
                            return $this->handleView($this->view($result, Response::HTTP_OK));
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'name is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"Action: Find User By Name ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/find_by_user_id")
     * @SWG\Get(path="/v2/webhook/zapier/action/find_by_user_id",
     *   tags={"ZAPIER ACTION"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Parameter(
     *      name="user_id",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="user_id"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function findUserByUserIdAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->query->has('user_id') && !empty($request->query->get('user_id'))){
                            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$request->query->get('user_id'), 'page_id'=>$page->getPageId()]);
                            $result = [];
                            if($subscriber instanceof Subscribers){
                                $result[] = [
                                    'id' => $subscriber->getSubscriberId(),
                                    'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                                ];
                            }
                            return $this->handleView($this->view($result, Response::HTTP_OK));
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'user_id is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"Action: Find User By ID ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/find_by_custom_field")
     * @SWG\Get(path="/v2/webhook/zapier/action/find_by_custom_field",
     *   tags={"ZAPIER ACTION"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Parameter(
     *      name="custom_field",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="custom_field"
     *   ),
     *   @SWG\Parameter(
     *      name="custom_field_value",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="custom_field_value"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function findUserByCustomFieldAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->query->has('custom_field') && !empty($request->query->get('custom_field'))
                            && $request->query->has('custom_field_value') && !empty($request->query->get('custom_field_value'))
                        ){
                            $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$request->query->get('custom_field'), 'status'=>true]);
                            if($customField instanceof CustomFields){
                                $subscribersCustomField = $em->getRepository("AppBundle:SubscribersCustomFields")->findBy(['customField'=>$customField, 'value'=>$request->query->get('custom_field_value')],['id'=>'DESC']);
                                $result = [];
                                if(!empty($subscribersCustomField)){
                                    foreach ($subscribersCustomField as $subscriberCustomField){
                                        if($subscriberCustomField instanceof SubscribersCustomFields && $subscriberCustomField->getSubscriber() instanceof Subscribers){
                                            $result[] = [
                                                'id' => $subscriberCustomField->getSubscriber()->getSubscriberId(),
                                                'user' => ZapierHelper::generateUserResponse($em, $page, $subscriberCustomField->getSubscriber())
                                            ];
                                        }
                                    }
                                }
                                return $this->handleView($this->view($result, Response::HTTP_OK));
                            }
                            else{
                                return $this->handleView($this->view(['error'=>'Custom Field not found'], Response::HTTP_BAD_REQUEST));
                            }
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'user_id is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"Action: Find User By Custom Field ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Get("/get_user/{id}")
     * @SWG\Get(path="/v2/webhook/zapier/action/get_user/{id}",
     *   tags={"ZAPIER ACTION"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Parameter(
     *      name="id",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="id"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function getUserByIdAction(Request $request, $id){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$id, 'page_id'=>$page->getPageId()]);
                        if($subscriber instanceof Subscribers){
                            $result = [
                                'user' => ZapierHelper::generateUserResponse($em, $page, $subscriber)
                            ];
                            return $this->handleView($this->view($result, Response::HTTP_OK));
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'User not Found'], Response::HTTP_NOT_FOUND));
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
            $fs->appendToFile('zapier_request.txt',"Action: GET User By ID ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

}