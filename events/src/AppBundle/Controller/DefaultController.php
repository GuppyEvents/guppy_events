<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        $communities = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy(array('university'=>5));

        return $this->render('default/home.html.twig' , array(
            'communities' => $communities
        ));

    }

    /**
     * @Route("/society/{communityId}", name="homepage_club")
     */
    public function clubAction($communityId)
    {
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findEventsByCommunityId($communityId);

        return $this->render('default/club.html.twig' , array(
            'community' => $community,
            'eventList' => $eventList
        ));
    }

}
