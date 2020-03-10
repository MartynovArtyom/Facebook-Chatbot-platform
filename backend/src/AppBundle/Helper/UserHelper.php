<?php
/**
 * Created by PhpStorm.
 * Date: 15.11.18
 * Time: 14:04
 */

namespace AppBundle\Helper;


use AppBundle\Entity\DigistoreProduct;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class UserHelper
{
    /**
     * @param EntityManager $em
     * @param $users
     * @return array
     */
    public static function getUsersResponse(EntityManager $em, $users){
        $result = [];
        if(!empty($users)){
            foreach ($users as $user){
                if($user instanceof User){
                    $countPages = $em->getRepository("AppBundle:Page")->count(['user'=>$user]);
                    $countSubscribers = $em->getRepository("AppBundle:Subscribers")->countAllByUserId($user->getId());

                    $result[] = [
                        'id' => $user->getId(),
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                        'avatar' => $user->getAvatar(),
                        'status' => $user->isEnabled(),
                        'countPages' => $countPages,
                        'countSubscribers' => $countSubscribers,
                        'limitSubscribers' => $user->getLimitSubscribers(),
                        'productLabel' => ($user->getProduct() instanceof DigistoreProduct) ? $user->getProduct()->getLabel() : 'Not Product'
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * @param EntityManager $em
     * @param User $user
     * @return array
     */
    public static function getUserResponseByUser(EntityManager $em, User $user){
        $role = 'ROLE_USER';
        if($user->hasRole("ROLE_ADMIN")){
            $role = 'ROLE_ADMIN';
        }

        $countFlows = $em->getRepository("AppBundle:Flows")->countAllByUserId($user->getId());
        $countSubscribers = $em->getRepository("AppBundle:Subscribers")->countAllByUserId($user->getId());
        $countWidgets = $em->getRepository("AppBundle:Widget")->countAllByUserId($user->getId());
        $countSequences = $em->getRepository("AppBundle:Sequences")->countAllByUserId($user->getId());

        $pages = $em->getRepository("AppBundle:Page")->findBy(['user'=>$user]);

        $mapSubscribers = $em->getRepository("AppBundle:Subscribers")->getSubscriberForMapByUserId($user->getId());

        return [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
            'status' => $user->isEnabled(),
            'role' => $role,
            'created' => $user->getCreated(),
            'lastLogin' => $user->getLastLogin(),
            'countPages' => count($pages),
            'countFlows' => $countFlows,
            'countSubscribers' => $countSubscribers,
            'countWidgets' => $countWidgets,
            'countSequences' => $countSequences,
            'trialEnd' => $user->getTrialEnd(),
            'pages' => PageHelper::getPagesResponse($em, $pages),
            'mapSubscribers' => $mapSubscribers,
            'limitSubscribers' => $user->getLimitSubscribers(),
            'orderId' => $user->getOrderId(),
            'product' => ($user->getProduct() instanceof DigistoreProduct) ? $user->getProduct()->toArray() : $user->getProduct(),
            'quentnId' => $user->getQuentnId(),
        ];
    }
}
