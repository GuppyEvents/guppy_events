<?php

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\Community;
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
 * @Route("/community", name="community")
 */
class CommunityController extends Controller
{

    /**
     * @Route("/home/{communityId}", name="user_community_homepage")
     */
    public function communityHomeAction($communityId)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findEventsByCommunityId($communityId);

        $community->link_facebook = null;
        $community->link_twitter = null;
        $community->link_instagram = null;
        $communityLinks = $this->getDoctrine()->getRepository('AppBundle:CommunityLink')->findCommunitySocialNetworksByCommunityId($community->getId());
        foreach ($communityLinks as $communityLink){
            switch ($communityLink->getSocialNetwork()->getId()){
                case 5001:
                    $community->link_facebook = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                    break;
                case 5002:
                    $community->link_twitter = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                    break;
                case 5003:
                    $community->link_instagram = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                    break;
                default:
                    break;
            }
        }

        $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$this->getUser()));

        $data['community'] = $community;
        $data['eventList'] = $eventList;
        $data['communityUser'] = $communityUser;

        return $this->render('AppBundle:community:communityHome.html.twig' , $data);
    }



    /**
     * @Route("/events/{communityId}", name="user_community_events_homepage")
     */
    public function communityEventsAction($communityId)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findEventsByCommunityId($communityId);

        if($this->getUser()){
            foreach ($eventList as $event){
                $eventUser = $this->getDoctrine()->getRepository('AppBundle:EventUserRating')->findOneBy(array('user'=>$this->getUser()->getId() , 'event'=>$event->getId()));
                $event->is_saved = $eventUser ? $eventUser->getIsSaved() : false;
            }
        }

        $data['community'] = $community;
        $data['eventList'] = $eventList;
        return $this->render('AppBundle:community:communityEvents.html.twig' , $data);
    }


    /**
     * @Route("/users/{communityId}", name="user_community_users_homepage")
     */
    public function communityUsersAction($communityId)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);

        $data['community'] = $community;
        return $this->render('AppBundle:community:communityUserList.html.twig' , $data);
    }



    /**
     * @Route("/list", name="community_list")
     * @Security("has_role('ROLE_USER')")
     */
    public function listAction(Request $request)
    {
        $selectedUniversityId = null;
        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findAll();
        $universityList = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){
            $em = $this->getDoctrine()->getManager();

            try{
                $selectedUniversityId = $request->get('university_id');

                // eğer universite id değeri varsa o universitenin topluluk listesi getir
                if($selectedUniversityId && $this->getDoctrine()->getRepository('AppBundle:University')->find($request->get('university_id'))){
                    $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy(array('university'=>$request->get('university_id')));
                    $communityIdList = array();
                    for($i=0; $i<count($communityList);$i++){
                        array_push($communityIdList ,$communityList[$i]->getId());
                    }
                }

            } catch (Exception $e){

            }
        }

        return $this->render(
            'AppBundle:community:communityList.html.twig', array(
                'communityList'=>$communityList,
                'universityList'=>$universityList,
                'selectedUniversityId'=>$selectedUniversityId
            )
        );
    }



    /**
     * @Route("/post", name="community_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function postAction(Request $request)
    {
        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getManager();

            try{

                // 1.1) University Repository should try to register university
                $university = $em->getRepository('AppBundle:University')->find($request->get('university_id'));

                $community = new Community();
                $community->setName( $request->get('community_name') );
                $community->setDescription( $request->get('community_description') );
                $community->setImageBase64($request->get('community_image_base64'));
                $community->setImageBackgroundBase64($request->get('community_image_background_base64'));
                $community->setUniversity( $university );

                $em->persist($community);
                $em->flush();

            } catch (Exception $e){}

            // Redirect route to university list page
            return $this->redirectToRoute('community_list');
        }


        // 2) DEFAULT CASE
        $universities = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        return $this->render(
            'AppBundle:community:communityRegister.html.twig', array(
                'universities'=>$universities
            )
        );
    }



    /**
     * @Route("/put/{communityId}", name="community_put")
     * @Security("has_role('ROLE_USER')")
     */
    public function putAction(Request $request , $communityId)
    {

        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            // 1.1) try to update community
            try{
                $em = $this->getDoctrine()->getManager();

                // 1.1.1) University Repository should try to register university
                $community = $em->getRepository('AppBundle:Community')->find($communityId);

                // 1.1.2) Check University exist
                if (!$community) {
                    throw $this->createNotFoundException(
                        'No product found for id '.$communityId
                    );
                }

                // 1.1.3) Update
                $community->setName( $request->get('community_name') );
                $community->setDescription( $request->get('community_description') );
                $community->setImageBase64($request->get('community_image_base64'));
                $community->setImageBackgroundBase64($request->get('community_image_background_base64'));
                
                $em->persist($community);
                $em->flush();

            } catch (Exception $e){}

            // 1.2) Redirect route to university list page
            return $this->redirectToRoute('community_list');
        }

        // 2) DEFAULT CASE
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
        $communityLinkList = $this->getDoctrine()->getRepository('AppBundle:CommunityLink')->findCommunitySocialNetworksByCommunityId($community->getId());

        return $this->render(
            'AppBundle:community:communityUpdate.html.twig', array(
                'community' => $community,
                'communityLinkList' => $communityLinkList
            )
        );
    }



    /**
     * @Route("/delete/{communityId}", name="community_delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction($communityId)
    {

        // 1) POST OPERATION
        // 1.1) try to delete community
        try{
            $em = $this->getDoctrine()->getManager();

            $community = $em->getRepository('AppBundle:Community')->find($communityId);
            if (!$community) {
                throw $this->createNotFoundException(
                    'No product found for id '.$communityId
                );
            }

            $em->remove($community);
            $em->flush();

        } catch (Exception $e){}

        // 1.2) Redirect route to community list page
        return $this->redirectToRoute('community_list');

    }


    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/add-event-from-fb", name="community_add_event_from_facebook")
     */
    public function addEventFromFacebook(Request $request){

        /**
         * -- -- READ ME -- --
         * Bu fonksiyon, etkinliklerin facebook üzerinden alınması için yazılmaya başlanmış olup yarıda bırakılmıştır.
         */

        // -- 1 -- Initialization
        $data = array();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to Add New Mail Server
        try{

            // -- 2.1 -- Get Community id
            $communityId = $request->get('cid');

            $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);

            $facebook_pattern = '/^((http(s)?:\/\/)?(w{3}\.)?)?(facebook.com\/){1}.*/';
            $twitter_pattern = '/^((http(s)?:\/\/)?(w{3}\.)?)?(twitter.com\/){1}.*/';
            $instagram_pattern = '/^((http(s)?:\/\/)?(w{3}\.)?)?(instagram.com\/){1}.*/';

            $communityLinkList = $this->getDoctrine()->getRepository('AppBundle:CommunityLink')->findBy(array('community'=>$community->getId()));
            foreach ($communityLinkList as &$communityLink) {
                if(preg_match($facebook_pattern,$communityLink->getLink())){
                    $data['facebook'] = $communityLink->getLink();
                }else if(preg_match($twitter_pattern,$communityLink->getLink())){
                    $data['twitter'] = $communityLink->getLink();
                }else if(preg_match($instagram_pattern,$communityLink->getLink())){
                    $data['instagram'] = $communityLink->getLink();
                }
            }


//            $this->getDoctrine()->getManager()->persist();
//            $this->getDoctrine()->getManager()->flush();

            $data['id'] = $community->getId();
            $data['name'] = $community->getName();
            $data['facebookEvents'] = $data['facebook'].'events';


//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL, $data['facebookEvents']);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            $response = curl_exec($ch);
//            $data['facebookEventsRespond'] = $response;

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
