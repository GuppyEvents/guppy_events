<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Utils;
use Monolog\Handler\Curl\Util;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\UserType;
use AppBundle\Form\AdminUserType;
use AppBundle\Entity\User;
use AppBundle\Entity\AdminUser;

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
        $form = $this->createForm(UserType::class, $user);
        $data['form'] = $form->createView();

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

//            $acceptedMailAddress = '@ug.bilkent.edu.tr';
            $acceptedMailAddress = '@gmail.com';
            if(substr($user->getEmail(), -strlen($acceptedMailAddress)) === $acceptedMailAddress){

                // 3) Encode the password (you could also do this via Doctrine listener)
                $password = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPassword());
                $user->setPassword($password);

                // 4) save the User!
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $confirmLink = "http://seruvent.com/activation/" . base64_encode( Utils::getGUID() . "**" . $user->getId() . "##" . rand(10,100));

                Utils::mailSendSingle($user->getEmail(),"Seruvent Kayıt Aktivasyonu", "Merhaba " . $user->getName() . ",\n\rKaydını onaylamak için aşağıdaki linke tıklaman yeterli.\n\r" . $confirmLink);

                return $this->redirectToRoute('homepage');

            }else{
                $data['error_msg'] = "Bilkent mail adresi ile kayıt olmanız gerekiyor. (@ug.bilkent.edu.tr)";
            }
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

            $user->setEmailValidated(true);
            $em->persist($user);
            $em->flush();

        } catch (Exception $e){}

        return $this->redirectToRoute('homepage');
    }
    
}
