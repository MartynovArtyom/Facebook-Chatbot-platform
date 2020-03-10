<?php
/**
 * Created by PhpStorm.
 * Date: 16.07.18
 * Time: 13:26
 */

namespace AppBundle\Listener;


use AppBundle\Entity\Page;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * JWTCreatedListener constructor.
     * @param RequestStack $requestStack
     * @param EntityManager $em
     */
    public function __construct(RequestStack $requestStack, EntityManager $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    /**
     * Replaces the data in the generated
     *
     * @param JWTCreatedEvent $event
     * @throws \Exception
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        /** @var $user User */
        $user = $event->getUser();

        $expiration = new \DateTime('+1 year');
        $payload = [
            'exp' => $expiration->getTimestamp(),
            'username' => $user->getUsername(),
            'user_id' => $user->getId(),
            'facebook_id' => $user->getFacebookId(),
            'roles' => (isset($user->getRoles()[0])) ? $user->getRoles()[0] : 'ROLE_USER'
        ];

        $event->setData($payload);
    }

}
