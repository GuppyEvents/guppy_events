<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Result;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/arama", name="search")
 */
class SearchController extends Controller
{

    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          COMMON FUNCTIONS
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    public function getSearchEvents($searchKey,$page=1){

        $eventListArray = array();
        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findEventListByName($searchKey,$page);

        foreach ($eventList as $event){

            $eventTemp = array();
            $eventTemp['id'] = $event->getId();
            $eventTemp['title'] = $event->getTitle();
            $eventTemp['imageBase64'] = $event->getImageBase64();
            $eventTemp['startDate'] = $event->getStartDate()->format('d.m.Y / H:m');
            $eventTemp['isSaved'] = false;
            $eventTemp['homepagelink'] = $this->get('router')->generate('user_event_mainpage' , array(
                'eventId' => $event->getId()
            ),true);

            if($this->getUser()){
                $eventUser = $this->getDoctrine()->getRepository('AppBundle:EventUserRating')->findOneBy(array('user'=>$this->getUser(),'event'=>$event));
                if($eventUser){
                    $eventTemp['isSaved'] = $eventUser->getIsSaved();
                }
            }

            if($event->getCommunityUser()){
                if($event->getCommunityUser()->getCommunity()){
                    $eventTemp['communityId'] = $event->getCommunityUser()->getCommunity()->getId();
                    $eventTemp['communityName'] = $event->getCommunityUser()->getCommunity()->getName();
                }
            }

            $eventTemp['userSaveCount'] = count($this->getDoctrine()->getRepository('AppBundle:EventUserRating')->findBy(array('event'=>$event, 'isSaved'=>true)));

            array_push($eventListArray, $eventTemp);
        }

        return $eventListArray;

    }


    public function getSearchCommunities($searchKey,$page=1, $orderBy=0){
        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->searchCommunityListWithEventCount($searchKey,$page, $orderBy);
        foreach ($communityList as &$community) {
            $community['memberCount'] = $this->getDoctrine()->getRepository('AppBundle:Community')->findUserCountByCommunity($community['id']);
            $community['homepagelink'] = $this->get('router')->generate('user_community_events_homepage' , array(
                'communityId' => $community['id']
            ),true);

            if($this->getUser()){
                $community['isUserCommunityAdmin'] = $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser(),$community['id']);
                $community['isUserCommunityMember'] =  $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityMember($this->getUser(),$community['id']);
                $community['isUserCommunityApplier'] =  $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityApplier($this->getUser(),$community['id']);
            }
        }

        return $communityList;
    }



    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @Route("/", name="search_all")
     */
    public function indexAction(Request $request)
    {
        $data = array();
        $searchKey = $request->get('search_key');

        // DEPRECIATED
        // $data['communityList'] = $this->getDoctrine()->getRepository('AppBundle:Community')->findCommunityListByName($searchKey);

        $data['universityCommunityCount'] = $this->getDoctrine()->getRepository('AppBundle:University')->findUniversityCommunityCount(5);
        if(!$request->get('order')){
            $data['communityList'] = $this->getSearchCommunities($searchKey);
        }else{
            $data['communityList'] = $this->getSearchCommunities($searchKey, 1, $request->get('order'));
        }
        $data['search_key'] = $searchKey;
        $data['eventList'] = $this->getSearchEvents($searchKey);

        return $this->render('AppBundle:search:index.html.twig', $data);
    }




    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/more", name="search_more")
     */
    public function getMoreSearchResult(Request $request){

        // -- 1 -- Initialization
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $data = array();

        // -- 2 -- Try to Get More Search Result
            try{

            // -- 2.1 -- Get parameter list
                $more = $request->get('more');

                switch ($more){

                    case 'community':
                        $page = intval($request->get('page'));
                        $key = $request->get('key');
                        $orderBy=0;
                        if($request->get('order')){
                            $orderBy = $request->get('order');
                        }
                        $data['communityList'] = $this->getSearchCommunities($key,$page, $orderBy);
                        break;

                    case 'event':
                        $page = intval($request->get('page'));
                        $key = $request->get('key');
                        $data['eventList'] = $this->getSearchEvents($key,$page);
                        break;

                    default:
                        break;
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