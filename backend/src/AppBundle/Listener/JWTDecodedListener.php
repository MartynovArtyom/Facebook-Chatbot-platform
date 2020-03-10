<?php


namespace AppBundle\Listener;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class JWTDecodedListener
 * @package AppBundle\Listener
 */
class JWTDecodedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

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
     * @param JWTDecodedEvent $event
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        $payload = $event->getPayload();
        if(isset($payload['user_id']) && !empty($payload['user_id'])){
            $user = $this->em->getRepository('AppBundle:User')->find($payload['user_id']);
            if(!$user instanceof User || !$user->isEnabled()){
                $event->markAsInvalid();
            }
        }
    }
}
