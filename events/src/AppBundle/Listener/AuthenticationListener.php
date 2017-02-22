<?php

namespace AppBundle\Listener;


use AppBundle\Entity\Utils;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use AppBundle\Repository\UserRepository;
class AuthenticationListener
{
    protected $em;
    protected $logger;
    function __construct(EntityManager $em, Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
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
        $user = $event->getAuthenticationToken()->getUser();
        $isCommunityAdmin = $this->em->getRepository('AppBundle:User')->hasUserCommunityAdmin($user);
        Utils::setUserCanAddEvent($event->getRequest()->getSession(), $isCommunityAdmin);

        // graylog
        $this->logger->addInfo("user_id:" . strval($user->getId()) . ' logged in');
    }
}