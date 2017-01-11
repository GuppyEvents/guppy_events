<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Result;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        // TODO: Şuan için sadece BİLKENT ÜNİVERSİTESİ toplulukları getirilmekte
        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findCommunityListByUniversity();

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


    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/s/community-list", name="service_community_list")
     */
    public function getMoreSearchResult(Request $request){

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $data = array();

        // -- 2 -- Try to Get More Community Result
        try{

            // -- 2.1 -- Get parameter list
            $page = intval($request->get('page'));
            $pageSize = $request->get('pageSize') ? intval($request->get('pageSize')) : 12;

            $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findCommunityListByUniversity($page,$pageSize);

            $data['communityList'] = array();
            foreach ($communityList as $community) {
                $communityObj = array();
                $communityObj['id'] = $community->getId();
                $communityObj['name'] = $community->getName();
                $communityObj['image'] = $community->getImageBase64();
                $communityObj['universityName'] = $community->getUniversity()->getName();


                $communityObj['link_facebook'] = null;
                $communityObj['link_twitter'] = null;
                $communityObj['link_instagram'] = null;
                $communityLinks = $this->getDoctrine()->getRepository('AppBundle:CommunityLink')->findCommunitySocialNetworksByCommunityId($community->getId());
                foreach ($communityLinks as $communityLink){
                    switch ($communityLink->getSocialNetwork()->getId()){
                        case 5001:
                            $communityObj['link_facebook'] = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                            break;
                        case 5002:
                            $communityObj['link_twitter'] = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                            break;
                        case 5003:
                            $communityObj['link_instagram'] = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                            break;
                        default:
                            break;
                    }
                }

                $communityObj['homepagelink'] = $this->get('router')->generate('user_community_homepage' , array(
                    'communityId' => $community->getId(),
                ));
                
                array_push($data['communityList'], $communityObj);
            }

            // -- 2.2 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent( $data )));
            return $response;

        }catch (\Exception $ex){
            // content == "Unexpected Error"
            $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent($ex)));
            return $response;
        }

        // -- 3 -- Set & Return value
        $response->setContent(json_encode(Result::$SUCCESS_EMPTY));
        return $response;

    }

}
