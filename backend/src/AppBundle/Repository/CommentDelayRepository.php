<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CommentDelayRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CommentDelayRepository extends EntityRepository
{
    /**
     * @return mixed
     * @throws \Exception
     */
    public function findNeedSendNow(){
        $date = new \DateTime();

        $query = $this->createQueryBuilder('cd')
            ->select('cd')
            ->where("DATE_FORMAT(cd.sendDate, '%Y-%m-%d %H:%i') = :date")
            ->setParameter('date', $date->format('Y-m-d H:i'));

        return $query->getQuery()->getResult();
    }
}
