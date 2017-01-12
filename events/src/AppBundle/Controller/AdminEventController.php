<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Community;

/**
 * @Route("/admin/event", name="admin_event")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminEventController extends Controller
{


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
