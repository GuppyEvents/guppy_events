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

        // İlgili kullanıcının topluluğun yöneticisi olup olunmadığına bakılır
        $isUserAdmin = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$community , 'user'=>$this->getUser() , 'status'=>1));
        $data['userIsAdmin'] = $isUserAdmin and count($isUserAdmin)>0 ? true : false;

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

        // İlgili kullanıcının topluluğun yöneticisi olup olunmadığına bakılır
        $isUserAdmin = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$community , 'user'=>$this->getUser() , 'status'=>1));
        $data['userIsAdmin'] = $isUserAdmin and count($isUserAdmin)>0 ? true : false;

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

        if($community){
            $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$community));
            $acceptState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findAcceptState();
            $communityUserRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser, 'state' => $acceptState));
            $data['communityUserList'] = $communityUserRole;
            $dates= array();

            foreach($communityUserRole as $comm){
                $dates[(string)$comm->getRegisterDate()->getTimestamp() * 1000] = 1;
            }
            $data['registerDates'] = $dates;

            //BAŞVURULABİLECEK ROLLERİ GETİR
            $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityRole')->findAll();
            $data["userRoles"] = $communityUserRoles;
            // İlgili kullanıcının topluluğun yöneticisi olup olunmadığına bakılır
            $isUserAdmin = $this->getDoctrine()->getRepository('AppBundle:User')->isUserAdmin($this->getUser(), $community, $this);
            $data['userIsAdmin'] = $isUserAdmin;
        }

        $data['community'] = $community;
        return $this->render('AppBundle:community:communityUserList.html.twig' , $data);
    }


    /**
     * @Route("/membership-applications/{communityId}", name="user_community_membership_applications_homepage")
     * @Security("has_role('ROLE_USER')")
     */
    public function communityMembershipApplicationsAction($communityId)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
        if($community){
            // İlgili kullanıcının topluluğun yöneticisi olup olunmadığına bakılır
            $isUserAdmin = $this->getDoctrine()->getRepository('AppBundle:User')->isUserAdmin($this->getUser(), $community, $this);

            $data['userIsAdmin'] = $isUserAdmin;


            $pendingState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findPendingState();
            $communityUsers = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$community));

            // Topluluğun üyelik başvurusu yapanların listesi alınır
            $communityMembershipApplications = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUsers , 'state'=>$pendingState ));
            $data['communityMembershipApplications'] = $communityMembershipApplications;
        }

        $data['community'] = $community;
        return $this->render('AppBundle:community:communityMembershipApplicationList.html.twig' , $data);
    }
    


    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @Route("/applications/confirm", name="user_community_membership_applications_confirm")
     * @Security("has_role('ROLE_USER')")
     */
    public function communityMembershipApplicationsConfirmAction(Request $request)
    {
        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $data = array();

        // -- 2 --
        try{

            // -- 2.1 -- Get parameters
            $operation = $request->get('operation');
            $reason = $request->get('reason');
            $communityUserRoleId = $request->get('communityUserRoleId');

            $communityUserRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->find($communityUserRoleId);


            // İlgili kullanıcının topluluğun yöneticisi olup olunmadığına bakılır
            $IsCommunityUserAdmin = $this->getDoctrine()->getRepository('AppBundle:User')->isUserAdmin($this->getUser(), $communityUserRole->getCommunityUser()->getCommunity(), $this);

            // -- 2.2 -- Checkers
            if (!$IsCommunityUserAdmin) {
                $response->setContent(json_encode(Result::$FAILURE_PERMISSION->setContent('Yetki yok')));
                return $response;
            }

            if (!$communityUserRole) {
                $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent('Community User Role bulunamadi')));
                return $response;
            }
            $pendingState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findPendingState();
            if ($communityUserRole->getState() != $pendingState) {
                $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent('Uye durumu uygun degil')));
                return $response;
            }

            $acceptState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findAcceptState();
            $rejectState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findRejectState();
            switch ($operation){
                case 'confirm':
                    $communityUserRole->setState($acceptState);
                    $data['success_msg'] = 'Üye başarıyla onaylandı';
                    break;
                case 'reject':
                    $communityUserRole->setState($rejectState);
                    $communityUserRole->setDescription($reason);
                    $data['success_msg'] = 'Üye başarıyla reddedildi';
                    break;
                default:
                    break;
            }

            $communityUserRole->setUpdateDate(new \DateTime('now'));
            $communityUserRole->setPerformBy($this->getUser());

            $this->getDoctrine()->getManager()->persist($communityUserRole);
            $this->getDoctrine()->getManager()->flush();


            // -- 2.2 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent($data)));
            return $response;

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
     * @Route("/applications/leave", name="user_community_membership_leave")
     * @Security("has_role('ROLE_USER')")
     */
    public function communityLeaveAction(Request $request)
    {
        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $data = array();

        // -- 2 --
        try{

            // -- 2.1 -- Get parameters
            $communityUserId = $request->get('communityUserId');
            $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->find($communityUserId);

            // -- 2.2 -- Checkers
            if (!$communityUser) {
                $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent('Community User bulunamadi')));
                return $response;
            }

            if ($communityUser->getUser()->getId() != $this->getUser()->getId()) {
                $response->setContent(json_encode(Result::$FAILURE_PERMISSION->setContent('Yetkiniz bulunmamaktadır')));
                return $response;
            }

            $communityUser->setStatus($communityUser->getStatus() + 1000);

            $this->getDoctrine()->getManager()->persist($communityUser);
            $this->getDoctrine()->getManager()->flush();
            $data['success_msg'] = 'Gruptan başarılı bir şekilde ayrıldınız';

            // -- 2.2 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent($data)));
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
