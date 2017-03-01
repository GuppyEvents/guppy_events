<?php

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\Community;
use AppBundle\Entity\CommunityUser;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventUserComment;
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
use Pusher;

/**
 * @Route("/comment", name="comment")
 */
class EventUserCommentController extends Controller
{



    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/getComments", name="event_get_comments")
     */
    public function getEventComments(Request $request)
    {
        // -- 1 -- Initialization
        $data = array();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Service Operation
        try{

            // -- 2.1 --
            $eventId = intval( $request->get('eventId'));
            $page = intval($request->get('page'));
            $pageSize = $request->get('pageSize') ? intval($request->get('pageSize')) : 20;

            // -- 2.2 --
            if($this->getUser()){

                $eventComments = $this->getDoctrine()->getRepository('AppBundle:EventUserComment')->findEventComments($eventId,$page,$pageSize);

                $data['commentList'] = array();
                foreach($eventComments as $comment){
                    $tmpComment = array();
                    $tmpComment["comment"]=$comment->getComment();
                    $tmpComment["userImage"]=$comment->getUser()->getImageBase64();
                    $tmpComment["date"]=date_format($comment->getRegisterDate(), 'Y-m-d H:i:s');
                    array_push($data['commentList'], $tmpComment);
                }
                // -- 2.2.1 -- Return Result
                $response->setContent(json_encode(Result::$SUCCESS->setContent( $data )));
                return $response;
            }else{
                // -- 2.2.2 -- Return Result
                $response->setContent(json_encode(Result::$FAILURE_AUTH));
                return $response;
            }

        }catch (\Exception $ex){
            // content == "Unexpected Error"
            $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent($ex->getMessage())));
            return $response;
        }

        // -- 3 -- Set & Return value
        $response->setContent(json_encode(Result::$SUCCESS_EMPTY));
        return $response;
    }

    /**
     * @Route("/addComment", name="event_add_comment")
     */
    public function addEventComment(Request $request)
    {
        // -- 1 -- Initialization
        $data = array();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        if($this->getUser()){
            $em = $this->getDoctrine()->getManager();

            $comment = new EventUserComment();
            $comment->setEvent($em->getReference('AppBundle:Event', $request->get('eventId')));
            $comment->setUser($this->getUser());
            $comment->setIsApproved(1);
            $comment->setComment($request->get("comment"));
            $comment->setRegisterDate(new \DateTime('now'));

            $em->persist($comment);
            $em->flush();

            if($comment->getId()){
                $data['commentUser'] = $this->getUser()->getId();
                $data['userImage'] = $this->getUser()->getImageBase64();
                $data['date'] = date_format($comment->getRegisterDate(),'Y-m-d h:i:s');
                $data['comment'] = $comment->getComment();

                $options = array(
                    'cluster' => 'eu',
                    'encrypted' => true
                );
                $pusher = new Pusher(
                    'b3db03e9b30846af735c',
                    'df141006bbee56790e2a',
                    '307736',
                    $options
                );

                $pusher->trigger('comments-'. $request->get('eventId'), 'new_comment', $data);
            }

            $response->setContent(json_encode(Result::$SUCCESS->setContent($comment->getId())));
            return $response;
        }else{
            // -- 2.2.2 -- Return Result
            $response->setContent(json_encode(Result::$FAILURE_AUTH));
            return $response;
        }

    }




    /*******************************************************************************************************************
     *******************************************************************************************************************
                                                    UTIL FUNCTIONS
     *******************************************************************************************************************
     *******************************************************************************************************************
     */

    // checker functions will be here ...


}
