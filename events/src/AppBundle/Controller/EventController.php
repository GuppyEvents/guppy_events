<?php

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\Community;
use AppBundle\Entity\CommunityUser;
use AppBundle\Entity\Event;
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
 * @Route("/event", name="event")
 */
class EventController extends Controller
{


    /**
     * @Route("/h/{eventId}", name="user_event_mainpage")
     */
    public function eventMainPageAction($eventId)
    {
        $data = array();

        $event = $this->getDoctrine()->getRepository('AppBundle:Event')->find($eventId);

        $data['event'] = $event;

        return $this->render('AppBundle:event:eventMain.html.twig' , $data);
    }


    /**
     * @Route("/list", name="event_list")
     * @Security("has_role('ROLE_USER')")
     */
    public function listAction(Request $request)
    {

        $selectedCommunityId = null;
        $selectedUniversityId = null;
        $communityList = array();

        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findAll();
        $universityList = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){
            $em = $this->getDoctrine()->getManager();

            try{
                $selectedCommunityId = $request->get('community_id');
                $selectedUniversityId = $request->get('university_id');

                // eğer kulüp id değeri varsa o kulübün etkinlik listesi getir
                if($selectedCommunityId && $this->getDoctrine()->getRepository('AppBundle:Community')->find($request->get('community_id'))){
                    $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy(array('university'=>$request->get('university_id')));
                    $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findEventsByCommunityId($request->get('community_id'));


                // eğer universite id değeri varsa o universitenin etkinlik listesi getir
                }else if($selectedUniversityId && $this->getDoctrine()->getRepository('AppBundle:University')->find($request->get('university_id'))){
                    $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy(array('university'=>$request->get('university_id')));
                    $communityIdList = array();
                    for($i=0; $i<count($communityList);$i++){
                        array_push($communityIdList ,$communityList[$i]->getId());
                    }
                    $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findEventsByCommunityIdList($communityIdList);


                // hiçbiri yoksa tüm etkinleri getir
                }else{
                    $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findAll();
                }

                $universityList = $em->getRepository('AppBundle:University')->findAll();
            } catch (Exception $e){
                
            }
        }

        return $this->render( 'AppBundle:event:eventList.html.twig', array(
                'eventList'=>$eventList,
                'universityList'=>$universityList,
                'communityList'=>$communityList,
                'selectedCommunityId'=>$selectedCommunityId,
                'selectedUniversityId'=>$selectedUniversityId
            )
        );
    }



    /**
     * @Route("/post", name="event_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function postAction(Request $request)
    {
        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getManager();

            try{

                // --1.1-- Event have to added by user
                // --1.1.1-- Eğer kullanıcı admin ise izin ver
                // --1.2-- Event may contains community
                $user = $this->getUser();
                $community = $em->getRepository('AppBundle:Community')->find($request->get('community_id'));
                $communityUser = $em->getRepository('AppBundle:CommunityUser')->findBy(array('user'=>$user->getId() , 'community'=>$community->getId()));

                // --2.1-- Eğer böyle bir topluluk kullanıcısı varsa o kullanıcı ile işlem yap
                if(count($communityUser)>0){

                    $communityUser = $communityUser[0];

                // --2.2-- Eğer böyle bir topluluk kullanıcısı yoksa kullanıcıyı kulüp ile ilişkilendir
                }else{
                    $communityUser = new CommunityUser();
                    $communityUser->setCommunity($community);
                    $communityUser->setUser($user);
                    $communityUser->setDate(new \DateTime());

                    $em->persist($communityUser);
                    $em->flush();
                }
                
                $request_date = \DateTime::createFromFormat('m/d/Y H:i A', $request->get('event_date'));
                $request_permission = $request->get('event_permission') ? $request->get('event_permission') : 'PUBLIC';

                $event = new Event();
                $event->setTitle( $request->get('event_title') );
                $event->setDescription( $request->get('event_description') );
                $event->setPermission($request_permission);
                $event->setStartDate( $request_date );
                $event->setMaxParticipantNum( $request->get('event_participant_count') );
                $event->setImageBase64($request->get('event_image_base64'));
                $event->setGpsLocationLat($request->get('event_location_lat'));
                $event->setGpsLocationLng($request->get('event_location_lng'));
                $event->setCommunityUser( $communityUser );

                $em->persist($event);
                $em->flush();

            } catch (Exception $e){}

            // Redirect route to university list page
            return $this->redirectToRoute('event_list');
        }


        // 2) DEFAULT CASE
        $universities = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        return $this->render('AppBundle:event:eventRegister.html.twig', array(
                'universities'=>$universities
            )
        );
    }

    /**
     * @Route("/post/{eventId}", name="event_post_to_id")
     * @Security("has_role('ROLE_USER')")
     */
    public function postToIdAction(Request $request , $eventId)
    {

        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            // 1.1) try to update community
            try{
                $em = $this->getDoctrine()->getManager();
                $event = $em->getRepository('AppBundle:Event')->find($eventId);
                if (!$event) {
                    throw $this->createNotFoundException(
                        'No product found for id '.$event
                    );
                }else{
                    $date = \DateTime::createFromFormat('m/d/Y H:i A', $request->get('event_date'));

                    $event->setTitle( $request->get('event_title') );
                    $event->setDescription( $request->get('event_description') );
                    $event->setStartDate( $date );
                    $event->setMaxParticipantNum( $request->get('event_participant_count') );
                    $event->setImageBase64($request->get('event_image_base64'));
                    $em->persist($event);
                    $em->flush();
                }

            } catch (Exception $e){}

            // 1.2) Redirect route to university list page
            return $this->redirectToRoute('event_list');
        }

        // 2) DEFAULT CASE
        $event = $this->getDoctrine()->getRepository('AppBundle:Event')->find($eventId);
        $ticketList = $this->getDoctrine()->getRepository('AppBundle:Ticket')->findBy(array('event'=>$event->getId()));

        return $this->render('AppBundle:event:eventUpdate.html.twig', array(
                'event'=>$event,
                'eventTicketList'=>$ticketList
            )
        );
    }



    /**
     * @Route("/delete/{eventId}", name="event_delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction($eventId)
    {

        // 1) POST OPERATION
        // 1.1) try to delete community
        try{
            $em = $this->getDoctrine()->getManager();

            $event = $em->getRepository('AppBundle:Event')->find($eventId);
            if (!$event) {
                throw $this->createNotFoundException(
                    'No product found for id '.$event
                );
            }

            $em->remove($event);
            $em->flush();

        } catch (Exception $e){}

        // 1.2) Redirect route to community list page
        return $this->redirectToRoute('event_list');

    }


    /*******************************************************************************************************************
     *******************************************************************************************************************
                                                    UTIL FUNCTIONS
     *******************************************************************************************************************
     *******************************************************************************************************************
     */

    // checker functions will be here ...


}
