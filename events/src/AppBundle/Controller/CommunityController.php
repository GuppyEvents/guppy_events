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
 * @Route("/topluluk", name="community")
 */
class CommunityController extends Controller
{

    /**
     * @Route("/bilgi/{communityId}", name="user_community_homepage")
     */
    public function communityHomeAction($communityId)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);

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
        $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser));

        // İlgili kullanıcının topluluğun yöneticisi olup olunmadığına bakılır
        $data['isUserCommunityAdmin'] = $this->getUser() ? $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser(),$community) : false;
        $data['userCommunityUserAdminRole'] = $this->getUser() ? $this->getDoctrine()->getRepository('AppBundle:User')->getUserCommunityUserAdminRole($this->getUser(),$community) : null;

        $data['community'] = $community;
        $data['communityUser'] = $communityUser;

        $data['communityUserRoles'] = $communityUserRoles;

        return $this->render('AppBundle:community:communityHome.html.twig' , $data);
    }



    /**
     * @Route("/etkinlik/{communityId}", name="user_community_events_homepage")
     */
    public function communityEventsAction($communityId)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByCommunityId($communityId);

        foreach ($eventList as $event){
            $ticket = $this->getDoctrine()->getRepository('AppBundle:Ticket')->findLowestPriceTicketByEventId($event->getId());
            if($this->getUser()) {
                $eventUser = $this->getDoctrine()->getRepository('AppBundle:EventUserRating')->findOneBy(array('user'=>$this->getUser()->getId() , 'event'=>$event->getId()));
                $event->is_saved = $eventUser ? $eventUser->getIsSaved() : false;
            }
            $event->ticket_price = $ticket && $ticket->getPrice()>0 ? $ticket->getPrice() . ' TL' : null;
        }

        $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$this->getUser()));
        $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser));
        $data['communityUserRoles'] = $communityUserRoles;

        $data['community'] = $community;
        $data['eventList'] = $eventList;
        $data['isUserCommunityAdmin'] = $this->getUser() ? $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser(),$community) : false;
        
        return $this->render('AppBundle:community:communityEvents.html.twig' , $data);
    }


    /**
     * @Route("/uyeler/{communityId}", name="user_community_users_homepage")
     */
    public function communityUsersAction($communityId)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);

        if($community){
            $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$community));
            $acceptState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findAcceptState();
            $communityUserRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser, 'state' => $acceptState));
            $data['communityAllUserRoles'] = $communityUserRole;
            $dates= array();

            foreach($communityUserRole as $comm){
                $dates[(string)$comm->getRegisterDate()->getTimestamp() * 1000] = 1;
            }
            $data['registerDates'] = $dates;

            //BAŞVURULABİLECEK ROLLERİ GETİR
            $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityRole')->findAll();
            $data["userRoles"] = $communityUserRoles;

            if($this->getUser()){
                $userRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$community , 'user'=>$this->getUser()));
                $communityUserRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findOneBy(array('communityUser'=>$userRole, 'state' => $acceptState));
                $data['userRole'] = $communityUserRole;
            }

            // İlgili kullanıcının topluluğun yöneticisi olup olunmadığına bakılır
            $data['isUserCommunityAdmin'] = $this->getUser() ? $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser(),$community) : false;

            $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$this->getUser()));
            $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser));
            $data['communityUserRoles'] = $communityUserRoles;
        }

        $data['community'] = $community;
        return $this->render('AppBundle:community:communityUserList.html.twig' , $data);
    }


    /**
     * @Route("/uye-basvurulari/{communityId}", name="user_community_membership_applications_homepage")
     * @Security("has_role('ROLE_USER')")
     */
    public function communityMembershipApplicationsAction($communityId)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
        if($community){
            // İlgili kullanıcının topluluğun yöneticisi olup olunmadığına bakılır
            $data['isUserCommunityAdmin'] = $this->getUser() ? $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser(),$community) : false;

            $pendingState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findPendingState();
            $communityUsers = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$community));

            // Topluluğun üyelik başvurusu yapanların listesi alınır
            $communityMembershipApplications = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUsers , 'state'=>$pendingState ));
            $data['communityMembershipApplications'] = $communityMembershipApplications;

            $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$this->getUser()));
            $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser));
            $data['communityUserRoles'] = $communityUserRoles;
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
     * @Route("/s/event-list-by-community", name="service_event_list_by_community")
     */
    public function getMoreEvent(Request $request){

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $data = array();

        // -- 2 -- Try to Get More Event Result
        try{

            // -- 2.1 -- Get parameter list
            $id = intval( $request->get('id'));
            $page = intval($request->get('page'));
            $pageSize = $request->get('pageSize') ? intval($request->get('pageSize')) : 12;

            $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByCommunityId($id,$page,$pageSize);

            $data['eventList'] = array();
            foreach ($eventList as $event) {

                $eventObj = array();
                $eventObj['id'] = $event->getId();

                $eventObj['title'] = $event->getTitle();
                $eventObj['imageBase64'] = $event->getImageBase64();
                
                $eventObj['startDate'] = $event->getStartDate();

                foreach ($eventList as $event){
                    $ticket = $this->getDoctrine()->getRepository('AppBundle:Ticket')->findLowestPriceTicketByEventId($event->getId());
                    if($this->getUser()) {
                        $eventUser = $this->getDoctrine()->getRepository('AppBundle:EventUserRating')->findOneBy(array('user'=>$this->getUser()->getId() , 'event'=>$event->getId()));
                        $eventObj['is_saved'] = $eventUser ? $eventUser->getIsSaved() : false;
                    }
                    $eventObj['ticket_price'] = $ticket && $ticket->getPrice()>0 ? $ticket->getPrice() . ' TL' : null;
                }
                
                array_push($data['eventList'], $eventObj);
            }

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
            $communityUserRolesId = $request->get('curi');
            $communityUserRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->find($communityUserRolesId);

            // -- 2.2 -- Checkers
            if (!$communityUserRole) {
                $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent('Kullanıcının böyle bir rolü bulunamadı')));
                return $response;
            }

            if ($communityUserRole->getCommunityUser() && $communityUserRole->getCommunityUser()->getUser()->getId() != $this->getUser()->getId()) {
                $response->setContent(json_encode(Result::$FAILURE_PERMISSION->setContent('Yetkiniz bulunmamaktadır')));
                return $response;
            }

            $this->getDoctrine()->getManager()->remove($communityUserRole);
            $this->getDoctrine()->getManager()->flush();
            $data['success_msg'] = 'Gruptan ayrıldınız :(';

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
     * @Route("/edit/about", name="user_community_edit")
     * @Security("has_role('ROLE_USER')")
     */
    public function communityUpdateAction(Request $request)
    {
        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $logger = $this->get('monolog.logger.graylog');
        $data = array();
        $errorData = array();

        // -- 2 --
        try{

            // -- 2.1 -- Get parameters
            $communityUserRolesId = $request->get('curi');
            $description = $request->get('cDesc');
            $communityUserRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->find($communityUserRolesId);

            // -- 2.2 -- Checkers
            if (!$communityUserRole) {
                $errorData['user'] = $this->getUser()->getId();
                $errorData['communtiy_user_role_id'] = $communityUserRolesId;
                $errorData['description'] = 'User community role not found';
                $logger->addWarning(json_encode(Result::$FAILURE_EXCEPTION->setContent($errorData)));

                $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent('Kullanıcı rolü bulunamadı')));
                return $response;
            }

            $community = $communityUserRole->getCommunityUser()->getCommunity();    // rolün topluluğu
            if (!$community) {
                $errorData['user'] = $this->getUser()->getId();
                $errorData['communtiy_user_role_id'] = $communityUserRolesId;
                $errorData['description'] = 'Community not found';
                $logger->addWarning(json_encode(Result::$FAILURE_EXCEPTION->setContent($errorData)));

                $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent('Rolünüze ait bir topluluk bulunamadı')));
                return $response;
            }

            // Kullanıcının ilgili topluluk için yetkisi olunup olunmadığına bakılır
            if ($communityUserRole->getCommunityUser()->getUser()->getId() != $this->getUser()->getId()) {
                $errorData['user'] = $this->getUser()->getId();
                $errorData['communtiy_user_role_id'] = $communityUserRolesId;
                $errorData['description'] = __LINE__;
                $logger->addWarning(json_encode(Result::$FAILURE_PERMISSION->setContent($errorData)));

                $response->setContent(json_encode(Result::$FAILURE_PERMISSION->setContent('Yetkiniz bulunmamaktadır')));
                return $response;
            }

            $acceptState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findAcceptState();
            if($communityUserRole->getState()->getId() != $acceptState->getId()){
                $errorData['user'] = $this->getUser()->getId();
                $errorData['communtiy_user_role_id'] = $communityUserRolesId;
                $errorData['description'] = 'User role has not accept state';
                $errorData['line'] = __LINE__;
                $logger->addWarning(json_encode(Result::$FAILURE_PERMISSION->setContent($errorData)));

                $response->setContent(json_encode(Result::$FAILURE_PERMISSION->setContent('Rolünüz onaylanmadığından yetkiniz bulunmamaktadır')));
                return $response;
            }
            
            $adminRole = $this->getDoctrine()->getRepository('AppBundle:CommunityRole')->findAdminRole();
            if($communityUserRole->getCommunityRole()->getId() != $adminRole->getId()){
                $errorData['user'] = $this->getUser()->getId();
                $errorData['communtiy_user_role_id'] = $communityUserRolesId;
                $errorData['description'] = 'User has not admin role';
                $errorData['line'] = __LINE__;
                $logger->addWarning(json_encode(Result::$FAILURE_PERMISSION->setContent($errorData)));
                $response->setContent(json_encode(Result::$FAILURE_PERMISSION->setContent('Yönetici yetkiniz bulunmamaktadır')));
                return $response;
            }

            $community->setDescription($description);
            $this->getDoctrine()->getManager()->persist($community);
            $this->getDoctrine()->getManager()->flush();

            // -- 2.2 -- Return Result
            $data['success_msg'] = 'Topluluk bilgisi düzenlendi';
            $response->setContent(json_encode(Result::$SUCCESS->setContent($data)));
            return $response;

        }catch (\Exception $ex){
            // content == "Unexpected Error"
            $logger->addWarning(json_encode(Result::$FAILURE_EXCEPTION->setContent($ex->getMessage())));
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
