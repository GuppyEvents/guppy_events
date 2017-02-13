<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CommunityRole;
use AppBundle\Entity\CommunityUser;
use AppBundle\Entity\CommunityUserRoles;
use AppBundle\Entity\CommunityUserRoleState;
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

    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES (ALL)
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @Route("/s/add", name="service_community_user_add")
     */
    public function communityUserAddAction(Request $request)
    {

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $em = $this->getDoctrine()->getManager();
        $data = array();

        try {


            if(!$this->getUser()){
                // login olmama hatası döner
                $response->setContent(json_encode(Result::$FAILURE_AUTH->setContent('Giriş yapmanız gerekmektedir')));
                return $response;
            }

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
                $communityUser->setRegisterDate(new \DateTime('now'));

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
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/s/apply-role", name="service_apply_role")
     */
    public function applyToRole(Request $request){

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $data = array();
        $em = $this->getDoctrine()->getManager();

        // -- 2 -- Try to Get More Community Result
        try{

            if(!$this->getUser()){
                // login olmama hatası döner
                $response->setContent(json_encode(Result::$FAILURE_AUTH->setContent('Giriş yapmanız gerekmektedir')));
                return $response;
            }
            
            // -- 2.1 -- Get parameter list
            $roleId= $request->get('roleId');
            $communityId = $request->get('communityId');
            $communityRole = $this->getDoctrine()->getRepository('AppBundle:CommunityRole')->find($roleId);
            $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);

            // --2.2-- try to get community user
            $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$this->getUser()));


            // --2.3-- Eğer daha önce topluluk için başvuru olmadıysa && uygun topluluk varsa
            if(!$communityUser && $community){
                $communityUser = new CommunityUser();
                $communityUser->setUser($this->getUser());
                $communityUser->setCommunity($community);
                $communityUser->setRegisterDate(new \DateTime('now'));
                $em->persist($communityUser);
                $em->flush();

            }


            //daha önce başvurmuş mu kontrol et
            $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser, 'communityRole'=>$roleId));
            $deniedState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findRejectState();
            $pendingState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findPendingState();

            foreach($communityUserRoles as $role){

                if($role->getState()->getId() != $deniedState->getId()){
                    //bekleyen veya onaylanmış istek oldugu için hata dön
                    $response->setContent(json_encode(Result::$FAILURE_DATABASE_DUPLICATE));
                    return $response;
                }else{
                    $role->setState($pendingState);
                    $em->persist($role);
                    $em->flush();
                    $response->setContent(json_encode(Result::$SUCCESS));
                    return $response;
                }

            }


            $communityUserRole = new CommunityUserRoles();
            $communityUserRole->setCommunityUser($communityUser);
            $communityUserRole->setCommunityRole($communityRole);
            $communityUserRole->setState($pendingState);
            $communityUserRole->setPerformBy($this->getUser());

            $em->persist($communityUserRole);
            $em->flush();

            // -- 2.2 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent( $communityUserRole )));
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
