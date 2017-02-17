<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Utils;
use Monolog\Handler\Curl\Util;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Facebook\Facebook;

class RegistrationController extends Controller
{

    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request)
    {
        // 1) build the form
        $user = new User();
        $data = array();
        $data = array_merge($data,Utils::getSessionToastMessages());
        $form = $this->createForm(UserType::class, $user);
        $data['form'] = $form->createView();

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

//            $acceptedMailAddress = '@ug.bilkent.edu.tr';
            $acceptedMailAddress = '@';


            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $confirmLink = "http://seruvent.com/activation/" . base64_encode(Utils::getGUID() . "**" . $user->getId() . "##" . rand(10, 100));

            Utils::mailSendSingle($user->getEmail(), "Seruvent Kayıt Aktivasyonu", "Merhaba " . $user->getName() . ",\n\rKaydını onaylamak için aşağıdaki linke tıklaman yeterli.\n\r" . $confirmLink);

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            $_SESSION['success_message'] = "Kaydınız başarıyla tamamlanmıştır.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
            return $this->redirectToRoute('home_events');
        }
        return $this->render('AppBundle:registration:register.html.twig', $data );

    }

    /**
     * @Route("/registerFacebook", name="user_registration_facebook")
     */
    public function registerFacebookAction(Request $request)
    {

        // 1) build the form
        $user = new User();
        $data = array();
        $form = $this->createForm(UserType::class, $user);
        $data['form'] = $form->createView();

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 3) check facebook access token
            $fbUser = Utils::getFbUserFromFbToken($user->getFbId());

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

            Utils::mailSendSingle($user->getEmail(), "Seruvent Kayıt Aktivasyonu", "Merhaba " . $user->getName() . ",\n\rKaydını onaylamak için aşağıdaki linke tıklaman yeterli.\n\r" . $confirmLink);

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            $_SESSION['success_message'] = "Kaydınız başarıyla tamamlanmıştır.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır

            return $this->redirectToRoute('home_events');
        }
        return $this->render('AppBundle:registration:register.html.twig', $data );

    }



    /**
     * @Route("/activation/{uid}", name="user_register_activation")
     */
    public function registerActivationAction($uid)
    {
        $uid = base64_decode($uid);
        $uid = substr($uid, strpos($uid, "**")+2);
        $uid = substr($uid, 0, strpos($uid, "##"));

        $em = $this->getDoctrine()->getManager();
        $data = array();

        try{

            $user = $em->getRepository('AppBundle:User')->find($uid);
            if (!$uid) {
                throw $this->createNotFoundException(
                    'No user found for id '.$uid
                );
            }

            if($user->getEmailValidated()){
                $_SESSION['error_message'] = "Üyelik daha önce aktifleştirilmiştir.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
                return $this->redirectToRoute('login');
            }

            $user->setEmailValidated(true);
            $em->persist($user);
            $em->flush();
        } catch (Exception $e){}

        $_SESSION['success_message'] = "Üyeliğiniz başarıyla aktif edilmiştir.";//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır

        return $this->redirectToRoute('login');
    }
    
}
