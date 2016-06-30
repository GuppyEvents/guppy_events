<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Result;
use AppBundle\Entity\University;
use AppBundle\Entity\UniversityMail;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/university-user", name="university_user")
 */
class UniversityUserController extends Controller
{

    /**
     * @Route("/list", name="university_user")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getAction(Request $request)
    {

        $universityMails = $this->getDoctrine()->getRepository('AppBundle:UniversityMail')->findAll();
        $universities = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        return $this->render(
            'AppBundle:universitymail:universityMailList.html.twig', array(
                'universityMails'=>$universityMails,
                'universities'=>$universities
            )
        );
    }


    /**
     * @Route("/remove/{universityUserId}", name="university_user_delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function removeUniversityUserAction($universityUserId){
        // 1) POST OPERATION
        // 1.1) try to delete university
        try{
            $em = $this->getDoctrine()->getManager();

            $universityUser = $em->getRepository('AppBundle:UniversityUser')->find($universityUserId);
            if (!$universityUser) {
                throw $this->createNotFoundException(
                    'No product found for id '.$universityUser
                );
            }

            $em->remove($universityUser);
            $em->flush();

        } catch (Exception $e){}

        // 1.2) Redirect route to university list page
        return $this->redirectToRoute('settings_emails');
    }

}