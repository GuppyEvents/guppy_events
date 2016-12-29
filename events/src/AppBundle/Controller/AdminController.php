<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

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

        $communityUserList = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findAllExceptAdmin();

        $data['communityUserList'] = $communityUserList;

        return $this->render('AppBundle:admin:communityUserList.html.twig', $data);
    }
    
}
