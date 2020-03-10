<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
{
    /**
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function getAllForAdmin($params=[]){
        $query = $this->createQueryBuilder('u')
            ->select('u')
            ->orderBy('u.lastLogin','DESC');

        if(isset($params['search']) && !empty($params['search'])){
            $search = explode(" ", $params['search']);
            if(count($search) > 1){
                $query->andWhere('(u.firstName LIKE :search1) AND (u.lastName LIKE :search2)')
                    ->setParameter('search1', '%'.$search[0].'%')
                    ->setParameter('search2', '%'.$search[1].'%');
            }
            else{
                $query->andWhere('(u.firstName LIKE :search) OR (u.lastName LIKE :search) OR (u.email LIKE :search)')
                    ->setParameter('search', '%'.$params['search'].'%');
            }
        }

        if(isset($params['status']) && ($params['status'] == 'false' || $params['status'] == 'true')){
            if($params['status'] == 'false'){
                $query->andWhere('u.enabled = :status')
                    ->setParameter('status', false);
            }
            elseif ($params['status'] == 'true'){
                $query->andWhere('u.enabled = :status')
                    ->setParameter('status', true);
            }
        }

        if(isset($params['admin']) && $params['admin'] == "true"){
            $query->andWhere("u.roles LIKE :admin")
                ->setParameter('admin',"%".serialize(['ROLE_ADMIN'])."%");
        }
        if(array_key_exists('productId', $params)){
            if ($params['productId'] == 'null' || is_null($params['productId'])){
                $query->andWhere("(u.product IS NULL OR u.product = '')");
            }
            elseif(!empty($params['productId'])){
                $query->andWhere('u.product = :product')
                    ->setParameter('product', $params['productId']);
            }
        }
        if(isset($params['createdFrom']) && !empty($params['createdFrom'])){
            $createdFrom = ($params['createdFrom'] instanceof \DateTime) ? $params['createdFrom'] : new \DateTime($params['createdFrom']);
            $query->andWhere("DATE_FORMAT(u.created, '%Y-%m-%d') >= :createdFrom")
                ->setParameter("createdFrom", $createdFrom->format('Y-m-d'));
        }
        if(isset($params['createdTo']) && !empty($params['createdTo'])){
            $createdTo = ($params['createdTo'] instanceof \DateTime) ? $params['createdTo'] : new \DateTime($params['createdTo']);
            $query->andWhere("DATE_FORMAT(u.created, '%Y-%m-%d') <= :createdTo")
                ->setParameter("createdTo", $createdTo->format('Y-m-d'));
        }
        if(isset($params['createdTo']) && !empty($params['createdTo'])){
            $createdTo = ($params['createdTo'] instanceof \DateTime) ? $params['createdTo'] : new \DateTime($params['createdTo']);
            $query->andWhere("DATE_FORMAT(u.created, '%Y-%m-%d') <= :createdTo")
                ->setParameter("createdTo", $createdTo->format('Y-m-d'));
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getAllForTrialEnd(){
        $now = new \DateTime();

        return $this->createQueryBuilder('u')
            ->select('u')
            ->andWhere("DATE_FORMAT(u.trialEnd, '%Y-%m-%d') = :trialEnd")
            ->setParameter("trialEnd", $now->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function getAllQuntnIdIsNull(){
        return $this->createQueryBuilder('u')
            ->select('u')
            ->where('u.quentnId IS NULL')
            ->getQuery()
            ->getResult();

    }
}