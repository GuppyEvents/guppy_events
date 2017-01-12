<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Community;
use AppBundle\Entity\CommunityUser;
use AppBundle\Entity\Event;

/**
 * @Route("/admin/event", name="admin_event")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminEventController extends Controller
{

    /**
     * @Route("/post", name="admin_event_post")
     */
    public function postAction(Request $request)
    {
        // --1-- Init
        $data = array();

        // --2-- POST OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getManager();

            try{
                // --2.1-- Event may contains community
                $user = $this->getUser();
                $community = $em->getRepository('AppBundle:Community')->find($request->get('community_id'));
                $communityUser = $em->getRepository('AppBundle:CommunityUser')->findBy(array('user'=>$user->getId() , 'community'=>$community->getId()));

                // --2.2-- Eğer böyle bir topluluk kullanıcısı varsa o kullanıcı ile işlem yap
                if(count($communityUser)>0){

                    $communityUser = $communityUser[0];

                // --2.3-- Eğer böyle bir topluluk kullanıcısı yoksa kullanıcıyı kulüp ile ilişkilendir
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

            // Redirect route to event list page
            return $this->redirectToRoute('admin_event_list');
        }


        // --3-- DEFAULT CASE
        $data['universities'] = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        // --4-- RENDERING
        return $this->render('AppBundle:event:eventRegister.html.twig', $data);
    }




    /**
     * @Route("/list", name="admin_event_list")
     */
    public function listAction(Request $request)
    {
        // --1-- Init
        $data = array();
        $selectedCommunityId = null;
        $selectedUniversityId = null;
        $communityList = array();
        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findAll();
        $universityList = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        // --2-- POST OPERATION
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

            } catch (Exception $e){}
        }

        $data['eventList'] = $eventList;
        $data['universityList'] = $universityList;
        $data['communityList'] = $communityList;
        $data['selectedCommunityId'] = $selectedCommunityId;
        $data['selectedUniversityId'] = $selectedUniversityId;

        // --3-- RENDERING
        return $this->render( 'AppBundle:event:eventList.html.twig', $data);
    }

    
}
