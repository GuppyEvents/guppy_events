<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin", name="admin")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminController extends Controller
{

    /**
     * @Route("/", name="admin_homepage")
     */
    public function indexAction(Request $request)
    {
        $universityList = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();
        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findAll();
        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findAll();

        return $this->render('AppBundle:admin:index.html.twig', array(
            'university_count'=>count($universityList),
            'community_count'=>count($communityList),
            'event_count'=>count($eventList)
        ));
    }
    
}
