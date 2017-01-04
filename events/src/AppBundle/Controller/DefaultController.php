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
    public function homeAction(Request $request)
    {
        return $this->redirectToRoute('home_communities');
    }

    /**
     * @Route("/home/communities", name="home_communities")
     */
    public function communitiesAction(Request $request)
    {

        // TODO: Şuan için sadece bilkent üniversitesi etkinliklerini getirir
        $universityId = 5;

        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy(array('university'=>$universityId) , array('name'=>'ASC'));

        for($i=0; $i<count($communityList);$i++){

            $communityList[$i]->link_facebook = null;
            $communityList[$i]->link_twitter = null;
            $communityList[$i]->link_instagram = null;
            $communityLinks = $this->getDoctrine()->getRepository('AppBundle:CommunityLink')->findCommunitySocialNetworksByCommunityId($communityList[$i]->getId());
            foreach ($communityLinks as $communityLink){
                switch ($communityLink->getSocialNetwork()->getId()){
                    case 5001:
                        $communityList[$i]->link_facebook = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                        break;
                    case 5002:
                        $communityList[$i]->link_twitter = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                        break;
                    case 5003:
                        $communityList[$i]->link_instagram = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                        break;
                    default:
                        break;
                }
            }
        }

        $data['communities'] = $communityList;
        return $this->render('AppBundle:default:main_community_list.html.twig' , $data);

    }


    /**
     * @Route("/home/events", name="home_events")
     */
    public function eventsAction(Request $request)
    {

        $weekStartDate = new \DateTime();
        $weekStartDate->setTime(0,0);
        //$weekStartDate->sub(new \DateInterval("P3D"));

        $weekFinishDate = new \DateTime();
        $weekFinishDate->setTime(0,0);
        $weekFinishDate->add(new \DateInterval("P6D"));

        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findEventsByDate( $weekStartDate,$weekFinishDate);
        
        $data['eventList'] = $eventList;
        $data['weekStartDate'] = $weekStartDate;

        return $this->render('AppBundle:default:main_events.html.twig' , $data);
    }


    /**
     * @Route("/home/about", name="home_about")
     */
    public function aboutAction(Request $request)
    {
        $data = array();
        return $this->render('AppBundle:default:main_about.html.twig' , $data);

    }

}
