<?php

namespace AppBundle\Listener;


use AppBundle\Entity\Utils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use AppBundle\Repository\UserRepository;
class AuthenticationListener
{
    protected $em;
    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * onAuthenticationFailure
     *
     * @param 	AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure( AuthenticationFailureEvent $event )
    {
        // executes on failed login
    }

    /**
     * onAuthenticationSuccess
     *
     * @param 	InteractiveLoginEvent $event
     */
    public function onAuthenticationSuccess(InteractiveLoginEvent $event )
    {
        // executes on successful login
        $isCommunityAdmin = $this->em->getRepository('AppBundle:User')->hasUserCommunityAdmin($event->getAuthenticationToken()->getUser());
        Utils::setUserCanAddEvent($event->getRequest()->getSession(), $isCommunityAdmin);
    }
}