<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\AdminUserType;
use AppBundle\Entity\AdminUser;

class RegistrationController extends Controller
{

    /**
     * @Route("/register", name="admin_user_registration")
     */
    public function registerAction(Request $request)
    {
        // 1) build the form
        $user = new AdminUser();
        $form = $this->createForm(AdminUserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin_homepage');
        }

        return $this->render(
            'AppBundle:registration:register.html.twig',
            array('form' => $form->createView())
        );
    }
    
}
