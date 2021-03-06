<?php

namespace AppBundle\Repository;

/**
 * AutopostingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AutopostingRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $page_id
     * @return mixed
     */
    public function getAllAutopostingByPageId($page_id){

        $query = $this->createQueryBuilder('a')
            ->select('a.id, a.title, a.type, a.account, a.url, a.typePush, a.targeting, a.status')
            ->where("a.page_id = :pageID")
            ->setParameter('pageID', $page_id);

        return $query->getQuery()->getResult();
    }

    /**
     * @param \DateTime $now
     * @return mixed
     */
    public function updateLastSeenAll(\DateTime $now){
        return $this->createQueryBuilder('a')
            ->update()
            ->set('a.lastSeen', ':now')
            ->setParameter('now', $now->format('Y-m-d H:i:s'))
            ->getQuery()->getResult();
    }
}
