<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Address;
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
     * @Route("/get", name="university_get")
     * @Security("has_role('ROLE_USER')")
     */
    public function getAction(Request $request)
    {
        return $this->render(
            'AppBundle:admin:university.html.twig'
        );
    }


    /**
     * @Route("/post", name="university_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function postAction(Request $request)
    {
//        // 1) build the form
//        $university = new University();
//        $form = $this->createForm(UniversityType::class, $university);
//
//        // 2) handle the submit (will only happen on POST)
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//
//            // 3) save the University!
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($university);
//            $em->flush();
//
//            return $this->redirectToRoute('admin_homepage');
//        }



        if($request->getMethod() == 'POST'){

            // 1) University Repository should try to register university
            $university = new University();
            $university->setName( $request->get('university_name') );
            $university->setLink( $request->get('university_web_address') );

            $address = new Address();
            $address->setCityId( $request->get('address_city_id') );
            $address->setBoroughId( $request->get('address_borough_id') );

            // 2) Redirect route to university list page
            return $this->redirectToRoute('university_get');
        }

        return $this->render(
            'AppBundle:university:universityRegister.html.twig'
        );
    }

}
