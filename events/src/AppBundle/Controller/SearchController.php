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

        $data['search_key'] = $searchKey;
        $data['communityList'] = $this->getDoctrine()->getRepository('AppBundle:Community')->findCommunityListByName($searchKey);
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
                        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findCommunityListByName($key,$page);

                        $data['communityList'] = array();
                        foreach ($communityList as $community) {
                            $communityObj = array();
                            $communityObj['id'] = $community->getId();
                            $communityObj['name'] = $community->getName();
                            $communityObj['image'] = $community->getImageBase64();
                            array_push($data['communityList'], $communityObj);
                        }
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