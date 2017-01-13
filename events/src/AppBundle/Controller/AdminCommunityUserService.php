<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CommunityUser;
use AppBundle\Entity\Result;
use AppBundle\Entity\UniversityUser;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/admin/service/community-user", name="admin_service_community_user")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminCommunityUserService extends Controller
{

    /**
     * @Route("confirm", name="admin_service_community_user_confirm")
     */
    public function communityUserConfirmService(Request $request)
    {
        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $data = array();
        $em = $this->getDoctrine()->getManager();

        try {
            // --1.1-- Get post parameter
            $communityId = $request->get('cid');
            $userId = $request->get('uid');
            $status = $request->get('status');

            // --1.2-- try to get community user
            $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
            $user = $em->getRepository('AppBundle:User')->find($userId);
            $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$user));

            // --2.1-- Checker
            if(!$community || !$user || !$status || !$communityUser){
                $response->setContent(json_encode(Result::$FAILURE_PARAM_MISMATCH->setContent('NULL error')));
                return $response;
            }

            // --3.1-- Update operation
            $prevStatus = $communityUser->getStatus();
            $communityUser->setUpdateDate(new \DateTime('now'));
            $communityUser->setStatus(intval($status));
            $communityUser->setPerformBy($this->getUser());

            $em->persist($communityUser);
            $em->flush();

            $data['msg'] = $user->getName() . ' role updated from ' . strval($prevStatus) . ' to ' . strval($status) . ' at ' . $community->getName();
            $data['status'] = $communityUser->getStatus();

            $response->setContent(json_encode(Result::$SUCCESS->setContent($data)));
            return $response;

        } catch (\Exception $ex){
            // content == "Unexpected Error"
            $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent($ex)));
            return $response;
        }

        // -- 3 -- Set & Return value
        $response->setContent(json_encode(Result::$SUCCESS_EMPTY));
        return $response;
    }


}
