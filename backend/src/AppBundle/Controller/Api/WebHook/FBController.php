<?php
/**
 * Created by PhpStorm.
 * Date: 14.11.18
 * Time: 14:45
 */

namespace AppBundle\Controller\Api\WebHook;

use AppBundle\Webhooks\FB;
use AppBundle\Webhooks\FBFeed;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * Class FBController
 * @package AppBundle\Controller\Api\WebHook
 *
 * @Rest\Route("/fb")
 */
class FBController extends FOSRestController
{
    /**
     * FBController constructor.
     */
    public function __construct()
    {
        ini_set('max_execution_time', 0);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/")
     * @SWG\Get(path="/v2/webhook/fb/",
     *   tags={"WEBHOOK FB"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     * @Rest\Post("/")
     * @SWG\Post(path="/v2/webhook/fb/",
     *   tags={"WEBHOOK FB"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function fbAction(Request $request){
        if (!empty($_REQUEST['hub_mode']) && $_REQUEST['hub_mode'] == 'subscribe' && $_REQUEST['hub_verify_token'] == $this->container->getParameter('facebook_verify')) {
            // Webhook setup request
            echo $_REQUEST['hub_challenge'];
        } else {
            $data = json_decode(file_get_contents("php://input"), true, 512, JSON_BIGINT_AS_STRING);
            if (isset($data['entry']) && isset($data['entry'][0]) && isset($data['entry'][0]['messaging']) && !empty($data['entry'][0]['messaging'])){
                $url = $request->getSchemeAndHttpHost()."/v2/webhook/fb/do";
            }
            elseif (isset($data['entry']) && isset($data['entry'][0]) && isset($data['entry'][0]['changes']) && !empty($data['entry'][0]['changes'])){
                $url = $request->getSchemeAndHttpHost()."/v2/webhook/fb/feed";
            }
            else{
                return $this->handleView($this->view(null, Response::HTTP_OK));
            }

            $headers = [
                'Content-Type: application/json',
            ];

            $process = curl_init($url);
            curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($process, CURLOPT_HEADER, false);
            curl_setopt($process, CURLOPT_TIMEOUT, 5);
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
            $return = curl_exec($process);
            curl_close($process);
        }
        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @Rest\Get("/do")
     * @SWG\Get(path="/v2/webhook/fb/do",
     *   tags={"WEBHOOK FB"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     * @Rest\Post("/do")
     * @SWG\Post(path="/v2/webhook/fb/do",
     *   tags={"WEBHOOK FB"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function fbDoAction(Request $request){
        try{
            $data = json_decode(file_get_contents("php://input"), true, 512, JSON_BIGINT_AS_STRING);
            /*if($data['entry'][0]['id'] == '2007082092860760'){
                $fs = new Filesystem();
                $fs->appendToFile('webhook_request.txt', json_encode($data)."\n\n");
            }*/
            
            $fb = new FB($this->getDoctrine()->getManager(), $this->container, $this->container->get('gos_web_socket.zmq.pusher'), $data);
            $response = $fb->handler();
            return $this->handleView($this->view($response, Response::HTTP_OK));

        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('webhook_request.txt', json_encode($e->getMessage())."\n\n");
        }
        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Get("/feed")
     * @SWG\Get(path="/v2/webhook/fb/feed",
     *   tags={"WEBHOOK FB"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     * @Rest\Post("/feed")
     * @SWG\Post(path="/v2/webhook/fb/feed",
     *   tags={"WEBHOOK FB"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function fbFeedAction(Request $request){
        try{
            $data = json_decode(file_get_contents("php://input"), true, 512, JSON_BIGINT_AS_STRING);
            /*if($data['entry'][0]['id'] == '2007082092860760'){
                $fs = new Filesystem();
                $fs->appendToFile('webhook_feed_request.txt', json_encode($data)."\n\n");
            }*/
            $fb = new FBFeed($this->getDoctrine()->getManager(), $this->container, $data);
            $response = $fb->handler();

            return $this->handleView($this->view($response, Response::HTTP_OK));

        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('webhook_feed_request.txt', json_encode($e->getMessage())."\n\n");
        }
        return $this->handleView($this->view(null, Response::HTTP_OK));
    }
    


    
    
}
