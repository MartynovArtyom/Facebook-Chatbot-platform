<?php
/**
 * Created by PhpStorm.
 * Date: 15.11.18
 * Time: 15:25
 */

namespace AppBundle\Controller\Api\Fetch;

use AppBundle\Entity\CustomFields;
use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\MainMenu;
use AppBundle\Entity\MainMenuDraft;
use AppBundle\Entity\MainMenuItems;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\SubscribersCustomFields;
use AppBundle\Entity\User;
use AppBundle\Entity\UserInputDelay;
use AppBundle\Entity\UserInputResponse;
use AppBundle\Flows\FlowsItem;
use AppBundle\Helper\Webhook\ZapierHelper;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use MailchimpAPI\Mailchimp;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * Class FetchController
 * @package AppBundle\Controller\Api\Fetch
 *
 */
class FetchController extends FOSRestController
{

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Post("/set_user_input")
     * @SWG\Post(path="/v2/fetch/set_user_input",
     *   tags={"FETCH"},
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="pageID",
     *              type="integer",
     *              example="pageID"
     *          ),
     *          @SWG\Property(
     *              property="subscriberID",
     *              type="integer",
     *              example="subscriberID"
     *          ),
     *          @SWG\Property(
     *              property="flowID",
     *              type="integer",
     *              example="flowID"
     *          ),
     *          @SWG\Property(
     *              property="flowItemUuid",
     *              type="string",
     *              example="flowItemUuid"
     *          ),
     *          @SWG\Property(
     *              property="itemUuid",
     *              type="string",
     *              example="itemUuid"
     *          ),
     *          @SWG\Property(
     *              property="response",
     *              type="string",
     *              example="response"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function setUserInputAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            if ($request->request->has('pageID') && !empty($request->request->get('pageID')) && $request->request->has('subscriberID') && !empty($request->request->get('subscriberID'))) {
                $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id' => $request->request->get('pageID')]);
                if ($page instanceof Page) {
                    $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id' => $page->getPageId(), 'subscriber_id' => $request->request->get('subscriberID')]);
                    if ($subscriber instanceof Subscribers) {
                        if ($request->request->has('flowID') && !empty($request->request->get('flowID'))
                            && $request->request->has('flowItemUuid') && !empty($request->request->get('flowItemUuid'))
                            && $request->request->has('itemUuid') && !empty($request->request->get('itemUuid'))
                            && $request->request->has('response') && !empty($request->request->get('response'))
                        ) {
                            $flow = $em->getRepository('AppBundle:Flows')->findOneBy(['page_id' => $page->getPageId(), 'id' => $request->request->get('flowID')]);
                            if ($flow instanceof Flows) {
                                $flowItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow' => $flow, 'uuid' => $request->request->get('flowItemUuid')]);
                                if ($flowItem instanceof FlowItems) {
                                    $itemUUID = $request->request->get('itemUuid');
                                    $userInputDelay = $em->getRepository("AppBundle:UserInputDelay")->findOneBy(
                                        [
                                            'page_id' => $page->getPageId(),
                                            'subscriber' => $subscriber,
                                            'flowItem' => $flowItem,
                                            'itemUuid' => $itemUUID
                                        ]
                                    );
                                    if ($userInputDelay instanceof UserInputDelay) {
                                        $em->remove($userInputDelay);
                                        $em->flush();
                                    }
                                    //find user input item
                                    $userInputItem = null;
                                    $userInputKey = null;
                                    $nextItems = [];
                                    $items = $flowItem->getItems();
                                    foreach ($items as $key => $item) {
                                        if (isset($item['uuid']) && $item['uuid'] == $itemUUID) {
                                            $userInputItem = $item;
                                            $userInputKey = $key;
                                        } elseif (!is_null($userInputKey) && !is_null($userInputItem)) {
                                            $nextItems[] = $item;
                                        }
                                    }

                                    if (!empty($userInputItem) && is_array($userInputItem)) {
                                        if (array_key_exists('type', $userInputItem) && $userInputItem['type'] == "user_input" && array_key_exists('params', $userInputItem)
                                            && !empty($userInputItem['params']) && array_key_exists('description', $userInputItem['params'])
                                            && !empty($userInputItem['params']['description'])
                                        ) {
                                            //find user response
                                            $responseUser = $request->request->get('response');

                                            //save user response
                                            $userInputResponse = new UserInputResponse(
                                                $page->getPageId(),
                                                $subscriber,
                                                $flowItem,
                                                $userInputItem['params']['description'],
                                                $responseUser,
                                                array_key_exists('replyType', $userInputItem['params']['keyboardInput']) ? $userInputItem['params']['keyboardInput']['replyType'] : 0
                                            );
                                            $em->persist($userInputResponse);
                                            $em->flush();
                                            //save user response to custom_field
                                            if (array_key_exists('keyboardInput', $userInputItem['params'])
                                                && array_key_exists('id', $userInputItem['params']['keyboardInput'])
                                                && !empty($userInputItem['params']['keyboardInput']['id'])
                                            ) {
                                                $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['page_id' => $page->getPageId(), 'id' => $userInputItem['params']['keyboardInput']['id']]);
                                                if ($customField instanceof CustomFields) {
                                                    $subscriberCustomField = $em->getRepository("AppBundle:SubscribersCustomFields")->findOneBy(['subscriber' => $subscriber, 'customField' => $customField]
                                                    );
                                                    if ($subscriberCustomField instanceof SubscribersCustomFields) {
                                                        $subscriberCustomField->setValue($responseUser);
                                                    }
                                                    else {
                                                        $subscriberCustomField = new SubscribersCustomFields($subscriber, $customField, $responseUser);
                                                    }
                                                    $em->persist($subscriberCustomField);
                                                    $em->flush();

                                                    //ZAPIER TRIGGER
                                                    ZapierHelper::triggerSetCustomField($em, $page, $subscriberCustomField);
                                                }
                                            }
                                            //SEND NEXT STEP
                                            if (array_key_exists('buttons', $userInputItem['params']) && !empty($userInputItem['params']['buttons'])) {
                                                $nextStepID = null;
                                                foreach ($userInputItem['params']['buttons'] as $button) {
                                                    if (array_key_exists('next_step', $button) && !empty($button['next_step'])) {
                                                        $nextStepID = $button['next_step'];
                                                    }
                                                }
                                                if (!is_null($nextStepID)) {
                                                    $nextFlowItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow' => $flowItem->getFlow(), 'uuid' => $nextStepID]);
                                                    if ($nextFlowItem instanceof FlowItems) {
                                                        $flowsItemSend = new FlowsItem($em, $nextFlowItem, $subscriber);
                                                        $flowsItemSend->send();
                                                    }
                                                }
                                            }
                                            //SEND NEXT ITEMS
                                            if (!empty($nextItems)) {
                                                $newFlowItem = new FlowItems(
                                                    $flowItem->getFlow(),
                                                    $flowItem->getUuid(),
                                                    'user-input-send',
                                                    FlowItems::TYPE_SEND_MESSAGE,
                                                    $nextItems,
                                                    $flowItem->getQuickReply(),
                                                    false,
                                                    $flowItem->getNextStep(),
                                                    100,
                                                    100,
                                                    []
                                                );
                                                $flowsItemSend = new FlowsItem($em, $newFlowItem, $subscriber);
                                                $flowsItemSend->send();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        catch (\Exception $e){}

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Post("/action/send_notify_admin")
     * @SWG\Post(path="/v2/fetch/action/send_notify_admin",
     *   tags={"FETCH"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function sendNotifyAdminAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        if(
            $request->request->has('pageID') && !empty($request->request->get('pageID'))
            && $request->request->has('adminIDs') && !empty($request->request->get('adminIDs'))
            && $request->request->has('subscriberIDs') && !empty($request->request->get('subscriberIDs'))
            && $request->request->has('textNotify') && !empty($request->request->get('textNotify'))
        ){
            $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$request->request->get('pageID')]);
            if($page instanceof Page){
                $admins = [];
                foreach ($request->request->get('adminIDs') as $adminID){
                    $admin = $em->getRepository("AppBundle:User")->find($adminID);
                    if($admin instanceof User){
                        $notification = $em->getRepository("AppBundle:Notification")->findOneBy(['page_id'=>$page->getPageId(), 'user'=>$admin, 'status'=>true]);
                        if($notification instanceof Notification){
                            $admins[] = [
                                'email' => $notification->getEmail(),
                                'firstName' => $notification->getUser()->getFirstName()
                            ];
                        }
                    }
                }
                if(!empty($admins)){
                    foreach ($admins as $admin){
                        if(isset($admin['email']) && isset($admin['firstName'])){
                            foreach ($request->request->get('subscriberIDs') as $subscriberID) {
                                $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id' => $page->getPageId(), 'id' => $subscriberID]);
                                if ($subscriber instanceof Subscribers) {
                                    $message = (new \Swift_Message('ChatBo neue Benachrichtigung'))
                                        ->setFrom('news@chatbo.de', 'ChatBo')
                                        ->setTo($admin['email'])
                                        ->setBody(
                                            $this->renderView('emails/new_notify.html.twig', [
                                                'page' => $page,
                                                'textNotify' => $request->request->get('textNotify'),
                                                'firstName' => $admin['firstName'],
                                                'link_chat' => "https://app.chatbo.de/".$page->getPageId()."/chat?id=".$subscriber->getSubscriberId()
                                            ]),
                                            'text/html'
                                        );
                                    try {
                                        $this->get('mailer')->send($message);
                                    }
                                    catch (\Swift_TransportException $e) {}
                                }
                            }
                        }
                    }
                }
            }
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \MailchimpAPI\MailchimpException
     *
     * @Rest\Get("/addMailChimpId")
     * @SWG\Get(path="/v2/fetch/addMailChimpId",
     *   tags={"FETCH"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function addMailChimpIdAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $mailchimp = new Mailchimp($this->container->getParameter('mailchimp_api_key'));
        $members = [];
        do {
            $result = $mailchimp->lists('681cd479b9')->members()->get(["count" => "1000","offset"=>count($members)])->deserialize(true);
            if(isset($result['members']) && !empty($result['members'])){
                $members = array_merge($members, $result['members']);
            }
            $totalCount = $result['total_items'];
        } while ($totalCount > count($members));

        foreach ($members as $member){
            if(isset($member['id']) && !empty($member['id']) && isset($member['email_address']) && !empty($member['email_address'])){
                $user = $em->getRepository(User::class)->findOneBy(['email'=>$member['email_address']]);
                if($user instanceof User){
                    $user->setQuentnId($member['id']);
                    $em->persist($user);
                }
            }
        }
        $em->flush();

        $users = $em->getRepository(User::class)->getAllQuntnIdIsNull();
        if(!empty($users)){
            foreach ($users as $user){
                if($user instanceof User){
                    $tags = [];
                    if(empty($user->getOrderId())){
                        $tags = ['ChatBo Starter'];
                    }
                    $post_params = [
                        "email_address" => $user->getEmail(),
                        "status" => "subscribed",
                        "email_type" => "html",
                        "merge_fields" => [
                            "FNAME" => $user->getFirstName(),
                            "LNAME" => $user->getLastName()
                        ],
                        "tags" => $tags
                    ];
                    $result = $mailchimp
                        ->lists($this->container->getParameter('mailchimp_list_id'))
                        ->members()
                        ->post($post_params)
                        ->deserialize(true);

                    if(isset($result['id']) && !empty($result['id'])){
                        $user->setQuentnId($result['id']);
                        $em->persist($user);
                    }
                    elseif (isset($result['title']) && $result['title'] == 'Member Exists'){
                        $findMembers = $mailchimp->searchMembers()->get([
                            'query'=> $user->getEmail()
                        ])->deserialize(true);
                        if(isset($findMembers['exact_matches']['members'][0]['id']) && !empty($findMembers['exact_matches']['members'][0]['id'])){
                            $user->setQuentnId($findMembers['exact_matches']['members'][0]['id']);
                            $em->persist($user);
                        }
                        elseif(isset($findMembers['full_search']['members'][0]['id']) && !empty($findMembers['full_search']['members'][0]['id'])){
                            $user->setQuentnId($findMembers['full_search']['members'][0]['id']);
                            $em->persist($user);
                        }
                    }
                }
            }
            $em->flush();
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }
}
