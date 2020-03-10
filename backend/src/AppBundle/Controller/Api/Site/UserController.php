<?php
/**
 * Created by PhpStorm.
 * Date: 23.07.18
 * Time: 14:23
 */

namespace AppBundle\Controller\Api\Site;


use AppBundle\Entity\Page;
use AppBundle\Entity\SaveImages;
use AppBundle\Entity\User;
use AppBundle\Helper\MyFbBotApp;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class UserController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/user")
 */
class UserController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/v2/user/",
     *   tags={"User"},
     *   security=false,
     *   summary="GET USER INFO",
     *   description="The method for getting user info",
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
     *   @SWG\Response(
     *      response=200,
     *      description="Success.",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="id",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="facebook_id",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="firstName",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="email",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="lastName",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="avatar",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="firstPopup",
     *              type="boolean",
     *          ),
     *          @SWG\Property(
     *              property="limitSubscribers",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="orderId",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="product",
     *              type="object",
     *              @SWG\Property(
     *                  type="integer",
     *                  property="id",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="productId",
     *              ),
     *              @SWG\Property(
     *                  type="string",
     *                  property="name",
     *              ),
     *              @SWG\Property(
     *                  type="string",
     *                  property="label",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="limitSubscribers",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="limitCompany",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="limitSequences",
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="comments",
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="downloadPsid",
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="zapier",
     *              ),
     *              @SWG\Property(
     *                  type="boolean",
     *                  property="admins",
     *              ),
     *              @SWG\Property(
     *                  type="string",
     *                  property="quentnUrl",
     *              ),
     *              @SWG\Property(
     *                  type="integer",
     *                  property="limitedQuentn",
     *              )
     *          ),
     *          @SWG\Property(
     *              property="quentnId",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="roles",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="enabled",
     *              type="boolean",
     *          ),
     *          @SWG\Property(
     *              property="created",
     *              type="datetime",
     *          ),
     *          @SWG\Property(
     *              property="lastLogin",
     *              type="datetime",
     *          ),
     *          @SWG\Property(
     *              property="trialEndDay",
     *              type="integer",
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
     *   )
     * )
     */
    public function infoAction(Request $request){
        $user = $this->getUser();

        $trialEndDay = 0;
        if($user->getTrialEnd() instanceof \DateTime){
            $now = new \DateTime();
            $now->setTime(0,0,0);
            $trialDay = new \DateTime($user->getTrialEnd()->format('Y-m-d'));
            $trialDay->setTime(0,0,0);
            $trialEndDay = $trialDay->diff($now)->d;
        }

        $view = $this->view([
            'id' => $user->getId(),
            'facebook_id' => $user->getFacebookId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'avatar' => $user->getAvatar(),
            'firstPopup' => $user->getFirstPopup(),
            'limitSubscribers' => $user->getLimitSubscribers(),
            'orderId' => $user->getOrderId(),
            'product' => $user->getProduct(),
            'quentnId' => $user->getQuentnId(),
            'roles' => (isset($user->getRoles()[0])) ? $user->getRoles()[0] : 'ROLE_USER',
            'enabled' => $user->isEnabled(),
            'created' => $user->getCreated(),
            'lastLogin' => $user->getLastLogin(),
            'trialEndDay' => $trialEndDay,

        ], Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Patch("/")
     * @SWG\Patch(path="/v2/user/",
     *   tags={"User"},
     *   security=false,
     *   summary="EDIT USER INFO",
     *   description="The method for edit user info",
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
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="boolean",
     *              property="firstPopup",
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *      response=204,
     *      description="Success."
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
     *   )
     * )
     */
    public function editAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if($request->request->has('firstPopup') && is_bool($request->request->get('firstPopup'))){
            $this->getUser()->setFirstPopup($request->request->get('firstPopup'));
            $em->persist($this->getUser());
            $em->flush();

            $view = $this->view([], Response::HTTP_NO_CONTENT);
            return $this->handleView($view);
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message' => 'firstPopup is required and should be boolean type'
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     *
     * @Rest\Get("/pages")
     * @SWG\Get(path="/v2/user/pages",
     *   tags={"User"},
     *   security=false,
     *   summary="GET ALL USER FB PAGES",
     *   description="The method for getting all user fb pages",
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
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(
     *                  property="access_token",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="id",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="category",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="is_webhooks_subscribed",
     *                  type="boolean",
     *                  description="if true page is already connect"
     *              ),
     *              @SWG\Property(
     *                  property="picture",
     *                  type="object",
     *                  @SWG\Property(
     *                      property="data",
     *                      type="object",
     *                      @SWG\Property(
     *                          property="url",
     *                          type="string",
     *                          description="avatar page"
     *                      ),
     *                  ),
     *              ),
     *              @SWG\Property(
     *                  property="tasks",
     *                  type="array",
     *                  @SWG\Items(type="string"),
     *                  description="permission page"
     *              ),
     *          )
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
     *              ),
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *              ),
     *              @SWG\Property(
     *                  property="subcode",
     *                  type="integer",
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
     *   )
     * )
     */
    public function getPagesFbAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $fb = new \Facebook\Facebook([
            'app_id' => $this->container->getParameter('facebook_id'),
            'app_secret' => $this->container->getParameter('facebook_secret'),
            'default_graph_version' => 'v3.3'
        ]);
        try {
            $response = $fb->get('/me/accounts?fields=access_token,id,category,name,is_webhooks_subscribed,tasks,picture{url}&limit=999', $this->getUser()->getFacebookAccessToken());
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            $view = $this->view([
                'error'=>[
                    'message'=>$e->getMessage(),
                    'code'=>$e->getCode(),
                    'subcode'=>$e->getSubErrorCode()
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            $view = $this->view([
                'error'=>[
                    'message'=>$e->getMessage(),
                    'code'=>$e->getCode()
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
        $decodeBody = $response->getDecodedBody();
        $pages = [];
        if(isset($decodeBody['data']) && !empty($decodeBody['data'])){
            foreach ($decodeBody['data'] as $key => $page) {
                if(isset($page['tasks']) && !empty($page['tasks'])){
                    if(
                        in_array('ANALYZE', $page['tasks']) && in_array('ADVERTISE', $page['tasks']) && in_array('MANAGE', $page['tasks'])
                        && in_array('MODERATE', $page['tasks']) && in_array('CREATE_CONTENT', $page['tasks'])
                    ){
                        $is_subscribed = (isset($page['is_webhooks_subscribed'])) ? $page['is_webhooks_subscribed'] : false;
                        if($is_subscribed == true && isset($page['id']) && isset($page['access_token'])){
                            $checkConnectPage = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$page['id']]);
                            if($checkConnectPage instanceof Page){
                                $checkConnectPage->setAccessToken($page['access_token']);
                                if(isset($page['name']) && !empty($page['name'])){
                                    $checkConnectPage->setTitle($page['name']);
                                }
                                $em->persist($checkConnectPage);
                                $em->flush();
                                //SAVE AVATAR
                                $saveImage = new SaveImages($page['picture']['data']['url'], "uploads/".$checkConnectPage->getPageId()."/avatar.jpg", $checkConnectPage->getId(), 'page');
                                $em->persist($saveImage);
                                $em->flush();
                            }
                            else{
                                $is_subscribed = false;
                            }
                        }
                        $pages[] = [
                            'access_token' => (isset($page['access_token'])) ? $page['access_token'] : null,
                            'id' => (isset($page['id'])) ? $page['id'] : null,
                            'category' => (isset($page['category'])) ? $page['category'] : null,
                            'name' => (isset($page['name'])) ? $page['name'] : null,
                            'is_webhooks_subscribed' => $is_subscribed,
                            'picture' => (isset($page['picture'])) ? $page['picture'] : null,
                            'tasks' => (isset($page['tasks'])) ? $page['tasks'] : null
                        ];

                    }
                }
            }
        }
        $view = $this->view($pages, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     *
     * @Rest\Delete("/")
     * @SWG\Delete(path="/v2/user/",
     *   tags={"User"},
     *   security=false,
     *   summary="REMOVE USER ON CHATBO",
     *   description="The method for removing user on chatbo",
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
     *   @SWG\Response(
     *     response=204,
     *     description="Success. User remove -> logout",
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
     *              ),
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *              ),
     *              @SWG\Property(
     *                  property="subcode",
     *                  type="integer",
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
     *   )
     * )
     */
    public function removeUserAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository("AppBundle:Page")->findBy(['user'=>$this->getUser()]);
        $fb = new \Facebook\Facebook([
            'app_id' => $this->container->getParameter('facebook_id'),
            'app_secret' => $this->container->getParameter('facebook_secret'),
            'default_graph_version' => 'v3.3'
        ]);
        foreach ($pages as $page){
            if($page instanceof Page){
                try{
                    $bot = new MyFbBotApp($page->getAccessToken());
                    $bot->deletePersistentMenu();
                } catch(\Exception $e){}

                if($page->getStatus() == true){
                    try {
                        $response = $fb->delete('/me/subscribed_apps?access_token='.$page->getAccessToken());
                    } catch(\Facebook\Exceptions\FacebookResponseException $e) {

                    } catch(\Facebook\Exceptions\FacebookSDKException $e) {

                    }
                }
                $em->getRepository("AppBundle:Page")->removeByPageId($page->getPageId());
            }
        }

        $em->remove($this->getUser());
        $em->flush();

        $view = $this->view([], Response::HTTP_NO_CONTENT);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     *
     * @Rest\Delete("/permissions")
     * @SWG\Delete(path="/v2/user/permissions",
     *   tags={"User"},
     *   security=false,
     *   summary="REMOVE USER PERMISSIONS ON CHATBO",
     *   description="The method for removing user permissions on chatbo",
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
     *   @SWG\Response(
     *     response=204,
     *     description="Success. User permissions remove -> logout",
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
     *   )
     * )
     */
    public function removePermissionsAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $pages = $em->getRepository("AppBundle:Page")->findBy(['user'=>$user]);
        $fb = new \Facebook\Facebook([
            'app_id' => $this->container->getParameter('facebook_id'),
            'app_secret' => $this->container->getParameter('facebook_secret'),
            'default_graph_version' => 'v3.3'
        ]);
        foreach ($pages as $page){
            if($page instanceof Page){
                if($page->getStatus() == true){
                    try {
                        $response = $fb->delete('/me/subscribed_apps?access_token='.$page->getAccessToken());
                        $res = $response->getDecodedBody();
                        if(isset($res['success']) && $res['success'] == true){
                            $page->setStatus(false);
                            $em->persist($page);
                            $em->flush();
                        }
                    } catch(\Facebook\Exceptions\FacebookResponseException $e) {

                    } catch(\Facebook\Exceptions\FacebookSDKException $e) {

                    }
                }

            }
        }

        try {
            $response = $fb->delete('/me/permissions?access_token='.$user->getFacebookAccessToken());
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {

        } catch(\Facebook\Exceptions\FacebookSDKException $e) {

        }

        $view = $this->view([], Response::HTTP_NO_CONTENT);
        return $this->handleView($view);
    }
}
