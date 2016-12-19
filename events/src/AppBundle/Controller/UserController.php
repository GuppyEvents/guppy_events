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
     * @Route("/a/profile", name="admin_profile")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function adminProfileAction(Request $request)
    {
        return $this->render('AppBundle:user:admin_profile.html.twig', array(
            'aa'=>'aa',
        ));
    }

    /**
     * @Route("/profile", name="user_profile_account")
     * @Security("has_role('ROLE_USER')")
     */
    public function userProfileAction(Request $request)
    {
        $data = array();
        $data['a'] = 'a';
        return $this->render('AppBundle:user:profile_settings_account.html.twig', $data);
    }

    /**
     * @Route("/profile/mail", name="user_profile_mail")
     * @Security("has_role('ROLE_USER')")
     */
    public function userProfileMailAction(Request $request)
    {
        $data = array();
        $data['a'] = 'a';
        return $this->render('AppBundle:user:profile_settings_mail.html.twig', $data);
    }

    /**
     * @Route("/profile/password", name="user_profile_password")
     * @Security("has_role('ROLE_USER')")
     */
    public function userProfilePasswordAction(Request $request)
    {
        $data = array();
        $data['a'] = 'a';
        return $this->render('AppBundle:user:profile_settings_password.html.twig', $data);
    }
}
