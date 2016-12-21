<?php

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\Community;
use AppBundle\Entity\CommunityUser;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventUserRating;
use AppBundle\Entity\Result;
use AppBundle\Entity\Address;
use AppBundle\Entity\University;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/social", name="social")
 */
class EventUserRatingController extends Controller
{


    /**
     * @Route("/s/event-rating", name="social_event_rating")
     */
    public function eventMainPageAction(Request $request)
    {
        // -- 1 -- Initialization
        $data = array();
        $em = $this->getDoctrine()->getManager();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Service Operation
        try{

            // -- 2.1 --
            $eventId = $request->get('eid');
            $eventSave = $request->get('is_saved') === 'true' ? true : false;

            // -- 2.2 --
            $eventUser = $em->getRepository('AppBundle:EventUserRating')->findOneBy(array('user'=>$this->getUser()->getId() , 'event'=>$eventId));

            if($eventUser){
                $eventUser->setIsAttend(!$eventSave);
            }else{
                $eventUser = new EventUserRating();
                $eventUser->setIsAttend(!$eventSave);
                $eventUser->setUser($this->getUser());

                $event = $em->getRepository('AppBundle:Event')->find($eventId);
                $eventUser->setEvent($event);
            }

            $this->getDoctrine()->getManager()->persist($eventUser);
            $this->getDoctrine()->getManager()->flush();

            $data['saved'] = $eventUser->getIsAttend();

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


    /*******************************************************************************************************************
     *******************************************************************************************************************
                                                    UTIL FUNCTIONS
     *******************************************************************************************************************
     *******************************************************************************************************************
     */

    // checker functions will be here ...


}
