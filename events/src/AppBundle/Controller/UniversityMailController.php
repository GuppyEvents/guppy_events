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
 * @Route("/university-mail", name="university_mail")
 */
class UniversityMailController extends Controller
{

    /**
     * @Route("/list", name="university_mail_list")
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

}
