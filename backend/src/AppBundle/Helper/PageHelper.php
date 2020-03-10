<?php
/**
 * Created by PhpStorm.
 * Date: 23.07.18
 * Time: 13:08
 */

namespace AppBundle\Helper;


use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\Page;
use AppBundle\Entity\PageAdmins;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Class PageHelper
 * @package AppBundle\Helper
 */
class PageHelper
{
    /**
     * @param EntityManager $em
     * @param $page_id
     * @param User $user
     * @return Page|bool|null|object
     */
    public static function checkAccessPage(EntityManager $em, $page_id, User $user){
        $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$page_id, 'user'=>$user, 'status'=>true]);
        if($page instanceof Page){
            return $page;
        }
        else{
            $pageAdmin = $em->getRepository("AppBundle:PageAdmins")->findOneBy(['page_id'=>$page_id, 'user'=>$user]);
            if($pageAdmin instanceof PageAdmins){
                $page = $em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$pageAdmin->getPageId(),'status'=>true]);
                if($page instanceof Page){
                    return $page;
                }
            }
        }

        return false;
    }

    /**
     * @param EntityManager $em
     * @param $pages
     * @return array
     */
    public static function getPagesResponse(EntityManager $em, $pages){
        $result = [];
        if(!empty($pages)){
            foreach ($pages as $page){
                if($page instanceof Page){
                    $countSubscribers = $em->getRepository('AppBundle:Subscribers')->count(['page_id'=>$page->getPageId(),'status'=>true]);

                    $result[] = [
                        'id' => $page->getId(),
                        'page_id' => $page->getPageId(),
                        'title' => $page->getTitle(),
                        'avatar' => $page->getAvatar(),
                        'status' => $page->getStatus(),
                        'created' => $page->getCreated(),
                        'userID' => $page->getUser()->getId(),
                        'firstName' => $page->getUser()->getFirstName(),
                        'lastName' => $page->getUser()->getLastName(),
                        'countSubscribers' => $countSubscribers,
                        'limitSubscribers' => $page->getUser()->getLimitSubscribers(),
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @return array
     */
    public static function getPageResponse(EntityManager $em, Page $page){
        $countSubscribers = $em->getRepository('AppBundle:Subscribers')->count(['page_id'=>$page->getPageId(),'status'=>true]);
        $countFlows = $em->getRepository('AppBundle:Flows')->count(['page_id'=>$page->getPageId()]);
        $countWidgets = $em->getRepository('AppBundle:Widget')->count(['page_id'=>$page->getPageId()]);
        $countSequences = $em->getRepository('AppBundle:Sequences')->count(['page_id'=>$page->getPageId()]);
        $mapSubscribers = $em->getRepository("AppBundle:Subscribers")->getSubscriberForMap($page->getPageId());

        return [
            'id' => $page->getId(),
            'page_id' => $page->getPageId(),
            'title' => $page->getTitle(),
            'avatar' => $page->getAvatar(),
            'status' => $page->getStatus(),
            'created' => $page->getCreated(),
            'userID' => $page->getUser()->getId(),
            'firstName' => $page->getUser()->getFirstName(),
            'lastName' => $page->getUser()->getLastName(),
            'countSubscribers' => $countSubscribers,
            'countFlows' => $countFlows,
            'countWidgets' => $countWidgets,
            'countSequences' => $countSequences,
            'mapSubscribers' => $mapSubscribers,
            'limitSubscribers' => $page->getUser()->getLimitSubscribers(),
            'product' => ($page->getUser()->getProduct() instanceof DigistoreProduct) ? $page->getUser()->getProduct()->toArray() : $page->getUser()->getProduct(),
        ];
    }
}
