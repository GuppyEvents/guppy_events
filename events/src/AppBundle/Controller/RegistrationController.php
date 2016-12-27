<?php

namespace AppBundle\Controller;

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

            $acceptedMailAddress = '@ug.bilkent.edu.tr';
            if(substr($user->getEmail(), -strlen($acceptedMailAddress)) === $acceptedMailAddress){

                // 3) Encode the password (you could also do this via Doctrine listener)
                $password = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPassword());
                $user->setPassword($password);

                // 4) save the User!
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('homepage');

            }else{
                $data['error_msg'] = "Bilkent mail adresini girmeniz gerekiyor.";
            }
        }

        return $this->render('AppBundle:registration:register.html.twig', $data );

    }
    
}
