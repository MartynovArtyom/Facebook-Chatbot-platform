<?php
/**
 * Created by PhpStorm.
 * Date: 23.07.18
 * Time: 17:02
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\InviteLinkAdmin;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Page;
use AppBundle\Entity\PageAdmins;
use AppBundle\Entity\User;
use AppBundle\Helper\PageHelper;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class PageAdminsController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page")
 */
class PageAdminsController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/{page_id}/admins/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/admins/",
     *   tags={"PAGE ADMINS"},
     *   security=false,
     *   summary="GET PAGE ADMINS BY PAGE_ID",
     *   description="The method for getting page admins by page_id",
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
     *     description="Success. NOT CAN REMOVE OWNER, ALWAYS YOU CAN REMOVE YOURSELF, ADMIN CAN REMOVE OTHER MEMBER",
     *     @SWG\Schema(
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="avatar",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="type",
     *                  type="integer",
     *                  description="1 = Owner, 2 = Your(can delete yourself), 3=Nothing"
     *              ),
     *              @SWG\Property(
     *                  property="role",
     *                  type="integer",
     *                  description="1=Admin, 2=Editor, 3=Live Chat Agent, 4=Viewer"
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
    public function getAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $admins = [];
            $admins[] = [
                'id' => $page->getUser()->getId(),
                'firstName' => $page->getUser()->getFirstName(),
                'lastName' => $page->getUser()->getLastName(),
                'avatar' => $page->getUser()->getAvatar(),
                'type' => 1,
                'role' => 1
            ];
            $pageAdmins = $em->getRepository("AppBundle:PageAdmins")->findBy(['page_id'=>$page->getPageId()]);
            if(!empty($pageAdmins)){
                foreach ($pageAdmins as $admin){
                    if($admin instanceof PageAdmins){
                        $type = 3;
                        if($admin->getUser()->getId() == $this->getUser()->getId()){
                            $type = 2;
                        }
                        $admins[] = [
                            'id' => $admin->getUser()->getId(),
                            'firstName' => $admin->getUser()->getFirstName(),
                            'lastName' => $admin->getUser()->getLastName(),
                            'avatar' => $admin->getUser()->getAvatar(),
                            'type' => $type,
                            'role' => $admin->getRole()
                        ];
                    }
                }
            }
            $view = $this->view($admins, Response::HTTP_OK);
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
     * @return Response
     *
     * @Rest\Post("/admins/")
     * @SWG\Post(path="/v2/page/admins/",
     *   tags={"PAGE ADMINS"},
     *   security=false,
     *   summary="CREATE PAGE ADMINS BY TOKEN",
     *   description="The method for creating page admins by token",
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
     *              property="token",
     *              type="string",
     *              description="required"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="page_id",
     *              type="string",
     *              description="page_id",
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
     *                  property="type",
     *                  type="string",
     *                  description="invalid_link or version"
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
    public function createAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if($this->getUser()->getProduct() instanceof DigistoreProduct && $this->getUser()->getProduct()->getAdmins() == true){
            if($request->request->has('token') && !empty($request->request->get('token'))){
                $invite = $em->getRepository("AppBundle:InviteLinkAdmin")->findOneBy(['token'=>$request->request->get('token')]);

                if(!$invite instanceof InviteLinkAdmin || $invite->getTimeExpired() <= time()){
                    $view = $this->view([
                        'error'=>[
                            'message' => "Invalid link",
                            'type' => 'invalid_link'
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
                $page_id = $invite->getPageId();
                $checkOwner = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$invite->getPageId(), 'user'=>$this->getUser()]);
                if($checkOwner instanceof Page){
                    $em->remove($invite);
                    $em->flush();
                }
                else{
                    $pageAdmin = $em->getRepository("AppBundle:PageAdmins")->findOneBy(['user'=>$this->getUser(), 'page_id'=>$page_id]);
                    if($pageAdmin instanceof PageAdmins){
                        $pageAdmin->setRole($invite->getRole());
                    }
                    else{
                        $pageAdmin = new PageAdmins($page_id, $this->getUser(), $invite->getRole());
                    }
                    $em->persist($pageAdmin);
                    $em->remove($invite);
                    $em->flush();

                    $notification = $em->getRepository("AppBundle:Notification")->findOneBy(['page_id'=>$page_id, 'user'=>$this->getUser()]);
                    if(!$notification instanceof Notification){
                        $notification = new Notification($page_id, $this->getUser(), $this->getUser()->getEmail());
                        $em->persist($notification);
                        $em->flush();
                    }
                }

                $view = $this->view([
                    'page_id' => $page_id
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Invalid link",
                        'type' => 'invalid_link'
                    ]
                ], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view([
                'error'=>[
                    'message'=>"Diese Funktion ist für deinen Plan nicht freigeschaltet",
                    'type' => 'version'
                ]
            ], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     * @throws \Exception
     *
     * @Rest\Post("/{page_id}/admins/token", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/admins/token",
     *   tags={"PAGE ADMINS"},
     *   security=false,
     *   summary="CREATE TOKEN FOR INVITE LINK BY PAGE_ID",
     *   description="The method for getting token for invite link by page_id",
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
     *              property="role",
     *              type="integer",
     *              description="required, 1=Admin, 2=Editor, 3=Live Chat Agent, 4=Viewer"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="token",
     *              type="string"
     *          ),
     *      )
     *   ),
     *   @SWG\Response(
     *      response=400,
     *      description="Bad Request",
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
    public function createTokenAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('role') && in_array($request->request->getInt('role',0),[1,2,3,4])){
                $token = bin2hex(random_bytes(16));
                $timeExpire = new \DateTime('+1 days');
                $inviteAdmin = new InviteLinkAdmin($page->getPageId(), $token, $timeExpire->getTimestamp(), $request->request->getInt('role',0));
                $em->persist($inviteAdmin);
                $em->flush();
                $view = $this->view(['token'=>$token], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error' => [
                        'message'=>"Select Role"
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
     * @param $user_id
     * @return Response
     *
     * @Rest\Patch("/{page_id}/admins/{user_id}", requirements={"page_id"="\d+","user_id"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/admins/{user_id}",
     *   tags={"PAGE ADMINS"},
     *   security=false,
     *   summary="UPDATE ADMIN FOR PAGE BY PAGE_ID AND USER_ID",
     *   description="The method for updating admin for page by page_id and user_id",
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
     *      name="user_id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="user_id"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="role",
     *              type="integer",
     *              description="required, 1=Admin, 2=Editor, 3=Live Chat Agent, 4=Viewer"
     *          ),
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
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
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="Not found",
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
    public function updateAction(Request $request, $page_id, $user_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $user = $em->getRepository("AppBundle:User")->find($user_id);
            if($user instanceof User){
                $pageAdmin = $em->getRepository("AppBundle:PageAdmins")->findOneBy(['user'=>$user, 'page_id'=>$page->getPageId()]);
                if($pageAdmin instanceof PageAdmins){
                    if($request->request->has('role') && in_array($request->request->getInt('role',0),[1,2,3,4])){
                        if($this->getUser()->getId() == $user->getId()){
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Can not update yourself"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        elseif ($page->getUser()->getId() == $user->getId()){
                            $view = $this->view([
                                'error'=>[
                                    'message'=>"Can not update owner"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                        else{
                            $pageAdmin->setRole($request->request->getInt('role',0));
                            $em->persist($pageAdmin);
                            $em->flush();

                            $view = $this->view([], Response::HTTP_OK);
                            return $this->handleView($view);
                        }
                    }
                    else{
                        $view = $this->view([
                            'error'=>[
                                'message'=>"Select Role"
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                        return $this->handleView($view);
                    }
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"User is not admin or owner of this page"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"User not found"
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
     * @param $user_id
     * @return Response
     *
     * @Rest\Delete("/{page_id}/admins/{user_id}", requirements={"page_id"="\d+","user_id"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/admins/{user_id}",
     *   tags={"PAGE ADMINS"},
     *   security=false,
     *   summary="REMOVE ADMIN FOR PAGE BY PAGE_ID AND USER_ID",
     *   description="The method for removing admin for page by page_id and user_id",
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
     *      name="user_id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      default="",
     *      description="user_id"
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
     *   ),
     *   @SWG\Response(
     *      response=404,
     *      description="Not found",
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
    public function removeAction(Request $request, $page_id, $user_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $user = $em->getRepository("AppBundle:User")->find($user_id);
            if($user instanceof User){
                $pageAdmin = $em->getRepository("AppBundle:PageAdmins")->findOneBy(['user'=>$user, 'page_id'=>$page->getPageId()]);
                if($pageAdmin instanceof PageAdmins){
                    $notification = $em->getRepository("AppBundle:Notification")->findOneBy(['page_id'=>$page->getPageId(), 'user'=>$user]);
                    if($notification instanceof Notification){
                        $em->remove($notification);
                    }
                    $em->remove($pageAdmin);
                    $em->flush();

                    $view = $this->view([], Response::HTTP_NO_CONTENT);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"User is not admin or owner of this page"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"User not found"
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

}
