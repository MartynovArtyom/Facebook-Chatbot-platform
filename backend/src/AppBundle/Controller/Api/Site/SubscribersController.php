<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 * Time: 13:52
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\Conversation;
use AppBundle\Entity\CustomFields;
use AppBundle\Entity\Page;
use AppBundle\Entity\PageAdmins;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\SubscribersCustomFields;
use AppBundle\Entity\SubscribersSequences;
use AppBundle\Entity\SubscribersTags;
use AppBundle\Entity\SubscribersWidgets;
use AppBundle\Entity\Tag;
use AppBundle\Entity\Widget;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class SubscribersController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/subscriber")
 */
class SubscribersController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/subscriber/",
     *   tags={"SUBSCRIBER"},
     *   security=false,
     *   summary="GET SUBSCRIBERS BY PAGE_ID",
     *   description="The method for getting subscribers by page_id",
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
     *      name="search",
     *      in="query",
     *      required=false,
     *      type="string",
     *      default="",
     *      description="search by firstName and lastName"
     *   ),
     *   @SWG\Parameter(
     *      name="targeting",
     *      in="query",
     *      required=false,
     *      type="array",
     *      @SWG\Items(
     *          type="array",
     *          @SWG\Items(
     *              type="string"
     *          ),
     *      ),
     *      description="NEED INCLUDE system, tags, widgets, sequences, customFields.
     ***        system ***
     *          Array of objects must have fields: name,criteria,value.
     *          Name: status, criteria: is, value: subscribe or unsubscribe;
     *          Name: gender, criteria: is, value: male or female;
     *          Name: locale, criteria: is or isn't, value: locale;
     *          Name: language, criteria: is or isn't, value: language;
     *          Name: timezone, criteria: is or isn't or greater_than or less_than, value: integer;
     *          Name: firstName, criteria: is or isn't or contains or not_contains, value: string;
     *          Name: lastName, criteria: is or isn't or contains or not_contains, value: string;
     *          Name: dateSubscribed, criteria: after or before or on, value: date;
     *          Name: lastInteraction, criteria: after or before or on, value: date;
     *
     ***        tags ***
     *          Array of objects must have fields: criteria(is, isn't),tagID.
     *
     ***        widgets ***
     *          Array of objects must have fields: criteria(is, isn't),widgetID.
     *
     ***        sequences ***
     *          Array of objects must have fields: criteria(is, isn't),sequenceID.
     *
     ***        customFields ***
     *          Array of objects must have fields: customFieldID, value, criteria(see type custom field):
     *          TYPE: text, CRITERIA: is, isn't, contains, not_contains, VALUE: text;
     *          TYPE: number, CRITERIA: is, isn't, greater_than, less_than, VALUE: number;
     *          TYPE: Date\Datetime, CRITERIA: after, before, on, VALUE: date/datetime;
     *          TYPE: Boolean, CRITERIA: is, VALUE: true or false;"
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
     *                  description="id"
     *              ),
     *              @SWG\Property(
     *                  property="page_id",
     *                  type="string",
     *                  description="page_id"
     *              ),
     *              @SWG\Property(
     *                  property="subscriber_id",
     *                  type="string",
     *                  description="subscriber_id"
     *              ),
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="string",
     *                  description="firstName"
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="string",
     *                  description="lastName"
     *              ),
     *              @SWG\Property(
     *                  property="gender",
     *                  type="string",
     *                  description="gender"
     *              ),
     *              @SWG\Property(
     *                  property="locale",
     *                  type="string",
     *                  description="locale"
     *              ),
     *              @SWG\Property(
     *                  property="timezone",
     *                  type="string",
     *                  description="timezone"
     *              ),
     *              @SWG\Property(
     *                  property="avatar",
     *                  type="string",
     *                  description="avatar"
     *              ),
     *              @SWG\Property(
     *                  property="lastInteraction",
     *                  type="date",
     *                  description="lastInteraction"
     *              ),
     *              @SWG\Property(
     *                  property="dateSubscribed",
     *                  type="date",
     *                  description="dateSubscribed"
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="boolean",
     *                  description="status"
     *              ),
     *              @SWG\Property(
     *                  property="lastSaveAvatar",
     *                  type="date",
     *                  description="lastSaveAvatar"
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
    public function getAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $params = [];
            if($request->query->has('targeting')){
                $params = json_decode($request->query->get('targeting'), true);
            }
            if($request->query->has('search')){
                $params['search'] = $request->query->get('search');
            }
            $subscribers = $em->getRepository("AppBundle:Subscribers")->getSubscribersByPageId($page_id, $params);

            $view = $this->view($subscribers, Response::HTTP_OK);
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
     * @param $subscriberID
     * @return Response
     *
     * @Rest\Get("/{subscriberID}", requirements={"page_id"="\d+","subscriberID"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/subscriber/{subscriberID}",
     *   tags={"SUBSCRIBER"},
     *   security=false,
     *   summary="GET SUBSCRIBERS BY ID BY PAGE_ID",
     *   description="The method for getting subscribers by id by page_id",
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
     *      name="subscriberID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="subscriberID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="object",
     *              property="details",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="id"
     *              ),
     *              @SWG\Property(
     *                  property="subscriber_id",
     *                  type="string",
     *                  description="subscriber_id"
     *              ),
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="string",
     *                  description="firstName"
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="string",
     *                  description="lastName"
     *              ),
     *              @SWG\Property(
     *                  property="gender",
     *                  type="string",
     *                  description="gender"
     *              ),
     *              @SWG\Property(
     *                  property="locale",
     *                  type="string",
     *                  description="locale"
     *              ),
     *              @SWG\Property(
     *                  property="localeName",
     *                  type="string",
     *                  description="localeName"
     *              ),
     *              @SWG\Property(
     *                  property="timezone",
     *                  type="string",
     *                  description="timezone"
     *              ),
     *              @SWG\Property(
     *                  property="avatar",
     *                  type="string",
     *                  description="avatar"
     *              ),
     *              @SWG\Property(
     *                  property="lastInteraction",
     *                  type="date",
     *                  description="lastInteraction"
     *              ),
     *              @SWG\Property(
     *                  property="dateSubscribed",
     *                  type="date",
     *                  description="dateSubscribed"
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="boolean",
     *                  description="status"
     *              )
     *          ),
     *          @SWG\Property(
     *              property="subscriberTags",
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
     *              property="subscriberSequences",
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
     *              property="subscriberWidgets",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="widgetID",
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
     *              property="allTags",
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
     *              property="allSequences",
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
     *              property="allCustomFields",
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
     *                  ),
     *                  @SWG\Property(
     *                      property="value",
     *                      type="string",
     *                      example="value"
     *                  )
     *              )
     *          ),
     *          @SWG\Property(
     *              property="conversationStatus",
     *              type="boolean",
     *              example=true
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
     *              example=401
     *          ),
     *          @SWG\Property(
     *              property="message",
     *              type="string",
     *              example="Invalid JWT Token",
     *          )
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
    public function getByIdAction(Request $request, $page_id, $subscriberID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(), 'subscriber_id'=>$subscriberID]);
            if($subscriber instanceof Subscribers){
                //-------SUBSCRIBER TAGS-----------
                $resultSubscriberTags = $em->getRepository("AppBundle:SubscribersTags")->findBy(['subscriber'=>$subscriber]);
                $subscriberTags = [];
                if(!empty($resultSubscriberTags)){
                    foreach ($resultSubscriberTags as $subscriberTag){
                        if($subscriberTag instanceof SubscribersTags){
                            if($subscriberTag->getTag() instanceof Tag){
                                $subscriberTags[] = [
                                    'tagID' => $subscriberTag->getTag()->getId(),
                                    'name' => $subscriberTag->getTag()->getName()
                                ];
                            }
                        }
                    }
                }

                //-------SUBSCRIBER SEQUENCES-----------
                $resultSubscriberSequences = $em->getRepository("AppBundle:SubscribersSequences")->findBy(['subscriber'=>$subscriber]);
                $subscriberSequences = [];
                if(!empty($resultSubscriberSequences)){
                    foreach ($resultSubscriberSequences as $subscriberSequence){
                        if($subscriberSequence instanceof SubscribersSequences){
                            if($subscriberSequence->getSequence() instanceof Sequences){
                                $subscriberSequences[] = [
                                    'sequenceID' => $subscriberSequence->getSequence()->getId(),
                                    'name' => $subscriberSequence->getSequence()->getTitle()
                                ];
                            }
                        }
                    }
                }

                //-------SUBSCRIBER WIDGETS-----------
                $resultSubscriberWidgets = $em->getRepository("AppBundle:SubscribersWidgets")->findBy(['subscriber'=>$subscriber]);
                $subscriberWidgets = [];
                if(!empty($resultSubscriberWidgets)){
                    foreach ($resultSubscriberWidgets as $subscriberWidget){
                        if($subscriberWidget instanceof SubscribersWidgets){
                            if($subscriberWidget->getWidget() instanceof Widget){
                                $subscriberWidgets[] = [
                                    'widgetID' => $subscriberWidget->getWidget()->getId(),
                                    'name' => $subscriberWidget->getWidget()->getName()
                                ];
                            }
                        }
                    }
                }

                //-------ALL TAGS----------------
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
                //----------ALL SEQUENCE----------------------
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

                //-----------ALL CUSTOM FIELD------------------
                $allCustomFields = $em->getRepository("AppBundle:CustomFields")->findBy(['page_id'=>$page->getPageId(),'status'=>true]);
                $customFields = [];
                if(!empty($allCustomFields)){
                    foreach ($allCustomFields as $customField){
                        if($customField instanceof CustomFields){
                            $value = null;
                            $subscriberCustomField = $em->getRepository("AppBundle:SubscribersCustomFields")->findOneBy(['subscriber'=>$subscriber,'customField'=>$customField]);
                            if($subscriberCustomField instanceof SubscribersCustomFields){
                                $value = $subscriberCustomField->getValue();
                            }
                            $customFields[] = [
                                'customFieldID' => $customField->getId(),
                                'name' => $customField->getName(),
                                'type' => $customField->getType(),
                                'value' => $value
                            ];
                        }
                    }
                }

                $conversation = $em->getRepository("AppBundle:Conversation")->findOneBy(['subscriber'=>$subscriber]);
                $conversationStatus = false;
                if($conversation instanceof Conversation){
                    $conversationStatus = $conversation->getStatus();
                }

                $view = $this->view([
                    'details' => [
                        'id' => $subscriber->getId(),
                        'subscriber_id' => $subscriber->getSubscriberId(),
                        'firstName' => $subscriber->getFirstName(),
                        'lastName' => $subscriber->getLastName(),
                        'gender' => $subscriber->getGender(),
                        'locale' => $subscriber->getLocale(),
                        'localeName' => locale_get_display_name($subscriber->getLocale()),
                        'timezone' => $subscriber->getTimezone(),
                        'avatar' => $subscriber->getAvatar(),
                        'lastInteraction' => $subscriber->getLastInteraction(),
                        'dateSubscribed' => $subscriber->getDateSubscribed(),
                        'status' => $subscriber->getStatus()
                    ],
                    'subscriberTags'=> $subscriberTags,
                    'subscriberSequences' => $subscriberSequences,
                    'subscriberWidgets' => [],
                    'allTags'=> $tags,
                    'allSequences' => $sequences,
                    'allCustomFields' => $customFields,
                    'conversationStatus' => $conversationStatus
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Subscriber Not Found"
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
     *
     * @Rest\Get("/filters", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/subscriber/filters",
     *   tags={"SUBSCRIBER"},
     *   security=false,
     *   summary="GET FILTERS FOR SUBSCRIBERS BY PAGE_ID",
     *   description="The method for getting filters for subscribers by page_id",
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
     *              property="allSubscribers",
     *              type="integer",
     *              example=10,
     *          ),
     *          @SWG\Property(
     *              property="subscriberSubscribers",
     *              type="integer",
     *              example=10,
     *          ),
     *          @SWG\Property(
     *              property="unSubscriberSubscribers",
     *              type="integer",
     *              example=10,
     *          ),
     *          @SWG\Property(
     *              property="system",
     *              type="object",
     *              @SWG\Property(
     *                  property="gender",
     *                  type="object",
     *                  @SWG\Property(
     *                      property="male",
     *                      type="integer",
     *                      example=5,
     *                  ),
     *                  @SWG\Property(
     *                      property="female",
     *                      type="integer",
     *                      example=5,
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="locale",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      title="localeID",
     *                      @SWG\Property(
     *                          property="value",
     *                          type="string",
     *                          example="ru_RU",
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string",
     *                          example="Russia",
     *                      ),
     *                      @SWG\Property(
     *                          property="subscriberCount",
     *                          type="integer",
     *                          example=2,
     *                      )
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="language",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object",
     *                      title="languageID",
     *                      @SWG\Property(
     *                          property="value",
     *                          type="string",
     *                          example="ru_RU",
     *                      ),
     *                      @SWG\Property(
     *                          property="name",
     *                          type="string",
     *                          example="Russian",
     *                      ),
     *                      @SWG\Property(
     *                          property="subscriberCount",
     *                          type="integer",
     *                          example=2,
     *                      )
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="string"
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="string"
     *                  )
     *              )
     *          ),
     *          @SWG\Property(
     *              property="tags",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="tagID",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="tagName",
     *                  ),
     *                  @SWG\Property(
     *                      property="subscriberCount",
     *                      type="integer",
     *                      example=2,
     *                  )
     *              )
     *          ),
     *          @SWG\Property(
     *              property="widgets",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="widgetID",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="widgetName",
     *                  ),
     *                  @SWG\Property(
     *                      property="subscriberCount",
     *                      type="integer",
     *                      example=2,
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
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="sequenceName",
     *                  ),
     *                  @SWG\Property(
     *                      property="subscriberCount",
     *                      type="integer",
     *                      example=2,
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
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                      example="customFieldName",
     *                  ),
     *                  @SWG\Property(
     *                      property="type",
     *                      type="integer",
     *                      example=1,
     *                      description="1 = Text, 2 = Number, 3 = Date, 4 = DateTime, 5 = Boolean"
     *                  ),
     *                  @SWG\Property(
     *                      property="values",
     *                      type="array",
     *                      @SWG\Items(
     *                          type="string"
     *                      )
     *                  )
     *              )
     *          ),
     *          @SWG\Property(
     *              property="admins",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="adminID",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @SWG\Property(
     *                      property="firstName",
     *                      type="string",
     *                      example="firstName",
     *                  ),
     *                  @SWG\Property(
     *                      property="lastName",
     *                      type="string",
     *                      example="lastName",
     *                  ),
     *                  @SWG\Property(
     *                      property="role",
     *                      type="integer",
     *                      description="1=Admin, 2=Editor, 3=Live Chat Agent, 4=Viewer"
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
    public function getFiltersAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $subscribers = $em->getRepository("AppBundle:Subscribers")->findBy(['page_id'=>$page->getPageId()]);
            $subscriberSubscribers = 0;
            $unSubscriberSubscribers = 0;
            //----------------SYSTEM-------------
            $system = [];
            $system['gender'] = $system['locale'] = $system['language'] = $system['firstName'] = $system['lastName'] = [];
            $system['gender']['male'] = 0;
            $system['gender']['female'] = 0;
            if(!empty($subscribers)){
                foreach ($subscribers as $subscriber){
                    if($subscriber instanceof Subscribers){
                        //----Count Status----
                        if($subscriber->getStatus() == true){
                            $subscriberSubscribers = $subscriberSubscribers + 1;
                        }
                        else{
                            $unSubscriberSubscribers = $unSubscriberSubscribers + 1;
                        }
                        //----Count Gender-----
                        if(!empty($subscriber->getGender())){
                            if(!isset($system['gender'][$subscriber->getGender()])){
                                $system['gender'][$subscriber->getGender()] = 0;
                            }
                            $system['gender'][$subscriber->getGender()] = $system['gender'][$subscriber->getGender()] + 1;
                        }

                        //--------LOCALE & LANGUAGE--------
                        if(!empty($subscriber->getLocale())){
                            //------LOCALE------
                            if(!isset($system['locale'][$subscriber->getLocale()])){
                                $locale = locale_get_display_name($subscriber->getLocale());
                                $system['locale'][$subscriber->getLocale()] = [
                                    'value' => $subscriber->getLocale(),
                                    'name' => $locale,
                                    'subscriberCount' => 1
                                ];
                            }
                            else{
                                if(isset($system['locale'][$subscriber->getLocale()]['subscriberCount'])){
                                    $system['locale'][$subscriber->getLocale()]['subscriberCount'] = $system['locale'][$subscriber->getLocale()]['subscriberCount']+1;
                                }
                                else{
                                    $system['locale'][$subscriber->getLocale()]['subscriberCount'] = 1;
                                }
                            }

                            //------LANGUAGE-------
                            if(!isset($system['language'][$subscriber->getLocale()])){
                                $language = locale_get_display_language($subscriber->getLocale());
                                $system['language'][$subscriber->getLocale()] = [
                                    'value' => $subscriber->getLocale(),
                                    'name' => $language,
                                    'subscriberCount' => 1
                                ];
                            }
                            else{
                                if(isset($system['language'][$subscriber->getLocale()]['subscriberCount'])){
                                    $system['language'][$subscriber->getLocale()]['subscriberCount'] = $system['language'][$subscriber->getLocale()]['subscriberCount']+1;
                                }
                                else{
                                    $system['language'][$subscriber->getLocale()]['subscriberCount'] = 1;
                                }
                            }

                        }

                        //--------GET FIRST NAMEs------------
                        if(!empty($subscriber->getFirstName()) && !in_array($subscriber->getFirstName(),$system['firstName'])){
                            $system['firstName'][] = $subscriber->getFirstName();
                        }

                        //--------GET LAST NAMEs------------
                        if(!empty($subscriber->getLastName()) && !in_array($subscriber->getLastName(),$system['lastName'])){
                            $system['lastName'][] = $subscriber->getLastName();
                        }
                    }
                }
            }
            //-------TAGS----------------
            $allTags = $em->getRepository("AppBundle:Tag")->findBy(['page_id'=>$page->getPageId()]);
            $tags = [];
            if(!empty($allTags)){
                foreach ($allTags as $tag){
                    if($tag instanceof Tag){
                        $subscriberCount = $em->getRepository("AppBundle:SubscribersTags")->count(['tag'=>$tag]);
                        $tags[] = [
                            'tagID' => $tag->getId(),
                            'name' => $tag->getName(),
                            'subscriberCount' => ($subscriberCount > 0) ? $subscriberCount : 0
                        ];
                    }
                }
            }

            //-----------WIDGET---------------------
            $allWidgets = $em->getRepository("AppBundle:Widget")->findBy(['page_id'=>$page->getPageId()]);
            $widgets = [];
            if(!empty($allWidgets)){
                foreach ($allWidgets as $widget){
                    if($widget instanceof Widget){
                        $subscriberCount = $em->getRepository("AppBundle:SubscribersWidgets")->count(['widget'=>$widget]);
                        $widgets[] = [
                            'widgetID' => $widget->getId(),
                            'name' => $widget->getName(),
                            'subscriberCount' => ($subscriberCount > 0) ? $subscriberCount : 0
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
                        $subscriberCount = $em->getRepository("AppBundle:SubscribersSequences")->count(['sequence'=>$sequence]);
                        $sequences[] = [
                            'sequenceID' => $sequence->getId(),
                            'name' => $sequence->getTitle(),
                            'subscriberCount' => ($subscriberCount > 0) ? $subscriberCount : 0
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
                        $values = [];
                        if($customField->getType() == 1){
                            $subscribersCustomFields = $em->getRepository("AppBundle:SubscribersCustomFields")->findBy(['customField'=>$customField]);
                            if(!empty($subscribersCustomFields)){
                                if($subscribersCustomFields instanceof SubscribersCustomFields){
                                    if(!in_array($subscribersCustomFields->getValue(), $values)){
                                        $values[] = $subscribersCustomFields->getValue();
                                    }
                                }
                            }
                        }

                        $customFields[] = [
                            'customFieldID' => $customField->getId(),
                            'name' => $customField->getName(),
                            'type' => $customField->getType(),
                            'values' => $values
                        ];
                    }
                }
            }

            //-----------ADMINS-------------------
            $admins[] = [
                'adminID' => $page->getUser()->getId(),
                'firstName' => $page->getUser()->getFirstName(),
                'lastName' => $page->getUser()->getLastName(),
                'role' => 1
            ];
            $pageAdmins = $em->getRepository("AppBundle:PageAdmins")->findBy(['page_id'=>$page->getPageId()]);
            if(!empty($pageAdmins)){
                foreach ($pageAdmins as $pageAdmin){
                    if($pageAdmin instanceof PageAdmins){
                        $admins[] = [
                            'adminID' => $pageAdmin->getUser()->getId(),
                            'firstName' => $pageAdmin->getUser()->getFirstName(),
                            'lastName' => $pageAdmin->getUser()->getLastName(),
                            'role' => $pageAdmin->getRole()
                        ];
                    }
                }
            }

            $view = $this->view([
                'allSubscribers' => count($subscribers),
                'subscriberSubscribers' => $subscriberSubscribers,
                'unSubscriberSubscribers' => $unSubscriberSubscribers,
                'system' => $system,
                'tags' => $tags,
                'widgets' => $widgets,
                'sequences' => $sequences,
                'customFields' => $customFields,
                'admins' => $admins
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
}
