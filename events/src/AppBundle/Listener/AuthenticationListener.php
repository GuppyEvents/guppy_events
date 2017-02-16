<?php

namespace AppBundle\Listener;


use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthenticationListener
{
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
    public function onAuthenticationSuccess( InteractiveLoginEvent $event )
    {
        // executes on successful login
    }
}