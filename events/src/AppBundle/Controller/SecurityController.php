<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Utils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Entity\User;

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

        if($user && $user->getEmail() == $fbUser->getEmail()){
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            if($this->getUser() && $this->getUser()->getId() && $this->getUser()->getRole()=='ROLE_ADMIN'){
                return $this->redirectToRoute('admin_homepage');
            }else if($this->getUser()){
                return $this->redirectToRoute('home_events');
            }
        }else if(!$user){
            return self::registerWithFacebookToken($request->get("fbToken"));
        }else{
            $_SESSION['error_message'] = "Beklenmedik bir hata oluştu.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
            return $this->redirectToRoute('user_registration');
        }

    }

    public function registerWithFacebookToken($token)
    {
        try {
            // 1) build the form
            $user = new User();
            $data = array();


            // 3) check facebook access token
            $fbUser = Utils::getFbUserFromFbToken($token);
            $name = $fbUser->getName();
            $surname = $fbUser->getName();
            if(strrpos($name, " ") !== false){
                $surname = substr($name, strrpos($name, " ")+1);
                $name = substr($name, 0, strrpos($name, " "));
            }
            $user->setName($name);
            $user->setSurname($surname);
            $user->setEmail($fbUser->getEmail());
            $user->setUsername($fbUser->getEmail());
            $user->setFbId($fbUser->getId());
            $password = Utils::getGUID();
            $user->setPassword($password);
            $imageLink = Utils::uploadBytesToServer(file_get_contents($fbUser->getPicture()->getUrl()), Utils::getGUID() . ".jpg");
            $user->setImageBase64($imageLink);

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $confirmLink = "http://seruvent.com/activation/" . base64_encode(Utils::getGUID() . "**" . $user->getId() . "##" . rand(10, 100));
            try {
                Utils::mailSendSingle($user->getEmail(), "Seruvent Kayıt Aktivasyonu", "Merhaba " . $user->getName() . ",\n\rKaydını onaylamak için aşağıdaki linke tıklaman yeterli.\n\r" . $confirmLink);
            }catch (Exception $e){

            }

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            $_SESSION['success_message'] = "Kaydınız başarıyla tamamlanmıştır.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır

            return $this->redirectToRoute('home_events');
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Kaydınız oluşturulamadı.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
        }

        return $this->redirectToRoute('login');

    }
    
}
