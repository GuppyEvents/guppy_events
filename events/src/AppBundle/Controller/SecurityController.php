<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Utils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SecurityController extends Controller
{

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        if($this->getUser() && $this->getUser()->getId() && $this->getUser()->getRole()=='ROLE_ADMIN'){
            return $this->redirectToRoute('admin_homepage');
        }else if($this->getUser()){
            return $this->redirectToRoute('home_events');
        }

        $data = array(
            // last username entered by the user
            'last_username' => $lastUsername,
            'error'         => $error,
        );
        $data = array_merge($data,Utils::getSessionToastMessages());
        return $this->render(
            'AppBundle:security:login.html.twig',
            $data
        );
    }

    /**
     * @Route("/loginFacebook", name="loginFacebook")
     */
    public function loginFacebookAction(Request $request)
    {
        $fbUser = Utils::getFbUserFromFbToken($request->get("fbToken"));
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneBy(array("fbId" => $fbUser->getId()));

        if($user->getEmail() == $fbUser->getEmail()){
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            if($this->getUser() && $this->getUser()->getId() && $this->getUser()->getRole()=='ROLE_ADMIN'){
                return $this->redirectToRoute('admin_homepage');
            }else if($this->getUser()){
                return $this->redirectToRoute('home_events');
            }
        }else{
            $_SESSION['error_message'] = "Böyle bir kullanıcı kayıtlı değil, lütfen önce kaydolunuz.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
            $data = array();
            $data = array_merge($data,Utils::getSessionToastMessages());
            return $this->redirectToRoute('user_registration');
        }

    }
    
}
