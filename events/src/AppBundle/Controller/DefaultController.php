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

        // TODO: Şuan için sadece bilkent üniversitesi etkinliklerini getirir
        $communities = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy(array('university'=>5) , array('name'=>'ASC'));

        $weekStartDate = new \DateTime();
        $weekStartDate->setTime(0,0);
        $weekStartDate->sub(new \DateInterval("P3D"));

        $weekFinishDate = new \DateTime();
        $weekFinishDate->setTime(0,0);
        $weekFinishDate->add(new \DateInterval("P3D"));

        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findEventsByDate( $weekStartDate,$weekFinishDate);

        return $this->render('default/home.html.twig' , array(
            'communities' => $communities,
            'eventList' => $eventList,
            'weekStartDate' => $weekStartDate
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
