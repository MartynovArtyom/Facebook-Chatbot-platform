<?php
/**
 * Created by PhpStorm.
 * Date: 14.12.18
 * Time: 15:47
 */

namespace AppBundle\Controller\Api\Fetch;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\MainMenu;
use AppBundle\Entity\MainMenuItems;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\Widget;
use AppBundle\Flows\FlowsItem;
use AppBundle\Helper\Webhook\FbHelper;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

/**
 * Class InsightsController
 * @package AppBundle\Controller\Api\Fetch
 *
 * @Rest\Route("/insights")
 */
class InsightsController extends FOSRestController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Rest\Get("/menu")
     * @SWG\Get(path="/v2/fetch/insights/menu",
     *   tags={"INSIGHTS"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function menuAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            //SET STATS
            if($request->query->has('pageID') && !empty($request->query->get('pageID')) && $request->query->has('itemID') && !empty($request->query->get('itemID'))){
                $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$request->query->get('pageID')]);
                if($page instanceof Page){
                    $mainMenu = $em->getRepository("AppBundle:MainMenu")->findOneBy(['page_id'=>$page->getPageId()]);
                    if($mainMenu instanceof MainMenu){
                        $menuItem = $em->getRepository("AppBundle:MainMenuItems")->findOneBy(['mainMenu'=>$mainMenu, 'uuid'=>$request->query->get('itemID')]);
                        if($menuItem instanceof MainMenuItems){
                            $menuItem->setClicked($menuItem->getClicked()+1);
                            $em->persist($menuItem);
                            $em->flush();
                        }
                    }
                }
            }
        }
        catch (\Exception $e){}

        //REDIRECT TO USER URL
        if($request->query->has('url')){
            $url = parse_url(urldecode($request->query->get('url')));
            if(!isset($url['scheme'])){
                return $this->redirect('http://'.urldecode($request->query->get('url')));
            }
            else{
                return $this->redirect(urldecode($request->query->get('url')));
            }

        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @Rest\Get("/button")
     * @SWG\Get(path="/v2/fetch/insights/button",
     *   tags={"INSIGHTS"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function buttonAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            //SET STATS
            if(
                $request->query->has('flowID') && !empty($request->query->get('flowID'))
                && $request->query->has('flowItemID') && !empty($request->query->get('flowItemID'))
                && $request->query->has('itemID') && !empty($request->query->get('itemID'))
                && $request->query->has('buttonID') && !empty($request->query->get('buttonID'))
            ){
                $flow = $em->getRepository("AppBundle:Flows")->find($request->query->get('flowID'));
                if($flow instanceof Flows){
                    $flowItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flow, 'uuid'=>$request->query->get('flowItemID')]);
                    if($flowItem instanceof FlowItems){
                        if(!empty($flowItem->getItems())){
                            //UPDATE BUTTON CLICK FOR FLOW ITEM
                            $nextStepID = FbHelper::updateClickButtonForFlowItem($em, $flowItem, $request->query->get('itemID'), $request->query->get('buttonID'));

                            //NEXT STEP
                            if(!is_null($nextStepID)){
                                $nextFlowItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$flowItem->getFlow(), 'uuid'=>$nextStepID]);
                                if($nextFlowItem instanceof FlowItems){
                                    if($request->query->has('subscriberID') && !empty($request->query->get('subscriberID'))){
                                        $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy([
                                            'page_id' => $flowItem->getFlow()->getPageId(),
                                            'id' => $request->query->get('subscriberID'),
                                            'status' => true
                                        ]);
                                        if($subscriber instanceof Subscribers){
                                            $flowItemSend = new FlowsItem($em, $nextFlowItem, $subscriber);
                                            $flowItemSend->send();
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

        //REDIRECT TO USER URL
        if($request->query->has('url')){
            $url = parse_url(urldecode($request->query->get('url')));
            if(!isset($url['scheme'])){
                return $this->redirect('http://'.urldecode($request->query->get('url')));
            }
            else{
                return $this->redirect(urldecode($request->query->get('url')));
            }

        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }

    /**
     * @param Request $request
     * @param $widget_id
     * @return Response
     *
     * @Rest\Get("/widget/{widget_id}")
     * @SWG\Get(path="/v2/fetch/insights/widget/{widget_id}",
     *   tags={"INSIGHTS"},
     *   @SWG\Response(
     *     response=200,
     *     description="Success.",
     *   )
     * )
     */
    public function addShowsWidgetAction(Request $request, $widget_id){
        $em = $this->getDoctrine()->getManager();
        $widget = $em->getRepository("AppBundle:Widget")->find($widget_id);
        if($widget instanceof Widget){
            $widget->setShows($widget->getShows()+1);
            $em->persist($widget);
            $em->flush();
        }

        return $this->handleView($this->view(null, Response::HTTP_OK));
    }
}