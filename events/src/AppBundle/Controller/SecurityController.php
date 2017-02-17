<?php

namespace AppBundle\Controller;

use Monolog\Handler\Curl\Util;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
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

        if($this->getUser()){
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
     * @Route("/forgotPassword", name="forgotPassword")
     */
    public function forgotPasswordAction(Request $request)
    {
        $data = array();
        array_merge($data,Utils::getSessionToastMessages());

        if($request->isMethod('POST')){
            $email = $request->get("email");
            if($email){
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository('AppBundle:User')->findOneBy(array("email" => $email));
                if($user){
                    $link = $this->createForgotPasswordLink($user);
                    Utils::mailSendSingle($user->getEmail(), "Şifre Resetleme", "Merhaba " . $user->getName() . ",\n\rŞifreni değiştirmek için aşağıdaki linke tıklayabilirsin.\n\r" . $link);
                    $_SESSION['success_message'] = "Şifre değiştirme linkiniz mail adresinize gönderilmiştir.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
                    return $this->redirectToRoute('home_events');
                }else{
                    $_SESSION['error_message'] = "İşlem gerçekleştirilemedi.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
                }
            }else{
                $_SESSION['error_message'] = "İşlem gerçekleştirilemedi.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
            }
        }
        return $this->render(
            'AppBundle:security:forgot_password.html.twig',
            $data
        );
    }

    /**
     * @Route("/forgotPasswordChange", name="forgotPasswordChange")
     */
    public function forgotPasswordChangeAction(Request $request)
    {
        $data = array();
        $data = array_merge($data, Utils::getSessionToastMessages());
        $token = $request->get("token");
        try{
            if($request->isMethod("POST")) {
                $token = base64_decode($token);
                $id = substr($token, 0, strpos($token, "**"));
                $pass = substr($token, strpos($token, "**") + 2);
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository('AppBundle:User')->find($id);
                if ($user && $user->getPassword() == $pass) {
                    if($request->get("password")) {
                        $password = $this->get('security.password_encoder')
                            ->encodePassword($user, $request->get("password"));
                        $user->setPassword($password);
                        $em->persist($user);
                        $em->flush();
                        $_SESSION['success_message'] = "Şifreniz başarıyla değiştirilmiştir.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
                        return $this->redirectToRoute('home_events');
                    }else{
                        $data = array("error_msg" => "Şifre boş bırakılamaz.");
                        return $this->render(
                            'AppBundle:security:forgot_password_change.html.twig',
                            $data
                        );
                    }
                } else {
                    $_SESSION['error_message'] = "İşlem gerçekleştirilemedi.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
                    return $this->redirectToRoute('forgotPassword');
                }
            }else{
                return $this->render(
                    'AppBundle:security:forgot_password_change.html.twig'
                );
            }
        }catch (Exception $e){
            $_SESSION['error_message'] = "İşlem gerçekleştirilemedi.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
            return $this->redirectToRoute('forgotPassword');
        }
    }

    public function createForgotPasswordLink($user){
        $link = "http://seruvent.com/forgotPasswordChange?token=";
        $params=$user->getId() . "**" . $user->getPassword();
        return $link . base64_encode($params);
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

            $isCommunityAdmin = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->hasUserCommunityAdmin($this->getUser());
            Utils::setUserCanAddEvent($this->get('session'), $isCommunityAdmin);

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
