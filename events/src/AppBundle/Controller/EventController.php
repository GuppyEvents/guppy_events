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

                // --2.1-- Eğer böyle bir topluluk kullanıcısı varsa
                if(count($communityUser)>0){

                    //die('community user size == ' . count($communityUser));
                }else{
                    $communityUser = new CommunityUser();
                    $communityUser->setCommunity($community);
                    $communityUser->setUser($user);
                    $communityUser->setDate(new \DateTime());


                    $em->persist($communityUser);
                    $em->flush();
                    //die('community user size == 0');
                }
                
                $date = \DateTime::createFromFormat('m/d/Y', $request->get('event_date'));

                $event = new Event();
                $event->setTitle( $request->get('event_title') );
                $event->setDescription( $request->get('event_description') );
                $event->setStartDate( $date );
                $event->setMaxParticipantNum( $request->get('event_participant_count') );
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

//    /**
//     * @Route("/put/{communityId}", name="community_put")
//     * @Security("has_role('ROLE_USER')")
//     */
//    public function putAction(Request $request , $communityId)
//    {
//
//        // 1) POST OPERATION
//        if($request->getMethod() == 'POST'){
//
//            // 1.1) try to update community
//            try{
//                $em = $this->getDoctrine()->getManager();
//
//                // 1.1.1) University Repository should try to register university
//                $community = $em->getRepository('AppBundle:Community')->find($communityId);
//
//                // 1.1.2) Check University exist
//                if (!$community) {
//                    throw $this->createNotFoundException(
//                        'No product found for id '.$communityId
//                    );
//                }
//
//                // 1.1.3) Update
//                $community->setName( $request->get('community_name') );
//                $community->setDescription( $request->get('community_description') );
//                $em->flush();
//
//            } catch (Exception $e){}
//
//            // 1.2) Redirect route to university list page
//            return $this->redirectToRoute('community_list');
//        }
//
//        // 2) DEFAULT CASE
//        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
//
//        return $this->render(
//            'AppBundle:community:communityUpdate.html.twig', array(
//                'community' => $community,
//            )
//        );
//    }


//    /**
//     * @Route("/delete/{communityId}", name="community_delete")
//     * @Security("has_role('ROLE_USER')")
//     */
//    public function deleteAction($communityId)
//    {
//
//        // 1) POST OPERATION
//        // 1.1) try to delete community
//        try{
//            $em = $this->getDoctrine()->getManager();
//
//            $community = $em->getRepository('AppBundle:Community')->find($communityId);
//            if (!$community) {
//                throw $this->createNotFoundException(
//                    'No product found for id '.$communityId
//                );
//            }
//
//            $em->remove($community);
//            $em->flush();
//
//        } catch (Exception $e){}
//
//        // 1.2) Redirect route to community list page
//        return $this->redirectToRoute('community_list');
//
//    }


    /*******************************************************************************************************************
     *******************************************************************************************************************
                                                    UTIL FUNCTIONS
     *******************************************************************************************************************
     *******************************************************************************************************************
     */

    // checker functions will be here ...


}
