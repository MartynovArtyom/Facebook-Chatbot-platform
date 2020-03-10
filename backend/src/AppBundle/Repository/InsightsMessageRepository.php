<?php

namespace AppBundle\Repository;

/**
 * InsightsMessageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InsightsMessageRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $watermark
     * @param $recipient
     * @return mixed
     */
    public function findInsightMessage($watermark, $recipient)
    {
        return $this->createQueryBuilder('im')
            ->select('im')
            ->where("im.recipient = :recipient")
            ->setParameter('recipient', $recipient)
            ->andWhere("(im.watermark <= :watermark OR im.watermark IS NULL)")
            ->setParameter('watermark', $watermark)
            ->getQuery()
            ->getResult();

    }

    /**
     * @param $recipient
     * @return mixed
     */
    public function findInsightMessageDelivery($recipient)
    {
        return $this->createQueryBuilder('im')
            ->select('im')
            ->where("im.recipient = :recipient")
            ->setParameter('recipient', $recipient)
            ->andWhere("im.delivery = :false")
            ->setParameter('false', false)
            ->getQuery()
            ->getResult();

    }
}