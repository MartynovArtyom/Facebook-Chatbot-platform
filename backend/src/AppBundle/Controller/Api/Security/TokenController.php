<?php
/**
 * Created by PhpStorm.
 * Date: 16.07.18
 * Time: 10:39
 */

namespace AppBundle\Controller\Api\Security;

use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\SaveImages;
use AppBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use MailchimpAPI\Mailchimp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * Class TokenController
 * @package AppBundle\Controller\Api\Security
 *
 * @Rest\Route("/token")
 */
class TokenController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     * @throws \Facebook\Exceptions\FacebookSDKException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/v2/token/",
     *   tags={"AUTH"},
     *   security=false,
     *   summary="GET TOKEN",
     *   description="The method for getting token",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="facebook_token",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="FB TOKEN"
     *   ),
     *   @SWG\Parameter(
     *      name="new-login",
     *      in="query",
     *      type="boolean",
     *      required=false,
     *      default=false
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="token",
     *              type="string",
     *              example="JWT TOKEN"
     *          ),
     *          @SWG\Property(
     *              property="fb_token",
     *              type="string",
     *              example="FB TOKEN"
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
     *      response=403,
     *      description="ACCESS DENIED",
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
    public function tokenAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');

        if($request->query->has('facebook_token') && !empty($request->query->get('facebook_token'))){
            $access_token = $request->query->get('facebook_token');
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>'facebook_token is required'
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }

        $fb = new \Facebook\Facebook([
            'app_id' => $this->container->getParameter('facebook_id'),
            'app_secret' => $this->container->getParameter('facebook_secret'),
            'default_graph_version' => 'v3.3'
        ]);

        try {
            $response = $fb->get('/me?fields=id,first_name,last_name,email,picture.type(large)', $access_token);
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
                    'code'=>$e->getCode(),
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }

        $userData = $response->getDecodedBody();
        if(!isset($userData['id']) || empty($userData['id'])){
            $view = $this->view([
                'error'=>[
                    'message'=>'Facebook-ID kann nicht abgerufen werden. Überprüfen Sie die Facebook-Einstellungen und -Berechtigungen'
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
        elseif(!isset($userData['first_name']) || empty($userData['first_name'])){
            $view = $this->view([
                'error'=>[
                    'message'=>'Vorname kann nicht abgerufen werden. Überprüfen Sie die Facebook-Einstellungen oder -Berechtigungen'
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
        elseif(!isset($userData['last_name']) || empty($userData['last_name'])){
            $view = $this->view([
                'error'=>[
                    'message'=>'Nachname kann nicht abgerufen werden. Überprüfen Sie die Facebook-Einstellungen oder Berechtigungen'
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
        elseif(!isset($userData['email']) || empty($userData['email'])
        ){
            $view = $this->view([
                'error'=>[
                    'message'=>'E-Mails kann nicht abgerufen werden. Überprüfen Sie die Facebook-Einstellungen oder Berechtigungen'
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }

        try {
            $params = [
                "grant_type" => "fb_exchange_token",
                "client_id" => $this->container->getParameter('facebook_id'),
                "client_secret" => $this->container->getParameter('facebook_secret'),
                "fb_exchange_token" => $access_token
            ];
            $responseLongToken = $fb->get("/oauth/access_token?".http_build_query($params),$access_token);
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
                    'code'=>$e->getCode(),
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }

        $longTokenData = $responseLongToken->getDecodedBody();
        if(isset($longTokenData['access_token']) && !empty($longTokenData['access_token'])){
            $access_token = $longTokenData['access_token'];
        }
        $em = $this->getDoctrine()->getManager();
        $user = $userManager->findUserBy(array( 'facebook_id' => $userData['id']));
        if (!$user instanceof User) {
            $user = $userManager->createUser();
            $user->setFacebookId($userData['id']);
            $user->setFacebookAccessToken($access_token);

            $user->setUsername($userData['email']);
            $user->setFirstName($userData['first_name']);
            $user->setLastName($userData['last_name']);
            $user->setEmail($userData['email']);
            $user->setEnabled(true);
            $product = $em->getRepository("AppBundle:DigistoreProduct")->find(12);
            if($product instanceof DigistoreProduct){
                $user->setProduct($product);
                $user->setLimitSubscribers($product->getLimitSubscribers());
            }
        }
        else{
            $user->setFacebookAccessToken($access_token);
            $user->setUsername($userData['email']);
            $user->setFirstName($userData['first_name']);
            $user->setLastName($userData['last_name']);
            $user->setEmail($userData['email']);
        }

        $user->setAvatar((isset($userData['picture']['data']['url'])) ? $userData['picture']['data']['url'] : null );
        $user->setLastLogin(new \DateTime());
        $userManager->updateUser($user);

        //SAVE AVATAR
        if(!empty($user->getAvatar())){
            try{
                $saveImage = new SaveImages($user->getAvatar(), "uploads/user/".$user->getId()."/avatar.jpg", $user->getId(), 'user');
                $em->persist($saveImage);
                $em->flush();
            }
            catch (\Exception $e){}
        }

        if(empty($user->getQuentnId())){
            try{
                $mailchimp = new Mailchimp($this->container->getParameter('mailchimp_api_key'));
                $tags = [];
                if(empty($user->getOrderId())){
                    $tags = ['ChatBo Starter'];
//                    if($request->query->has('new-login') && $request->query->get('new-login') == 'true'){
//                        $tags = ['ChatBo Starter New'];
//                    }
//                    else{
//                        $tags = ['ChatBo Starter'];
//                    }
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
                    $userManager->updateUser($user);
                }
                elseif (isset($result['title']) && $result['title'] == 'Member Exists'){
                    $findMembers = $mailchimp->searchMembers()->get([
                        'query'=> $user->getEmail()
                    ])->deserialize(true);
                    if(isset($findMembers['exact_matches']['members'][0]['id']) && !empty($findMembers['exact_matches']['members'][0]['id'])){
                        $user->setQuentnId($findMembers['exact_matches']['members'][0]['id']);
                        $userManager->updateUser($user);
                    }
                    elseif(isset($findMembers['full_search']['members'][0]['id']) && !empty($findMembers['full_search']['members'][0]['id'])){
                        $user->setQuentnId($findMembers['full_search']['members'][0]['id']);
                        $userManager->updateUser($user);
                    }
                }
            }
            catch (\Exception $e){}
        }

        if($user->isEnabled()){
            $JWTToken = $this->get('lexik_jwt_authentication.jwt_manager')->create($user);
            $view = $this->view([
                'token' => $JWTToken,
                'fb_token' => $access_token
            ], Response::HTTP_OK);
            return $this->handleView($view);
        }

        $view = $this->view([
            'error'=>[
                'message'=>'Sie sind gesperrt. Wenden Sie sich an den technischen Support, um das Problem zu lösen.'
            ]
        ], Response::HTTP_BAD_REQUEST);
        return $this->handleView($view);

    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/update")
     * @SWG\Get(path="/v2/token/update",
     *   tags={"AUTH"},
     *   security=false,
     *   summary="UPDATE JWT TOKEN",
     *   description="The method for updating jwt token",
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
     *          type="object",
     *          @SWG\Property(
     *              property="token",
     *              type="string",
     *              description="JWT TOKEN",
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
    public function tokenUpdateAction(Request $request){
        $JWTToken = $this->get('lexik_jwt_authentication.jwt_manager')->create($this->getUser());
        $view = $this->view([
            'token' => $JWTToken
        ], Response::HTTP_OK);
        return $this->handleView($view);
    }
}
