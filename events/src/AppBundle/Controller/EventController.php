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
use AppBundle\Entity\Ticket;

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
        $tickets = $this->getDoctrine()->getRepository('AppBundle:Ticket')->findBy(array('event'=>$eventId));


        // --1-- kullanıcı varsa ve topluluk yöneticisi ise userIsAdmin true döner
        // --2-- kullanıcı varsa ve topluluk yöneticisi değilse userIsAdmin false döner
        // --3-- eğer kullanıcı yoksa null döner ve register butonu da kaldırılır
        if($event){
            if($event->getCommunityUser()->getCommunity() && $this->getUser()){
                $isUserAdmin = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$event->getCommunityUser()->getCommunity() , 'user'=>$this->getUser() , 'status'=>1));
                $data['userIsAdmin'] = $isUserAdmin and count($isUserAdmin)>0 ? true : false;
            }
        }

        $data['event'] = $event;
        $data['tickets'] = $tickets;

        return $this->render('AppBundle:event:eventMain.html.twig' , $data);
    }

    /**
     * @Route("/edit/{eventId}", name="event_edit_page")
     */
    public function eventEditPageAction($eventId)
    {
        $data = array();

        $event = $this->getDoctrine()->getRepository('AppBundle:Event')->find($eventId);

        $tickets = $this->getDoctrine()->getRepository('AppBundle:Ticket')->findBy(array('event'=>$eventId));
        $communityUserList = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('user'=>$this->getUser()->getId(), 'status'=>1));
        $data["communityUserList"] = $communityUserList;

        $data['event'] = $event;
        $data['tickets'] = $tickets;

        return $this->render('AppBundle:event:eventEdit.html.twig' , $data);
    }


    /**
     * @Route("/add", name="event_add_page")
     */
    public function eventAddPageAction()
    {
        $data = array();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $communityUserList = $em->getRepository('AppBundle:CommunityUser')->findBy(array('user'=>$user->getId(), 'status'=>1));
        $data["communityUserList"] = $communityUserList;
        return $this->render('AppBundle:event:eventAdd.html.twig' , $data);
    }



    /**
     * @Route("/editEvent/", name="event_edit_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function editEventAction(Request $request)
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
                }else{
                    return $this->redirectToRoute('event_add_page');
                }

                $request_date = \DateTime::createFromFormat('m/d/Y H:i A', $request->get('event_date'));
                $request_permission = $request->get('event_permission') ? $request->get('event_permission') : 'PUBLIC';

                $event = $em->getRepository('AppBundle:Event')->find($request->get('event_id'));
                $event->setTitle( $request->get('event_title') );
                $event->setDescription( $request->get('event_description') );
                $event->setPermission($request_permission);
                $event->setStartDate( $request_date );
                $event->setMaxParticipantNum( $request->get('event_participant_count') );
                $event->setImageBase64($request->get('event_image_base64'));
                $event->setGpsLocationLat($request->get('event_location_lat'));
                $event->setGpsLocationLng($request->get('event_location_lng'));
                $event->setCommunityUser( $communityUser );
                $event->setLocationName($request->get('search_event_location'));

                $ticket = $em->getRepository('AppBundle:Ticket')->find($request->get('ticket_id'));
                $ticket->setPrice(intval($request->get('event_price')));

                $em->flush();

                return $this->redirectToRoute('user_event_mainpage', array('eventId' => $event->getId()));
            } catch (Exception $e){
                return $this->redirectToRoute('event_add_page');
            }

        }
    }
    


    /**
     * @Route("/addEvent", name="event_add_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function addPostAction(Request $request)
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
                }else{
                    return $this->redirectToRoute('event_add_page');
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

                $ticket = new Ticket();
                $ticket->setPrice(intval($request->get('event_price')));
                $ticket->setEvent($event);
                $em->persist($event);
                $em->persist($ticket);
                $em->flush();
                return $this->redirectToRoute('user_event_mainpage', array('eventId' => $event->getId()));
            } catch (Exception $e){
                return $this->redirectToRoute('event_add_page');
            }

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
            return $this->redirectToRoute('admin_event_list');
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
        return $this->redirectToRoute('admin_event_list');

    }


    /*******************************************************************************************************************
     *******************************************************************************************************************
                                                    UTIL FUNCTIONS
     *******************************************************************************************************************
     *******************************************************************************************************************
     */

    // checker functions will be here ...


}
