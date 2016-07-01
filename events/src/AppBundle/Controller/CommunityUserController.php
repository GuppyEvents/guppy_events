<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Result;
use AppBundle\Entity\UniversityUser;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/community-user", name="community_user")
 */
class CommunityUserController extends Controller
{

    /**
     * @Route("/list/{communityId}", name="community_user_list")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function communityUserListAction(Request $request,$communityId)
    {

        // get community user list with their names & emails
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
        $communityUsers = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$community));

        return $this->render(
            'AppBundle:communityuser:communityUserList.html.twig', array(
                'community' => $community,
                'communityUsers' => $communityUsers,
            )
        );
    }



    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------


}
