<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Ticket;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Entity\City;
use AppBundle\Entity\Community;
use AppBundle\Entity\CommunityUser;
use AppBundle\Entity\Event;
use AppBundle\Entity\Result;
use AppBundle\Entity\Address;
use AppBundle\Entity\University;




/**
 * @Route("/ticket", name="ticket")
 * @Security("has_role('ROLE_ADMIN')")
 */
class TicketController extends Controller
{

    /**
     * @Route("/post", name="ticket_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function postAction(Request $request)
    {
        // -- 1 -- Initialization
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $em = $this->getDoctrine()->getEntityManager();

        // -- 2 -- Try to Add New Ticket To Given Event
            try{

            // -- 2.1 -- Get parameters
                $eventId = $request->get('eid');
                $ticketPrice = $request->get('t_price');

                $event = $em->getRepository('AppBundle:Event')->find($eventId);
                $ticket = new Ticket();
                $ticket->setPrice(intval($ticketPrice));
                $ticket->setEvent($event);

                $em->persist($ticket);
                $em->flush();

                $ticketRes = array();
                $ticketRes['id'] = $ticket->getId();
                $ticketRes['ticketprice'] = $ticket->getPrice();

            // -- 2.2 -- Return Result
                $response->setContent(json_encode(Result::$SUCCESS->setContent( $ticketRes )));
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


    /**
     * @Route("/delete", name="ticket_delete")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request)
    {
        // -- 1 -- Initialization
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $em = $this->getDoctrine()->getManager();

        // -- 2 -- Try to Add New Mail Server
            try{

                $ticketId = $request->get('tid');

                $ticket = $em->getRepository('AppBundle:Ticket')->find($ticketId);
                if (!$ticket) {
                    throw $this->createNotFoundException(
                        'No product found for id '.$ticket
                    );
                }

                $em->remove($ticket);
                $em->flush();

                // -- 2.2 -- Return Result
                $response->setContent(json_encode(Result::$SUCCESS->setContent( 'Ticket removed!' )));
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
