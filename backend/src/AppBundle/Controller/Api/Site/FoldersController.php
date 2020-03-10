<?php
/**
 * Created by PhpStorm.
 * Date: 01.11.18
 * Time: 12:43
 */

namespace AppBundle\Controller\Api\Site;

use AppBundle\Entity\Folders;
use AppBundle\Entity\Page;
use AppBundle\Helper\PageHelper;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class FoldersController
 * @package AppBundle\Controller\Api\Site
 *
 * @Rest\Route("/page/{page_id}/folders")
 */
class FoldersController extends FOSRestController
{
    /**
     * @param Request $request
     * @param $page_id
     * @return Response
     *
     * @Rest\Get("/", requirements={"page_id"="\d+"})
     * @SWG\Get(path="/v2/page/{page_id}/folders/",
     *   tags={"FOLDERS"},
     *   security=false,
     *   summary="GET FOLDERS BY PAGE_ID",
     *   description="The method for getting folders by page_id",
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
     *              property="id",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="children",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="children",
     *                      type="array",
     *                      @SWG\Items(
     *                          type="object"
     *                      )
     *                  )
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
            $parentFolders = $em->getRepository("AppBundle:Folders")->findBy(['page_id'=>$page->getPageId(),'parent'=>NULL]);
            $folders = [];
            if(!empty($parentFolders)){
                foreach ($parentFolders as $parentFolder){
                    if($parentFolder instanceof Folders){
                        $folders[] = [
                            'id' => $parentFolder->getId(),
                            'name' => $parentFolder->getName(),
                            'children' => self::findChildrenFolder($em, $parentFolder)
                        ];
                    }
                }
            }

            $view = $this->view($folders, Response::HTTP_OK);
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
     *
     * @Rest\Post("/", requirements={"page_id"="\d+"})
     * @SWG\Post(path="/v2/page/{page_id}/folders/",
     *   tags={"FOLDERS"},
     *   security=false,
     *   summary="CREATE FOLDERS BY PAGE_ID",
     *   description="The method for create folders by page_id",
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
     *              property="name",
     *              type="string",
     *              example="folder name"
     *          ),
     *          @SWG\Property(
     *              property="parent",
     *              type="integer",
     *              description="if root = null, else folderID"
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="new_folder",
     *              type="object",
     *              @SWG\Property(
     *                  property="id",
     *                  type="integer",
     *              ),
     *              @SWG\Property(
     *                  property="name",
     *                  type="string",
     *              ),
     *              @SWG\Property(
     *                  property="children",
     *                  type="array",
     *                  @SWG\Items(
     *                      type="object"
     *                  )
     *              )
     *          ),
     *          @SWG\Property(
     *              property="folders",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="children",
     *                      type="array",
     *                      @SWG\Items(
     *                          type="object"
     *                      )
     *                  )
     *              )
     *          )
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
    public function createAction(Request $request, $page_id){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            if($request->request->has('name') && !empty($request->request->get('name'))){
                $parent=NULL;
                if($request->request->has('parent')){
                    if(!is_null($request->request->get('parent'))){
                        $parentFolder = $em->getRepository('AppBundle:Folders')->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$request->request->get('parent')]);
                        if($parentFolder instanceof Folders){
                            $parent = $parentFolder->getId();
                        }
                        else{
                            $view = $this->view([
                                'error'=>[
                                   'message'=>"Parent Folder Not found"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                }
                $folder = new Folders($page->getPageId(), $request->request->get('name'),$parent);
                $em->persist($folder);
                $em->flush();

                $parentFoldersList = $em->getRepository("AppBundle:Folders")->findBy(['page_id'=>$page->getPageId(),'parent'=>NULL]);
                $folders = [];
                if(!empty($parentFoldersList)){
                    foreach ($parentFoldersList as $parentFolderList){
                        if($parentFolderList instanceof Folders){
                            $folders[] = [
                                'id' => $parentFolderList->getId(),
                                'name' => $parentFolderList->getName(),
                                'children' => self::findChildrenFolder($em, $parentFolderList)
                            ];
                        }
                    }
                }

                $view = $this->view([
                    'new_folder' => [
                        'id' => $folder->getId(),
                        'name' => $folder->getName(),
                        'children' => []
                    ],
                    'folders' => $folders
                ], Response::HTTP_OK);
                return $this->handleView($view);
            }
            else{
                $view = $this->view([
                    'error'=>[
                       'message'=>"name is required and should be not empty"
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
     * @param $folderID
     * @return Response
     *
     * @Rest\Patch("/{folderID}", requirements={"page_id"="\d+", "folderID"="\d+"})
     * @SWG\Patch(path="/v2/page/{page_id}/folders/{folderID}",
     *   tags={"FOLDERS"},
     *   security=false,
     *   summary="UPDATE FOLDER BY ID BY PAGE_ID",
     *   description="The method for update folder by id by page_id",
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
     *      name="folderID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="folderID"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="name",
     *              type="string",
     *              example="folder name"
     *          ),
     *          @SWG\Property(
     *              property="parent",
     *              type="integer",
     *              description="if root = null, else folderID"
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="folders",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="children",
     *                      type="array",
     *                      @SWG\Items(
     *                          type="object"
     *                      )
     *                  )
     *              )
     *          )
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
    public function updateByIdAction(Request $request, $page_id, $folderID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $folder = $em->getRepository("AppBundle:Folders")->findOneBy(['page_id'=>$page->getPageId(),'id'=>$folderID]);
            if($folder instanceof Folders) {
                //EDIT NAME
                if ($request->request->has('name') || $request->request->has('parent')) {
                    if ($request->request->has('name')) {
                        if (!empty($request->request->get('name'))) {
                            $folder->setName($request->request->get('name'));
                            $em->persist($folder);
                            $em->flush();
                        } else {
                            $view = $this->view([
                                'error' => [
                                    'message' => "name should be not empty"
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                            return $this->handleView($view);
                        }
                    }
                    //EDIT PARENT
                    if ($request->request->has('parent')) {
                        $parent = NULL;
                        if (!is_null($request->request->get('parent'))) {
                            $parentFolder = $em->getRepository("AppBundle:Folders")->findOneBy(['page_id' => $page->getPageId(), 'id' => $request->request->get('parent')]);
                            if ($parentFolder instanceof Folders) {
                                $parent = $parentFolder->getId();
                            } else {
                                $view = $this->view([
                                    'error' => [
                                        'message' => "Parent Folder Not Found"
                                    ]
                                ], Response::HTTP_BAD_REQUEST);
                                return $this->handleView($view);
                            }
                        }
                        $folder->setParent($parent);
                        $em->persist($folder);
                        $em->flush();
                    }

                    $parentFoldersList = $em->getRepository("AppBundle:Folders")->findBy(['page_id'=>$page->getPageId(),'parent'=>NULL]);
                    $folders = [];
                    if(!empty($parentFoldersList)){
                        foreach ($parentFoldersList as $parentFolderList){
                            if($parentFolderList instanceof Folders){
                                $folders[] = [
                                    'id' => $parentFolderList->getId(),
                                    'name' => $parentFolderList->getName(),
                                    'children' => self::findChildrenFolder($em, $parentFolderList)
                                ];
                            }
                        }
                    }

                    $view = $this->view([
                        'folders' => $folders
                    ], Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"name or parent is required"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Folder Not Found"
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
     * @param $folderID
     * @return Response
     *
     * @Rest\Delete("/{folderID}", requirements={"page_id"="\d+", "folderID"="\d+"})
     * @SWG\Delete(path="/v2/page/{page_id}/folders/{folderID}",
     *   tags={"FOLDERS"},
     *   security=false,
     *   summary="DELETE FOLDER BY ID BY PAGE_ID",
     *   description="The method for remove folder by id by page_id",
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
     *      name="folderID",
     *      in="path",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="folderID"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              property="folders",
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="id",
     *                      type="integer",
     *                  ),
     *                  @SWG\Property(
     *                      property="name",
     *                      type="string",
     *                  ),
     *                  @SWG\Property(
     *                      property="children",
     *                      type="array",
     *                      @SWG\Items(
     *                          type="object"
     *                      )
     *                  )
     *              )
     *          )
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
     *      description="Not Found",
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
    public function removeByIdAction(Request $request, $page_id, $folderID){
        $em = $this->getDoctrine()->getManager();
        $page = PageHelper::checkAccessPage($em, $page_id, $this->getUser());
        if($page instanceof Page){
            $folder = $em->getRepository("AppBundle:Folders")->findOneBy(['page_id'=>$page->getPageId(),'id'=>$folderID]);
            if($folder instanceof Folders){
                $childFolders = $em->getRepository('AppBundle:Folders')->findBy(['parent'=>$folder->getId()]);
                if(empty($childFolders)){
                    $em->remove($folder);
                    $em->flush();

                    $parentFoldersList = $em->getRepository("AppBundle:Folders")->findBy(['page_id'=>$page->getPageId(),'parent'=>NULL]);
                    $folders = [];
                    if(!empty($parentFoldersList)){
                        foreach ($parentFoldersList as $parentFolderList){
                            if($parentFolderList instanceof Folders){
                                $folders[] = [
                                    'id' => $parentFolderList->getId(),
                                    'name' => $parentFolderList->getName(),
                                    'children' => self::findChildrenFolder($em, $parentFolderList)
                                ];
                            }
                        }
                    }

                    $view = $this->view([
                        'folders' => $folders
                    ], Response::HTTP_OK);
                    return $this->handleView($view);
                }
                else{
                    $view = $this->view([
                        'error'=>[
                            'message'=>"Folder with subfolders can't be removed"
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                    return $this->handleView($view);
                }
            }
            else{
                $view = $this->view([
                    'error'=>[
                        'message'=>"Folder Not Found"
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
     * @param EntityManager $em
     * @param Folders $folder
     * @return array
     */
    public static function findChildrenFolder(EntityManager $em, Folders $folder){
        $childFoldersResult = $em->getRepository('AppBundle:Folders')->findBy(['parent'=>$folder->getId()]);
        $childFolders = [];
        if(!empty($childFoldersResult)){
            foreach ($childFoldersResult as $childFolder){
                if($childFolder instanceof Folders){
                    $childFolders[] = [
                        'id' => $childFolder->getId(),
                        'name' => $childFolder->getName(),
                        'children' => self::findChildrenFolder($em, $childFolder)
                    ];
                }
            }
        }

        return $childFolders;
    }
}