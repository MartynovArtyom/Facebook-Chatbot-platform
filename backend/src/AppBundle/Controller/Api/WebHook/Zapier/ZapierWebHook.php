<?php
/**
 * Created by PhpStorm.
 * Date: 18.01.19
 * Time: 14:05
 */

namespace AppBundle\Controller\Api\WebHook\Zapier;

use AppBundle\Entity\CustomFields;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\Tag;
use AppBundle\Entity\ZapierApiKey;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * Class ZapierWebHook
 * @package AppBundle\Controller\Api\WebHook\Zapier
 *
 * @Rest\Route("/zapier/webhook")
 */
class ZapierWebHook extends FOSRestController
{
    /**
     * @param Request $request
     * @return Response
     *
     * @Rest\Post("/")
     * @SWG\Post(path="/v2/webhook/zapier/webhook/",
     *   tags={"ZAPIER WEBHOOK"},
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
    public function subscribeAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId(), 'status'=>true]);
                    if($page instanceof Page){
                        if($request->request->has('target_url') && !empty($request->request->get('target_url'))
                            && $request->request->has('event') && !empty($request->request->get('event'))
                        ){
                            $field_id = null;
                            if($request->request->get('event') == 'set_custom_field'){
                                if($request->request->has('custom_field') && !empty($request->request->get('custom_field'))){
                                    $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['id'=>$request->request->get('custom_field'), 'page_id'=>$page->getPageId()]);
                                    if($customField instanceof CustomFields){
                                        $field_id = $customField->getId();
                                    }
                                    else{
                                        return $this->handleView($this->view(['error'=>'Custom Field not Found'], Response::HTTP_BAD_REQUEST));
                                    }
                                }
                                else{
                                    return $this->handleView($this->view(['error'=>'custom_field is required'], Response::HTTP_BAD_REQUEST));
                                }
                            }
                            elseif ($request->request->get('event') == 'new_tag'){
                                if($request->request->has('tag') && !empty($request->request->get('tag'))){
                                    $tag = $em->getRepository("AppBundle:Tag")->findOneBy(['id'=>$request->request->get('tag'), 'page_id'=>$page->getPageId()]);
                                    if($tag instanceof Tag){
                                        $field_id = $tag->getId();
                                    }
                                    else{
                                        return $this->handleView($this->view(['error'=>'Tag not Found'], Response::HTTP_BAD_REQUEST));
                                    }
                                }
                                else{
                                    return $this->handleView($this->view(['error'=>'tag is required'], Response::HTTP_BAD_REQUEST));
                                }
                            }
                            elseif ($request->request->get('event') == 'subscribe_sequence'){
                                if($request->request->has('sequence') && !empty($request->request->get('sequence'))){
                                    $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['id'=>$request->request->get('sequence'), 'page_id'=>$page->getPageId()]);
                                    if($sequence instanceof Sequences){
                                        $field_id = $sequence->getId();
                                    }
                                    else{
                                        return $this->handleView($this->view(['error'=>'Sequences not Found'], Response::HTTP_BAD_REQUEST));
                                    }
                                }
                                else{
                                    return $this->handleView($this->view(['error'=>'sequence is required'], Response::HTTP_BAD_REQUEST));
                                }
                            }
                            $checkZapierWebhook = $em->getRepository("AppBundle:ZapierWebhook")->findOneBy(['page_id'=>$page->getPageId(), 'target_url'=>$request->request->get('target_url')]);
                            if(!$checkZapierWebhook instanceof \AppBundle\Entity\ZapierWebhook){
                                $zapierWebhook = new \AppBundle\Entity\ZapierWebhook($page->getPageId(), $request->request->get('target_url'), $request->request->get('event'), $field_id);
                                $em->persist($zapierWebhook);
                                $em->flush();

                                return $this->handleView($this->view(['id'=>$zapierWebhook->getId()], Response::HTTP_CREATED));
                            }
                            else{
                                return $this->handleView($this->view(['error'=>'target_url is duplicate'], Response::HTTP_CONFLICT));
                            }
                        }
                        else{
                            return $this->handleView($this->view(['error'=>'target_url and event is required'], Response::HTTP_BAD_REQUEST));
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
            $fs->appendToFile('zapier_request.txt',"WEBHOOK SUBSCRIBE ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     *
     * @Rest\Delete("/{id}")
     * @SWG\Delete(path="/v2/webhook/zapier/webhook/{id}",
     *   tags={"ZAPIER WEBHOOK"},
     *   @SWG\Parameter(
     *      name="api_key",
     *      in="query",
     *      required=true,
     *      type="string",
     *      default="",
     *      description="api_key"
     *   ),
     *   @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
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
    public function unSubscribeAction(Request $request, $id){
        try{
            $em = $this->getDoctrine()->getManager();
            if($request->query->has('api_key') && !empty($request->query->get('api_key'))){
                $zapierKey = $em->getRepository("AppBundle:ZapierApiKey")->findOneBy(['token'=>$request->query->get('api_key')]);
                if($zapierKey instanceof ZapierApiKey){
                    $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$zapierKey->getPageId()]);
                    if($page instanceof Page){
                        $zapierWebhook = $em->getRepository("AppBundle:ZapierWebhook")->find($id);
                        if($zapierWebhook instanceof \AppBundle\Entity\ZapierWebhook){
                            $em->remove($zapierWebhook);
                            $em->flush();
                        }

                        return $this->handleView($this->view([], Response::HTTP_OK));
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
            $fs->appendToFile('zapier_request.txt',"WEBHOOK UNSUBSCRIBE ERROR:\n".$e->getMessage()."\n\n");

            return $this->handleView($this->view(['error'=>$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}