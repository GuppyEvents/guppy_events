<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user", name="user")
 * @Security("has_role('ROLE_USER')")
 */
class UserController extends Controller
{

    /**
     * @Route("/profile", name="user_profile")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:user:profile.html.twig', array(
            'aa'=>'aa',
        ));
    }

}
