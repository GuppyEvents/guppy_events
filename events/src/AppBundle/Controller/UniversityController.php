<?php

namespace AppBundle\Controller;

use AppBundle\Entity\University;
use AppBundle\Form\UniversityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/university", name="university")
 */
class UniversityController extends Controller
{

    /**
     * @Route("/post", name="university_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function postAction(Request $request)
    {
        // 1) build the form
        $university = new University();
        $form = $this->createForm(UniversityType::class, $university);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) save the University!
            $em = $this->getDoctrine()->getManager();
            $em->persist($university);
            $em->flush();

            return $this->redirectToRoute('admin_homepage');
        }

        return $this->render(
            'AppBundle:admin:university.html.twig',
            array('form' => $form->createView())
        );
    }

}
