<?php


namespace AppBundle\Controller\Api\Admin;


use AppBundle\Entity\DigistoreProduct;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class ProductController
 * @package AppBundle\Controller\Api\Admin
 *
 * @Rest\Route("/product")
 */
class ProductController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/v2/admin/product/",
     *   tags={"ADMIN PRODUCT"},
     *   security=false,
     *   summary="GET PRODUCTs FOR ADMIN",
     *   description="The method for getting product for admin",
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
    public function listAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $products = $em->getRepository("AppBundle:DigistoreProduct")->findAll();
        $result = [];
        foreach ($products as $product){
            if($product instanceof DigistoreProduct){
                $result[] = $product->toArray();
            }
        }
        $view = $this->view($result, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Post("/")
     * @SWG\Post(path="/v2/admin/product/",
     *   tags={"ADMIN PRODUCT"},
     *   security=false,
     *   summary="CREATE PRODUCT FOR ADMIN",
     *   description="The method for create product for admin",
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
     *              type="integer",
     *              property="productId",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="name",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="label",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitSubscribers",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitCompany",
     *              description="if unlimited than NULL"
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitSequences",
     *              description="if unlimited than NULL"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="comments",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="downloadPsid",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="zapier",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="admins",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="quentnUrl",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitedQuentn",
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="integer",
     *              property="id",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="productId",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="name",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="label",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitSubscribers",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitCompany",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitSequences",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="comments",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="downloadPsid",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="zapier",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="admins",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="quentnUrl",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitedQuentn",
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
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="message",
     *                      type="string",
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
    public function createAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $product = new DigistoreProduct($request->request->all());
        $errors = $this->get('validator')->validate($product, null, array('product'));
        if(count($errors) === 0){
            $em->persist($product);
            $em->flush();

            $view = $this->view($product->toArray(), Response::HTTP_CREATED);
            return $this->handleView($view);

        }
        else {
            $error_description = [];
            foreach ($errors as $er) {
                $error_description[]['message'] = $er->getMessage();
            }
            $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Get("/{id}", requirements={"id"="\d+"})
     * @SWG\Get(path="/v2/admin/product/{id}",
     *   tags={"ADMIN PRODUCT"},
     *   security=false,
     *   summary="GET PRODUCT BY ID FOR ADMIN",
     *   description="The method for get product by id for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      description="id"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="integer",
     *              property="id",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="productId",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="name",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="label",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitSubscribers",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitCompany",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitSequences",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="comments",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="downloadPsid",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="zapier",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="admins",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="quentnUrl",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitedQuentn",
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
    public function viewAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository("AppBundle:DigistoreProduct")->find($id);
        if($product instanceof DigistoreProduct){

            $view = $this->view($product->toArray(), Response::HTTP_OK);
            return $this->handleView($view);
        }
        else{
            $view = $this->view(['error'=>[
                'message' => 'Product Not Found'
            ]], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Put("/{id}", requirements={"id"="\d+"})
     * @SWG\Put(path="/v2/admin/product/{id}",
     *   tags={"ADMIN PRODUCT"},
     *   security=false,
     *   summary="EDIT PRODUCT BY ID FOR ADMIN",
     *   description="The method for edit product by id for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      description="id"
     *   ),
     *   @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="integer",
     *              property="productId",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="name",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="label",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitSubscribers",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitCompany",
     *              description="if unlimited than NULL"
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitSequences",
     *              description="if unlimited than NULL"
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="comments",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="downloadPsid",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="zapier",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="admins",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="quentnUrl",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitedQuentn",
     *          )
     *      ),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="integer",
     *              property="id",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="productId",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="name",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="label",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitSubscribers",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitCompany",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitSequences",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="comments",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="downloadPsid",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="zapier",
     *          ),
     *          @SWG\Property(
     *              type="boolean",
     *              property="admins",
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="quentnUrl",
     *          ),
     *          @SWG\Property(
     *              type="integer",
     *              property="limitedQuentn",
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
     *              type="array",
     *              @SWG\Items(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="message",
     *                      type="string",
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
    public function editAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository("AppBundle:DigistoreProduct")->find($id);
        if($product instanceof DigistoreProduct){
            $product->update($request->request->all());
            $errors = $this->get('validator')->validate($product, null, array('product'));
            if(count($errors) === 0){
                $em->persist($product);
                $em->flush();

                $view = $this->view($product->toArray(), Response::HTTP_OK);
                return $this->handleView($view);

            }
            else {
                $error_description = [];
                foreach ($errors as $er) {
                    $error_description[]['message'] = $er->getMessage();
                }
                $view = $this->view(['error'=>$error_description], Response::HTTP_BAD_REQUEST);
                return $this->handleView($view);
            }
        }
        else{
            $view = $this->view(['error'=>[
                'message' => 'Product Not Found'
            ]], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Delete("/{id}", requirements={"id"="\d+"})
     * @SWG\Delete(path="/v2/admin/product/{id}",
     *   tags={"ADMIN PRODUCT"},
     *   security=false,
     *   summary="REMOVE PRODUCT BY ID FOR ADMIN",
     *   description="The method for remove product by id for admin",
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
     *      name="id",
     *      in="path",
     *      required=true,
     *      type="integer",
     *      description="id"
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="Success."
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
    public function removeAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository("AppBundle:DigistoreProduct")->find($id);
        if($product instanceof DigistoreProduct){
            $em->remove($product);
            $em->flush();

            $view = $this->view([], Response::HTTP_NO_CONTENT);
            return $this->handleView($view);
        }
        else{
            $view = $this->view(['error'=>[
                'message' => 'Product Not Found'
            ]], Response::HTTP_NOT_FOUND);
            return $this->handleView($view);
        }
    }
}
