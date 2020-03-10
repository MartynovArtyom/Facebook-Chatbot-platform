<?php
/**
 * Created by PhpStorm.
 * Date: 15.11.18
 * Time: 10:39
 */

namespace AppBundle\Controller\Api\Admin;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class MainController
 * @package AppBundle\Controller\Api\Admin
 *
 */
class MainController extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/dashboard")
     * @SWG\Get(path="/v2/admin/dashboard",
     *   tags={"ADMIN MAIN"},
     *   security=false,
     *   summary="GET ADMIN DASHBOARD DATAs",
     *   description="The method for getting admin dashboard datas",
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
     *              property="userCount",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="pageCount",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="widgetCount",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="sequenceCount",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="flowCount",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="subscriberCount",
     *              type="integer",
     *          ),
     *          @SWG\Property(
     *              property="mapSubscribers",
     *              type="object"
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
    public function dashboardAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $userCount = $em->getRepository("AppBundle:User")->count([]);
        $pageCount = $em->getRepository("AppBundle:Page")->count([]);
        $widgetCount = $em->getRepository("AppBundle:Widget")->count([]);
        $sequenceCount = $em->getRepository("AppBundle:Sequences")->count([]);
        $flowCount = $em->getRepository("AppBundle:Flows")->count([]);
        $subscriberCount = $em->getRepository("AppBundle:Subscribers")->count([]);
        $mapSubscribers = $em->getRepository("AppBundle:Subscribers")->getSubscriberForMap();

        $view = $this->view([
            'userCount' => $userCount,
            'pageCount' => $pageCount,
            'widgetCount' => $widgetCount,
            'sequenceCount' => $sequenceCount,
            'flowCount' => $flowCount,
            'subscriberCount' => $subscriberCount,
            'mapSubscribers' => $mapSubscribers
        ], Response::HTTP_OK);
        return $this->handleView($view);
    }
}
