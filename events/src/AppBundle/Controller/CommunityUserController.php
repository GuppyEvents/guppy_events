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
    //                                          APPLICATION/JSON SERVICES (ALL)
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @Route("/s/add", name="service_community_user_add")
     * @Security("has_role('ROLE_USER')")
     */
    public function communityUserAddAction(Request $request)
    {

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $em = $this->getDoctrine()->getManager();
        $data = array();

        try {
            // --1.1-- Get post parameter
            $communityId = $request->get('cid');

            // --1.2-- try to get community user
            $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
            $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$this->getUser()));

            // --1.3-- Eğer daha önce topluluk için başvuru olmadıysa && uygun topluluk varsa
            if(!$communityUser && $community){
                $communityUser = new CommunityUser();
                $communityUser->setUser($this->getUser());
                $communityUser->setCommunity($community);
                $communityUser->setDate(new \DateTime('now'));

                $em->persist($communityUser);
                $em->flush();

                $data['success_msg'] = 'Başvurunuz alındı.';

                $response->setContent(json_encode(Result::$SUCCESS->setContent($data)));
                return $response;
            }

        } catch (Exception $ex){
            // content == "Unexpected Error"
            $response->setContent(json_encode(Result::$FAILURE_EXCEPTION->setContent($ex)));
            return $response;
        }

        // -- 3 -- Set & Return value
        $response->setContent(json_encode(Result::$SUCCESS_EMPTY));
        return $response;
    }


    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES (ADMIN)
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/s/confirm", name="service_community_user_confirm")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function communityUserConfirmAction(Request $request)
    {

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $em = $this->getDoctrine()->getManager();
        $data = array();

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
            // TODO: updated date değeri eklenmesi ya da update edilmesi gerekiyor
            //$communityUser->setDate(new \DateTime('now'));

            $prevStatus = $communityUser->getStatus();
            $communityUser->setStatus(intval($status));

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
