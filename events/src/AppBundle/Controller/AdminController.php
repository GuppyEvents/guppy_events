<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Result;

/**
 * @Route("/admin", name="admin")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminController extends Controller
{

    /**
     * @Route("/", name="admin_homepage")
     */
    public function indexAction(Request $request)
    {
        $data = array();
        $universityList = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();
        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findAll();
        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findAll();
        $userList = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();

        $communityAdminUserCount = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findCommunityAdminCount();
        $communityMemberUserCount = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findCommunityMembersCount();
        $communityUserRequestCount = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findCommunityUserRequestCount();

        $data['university_count'] = count($universityList);
        $data['community_count'] = count($communityList);
        $data['event_count'] = count($eventList);
        $data['user_count'] = count($userList);
        $data['community_user_admin_count'] = count($communityAdminUserCount);
        $data['community_user_member_count'] = count($communityMemberUserCount);
        $data['community_user_request_count'] = count($communityUserRequestCount);

        return $this->render('AppBundle:admin:index.html.twig', $data);
    }


    /**
     * @Route("/community-user-list", name="admin_community_user_list")
     */
    public function communityUserListAction(Request $request)
    {
        $data = array();


        $pendingState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findPendingState();
        $acceptState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findAcceptState();
        $rejectState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findRejectState();
        $communityUsers = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findAll();
        $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUsers ));

        $data['statePending'] = $pendingState;
        $data['stateAccept'] = $acceptState;
        $data['stateReject'] = $rejectState;
        $data['communityUserRoles'] = $communityUserRoles;

        return $this->render('AppBundle:admin:communityuser_list.html.twig', $data);
    }


    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("community/applications/confirm", name="admin_user_community_membership_applications_confirm")
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
            $communityUserRoleId = $request->get('communityUserRoleId');

            $communityUserRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->find($communityUserRoleId);

            if (!$communityUserRole) {
                $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent('Community User Role not found')));
                return $response;
            }
            $pendingState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findPendingState();
            if ($communityUserRole->getState() != $pendingState) {
                $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent('Member state not proper')));
                return $response;
            }

            $acceptState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findAcceptState();
            $rejectState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findRejectState();
            switch ($operation){
                case 'confirm':
                    $communityUserRole->setState($acceptState);
                    $data['success_msg'] = 'Role succefully accepted';
                    $data['roleState'] = 'accepted';
                    break;
                case 'reject':
                    $communityUserRole->setState($rejectState);
                    $data['success_msg'] = 'Role succefully rejected';
                    $data['roleState'] = 'rejected';
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
     * @Route("community/publish", name="admin_community_publish_service")
     */
    public function communityPublishAction(Request $request)
    {
        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $data = array();

        // -- 2 --
        try{

            // -- 2.1 -- Get parameters
            $operation = $request->get('operation');
            $communityId = $request->get('cid');

            $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
            if (!$community) {
                $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent('Community not found')));
                return $response;
            }

            switch ($operation){
                case 'publish':
                    $community->setIsApproved(true);
                    $community->setUpdateDate((new \DateTime('now')));
                    //işlemi kimin gerçekleştirdiği loglanmalı ya da veri tabanında tutulmalı
                    //$community->setPerformBy($this->getUser());
                    $data['success_msg'] = 'Community state changed to publish';
                    $data['publishState'] = 'accepted';
                    break;
                default:
                    break;
            }

            $this->getDoctrine()->getManager()->persist($community);
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
    
}
