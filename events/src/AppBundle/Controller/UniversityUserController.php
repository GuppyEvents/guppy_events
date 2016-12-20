<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Result;
use AppBundle\Entity\University;
use AppBundle\Entity\UniversityMail;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/university-user", name="university_user")
 */
class UniversityUserController extends Controller
{

    /**
     * @Route("/list", name="university_user")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getAction(Request $request)
    {

        $universityMails = $this->getDoctrine()->getRepository('AppBundle:UniversityMail')->findAll();
        $universities = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        return $this->render(
            'AppBundle:universitymail:universityMailList.html.twig', array(
                'universityMails'=>$universityMails,
                'universities'=>$universities
            )
        );
    }


    /**
     * @Route("/remove/{universityUserId}", name="university_user_delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function removeUniversityUserAction($universityUserId){
        // 1) POST OPERATION
        // 1.1) try to delete university
        try{
            $em = $this->getDoctrine()->getManager();

            $universityUser = $em->getRepository('AppBundle:UniversityUser')->find($universityUserId);
            if (!$universityUser) {
                throw $this->createNotFoundException(
                    'No product found for id '.$universityUser
                );
            }

            $em->remove($universityUser);
            $em->flush();

        } catch (Exception $e){}

        // 1.2) Redirect route to university list page
        return $this->redirectToRoute('settings_emails');
    }


    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/s/remove", name="university_user_delete_service")
     */
    public function addEventFromFacebook(Request $request){

        // -- 1 -- Initialization
        $data = array();
        $em = $this->getDoctrine()->getManager();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to delete university user mail
        try{

            $universityUserId = $request->get('mid');
            $userUniversityMail = $em->getRepository('AppBundle:UniversityUser')->findOneBy(array('user'=>$this->getUser()->getId() , 'id'=>$universityUserId));
            if($userUniversityMail){

                $em->remove($userUniversityMail);
                $em->flush();

            }else {
                $data['error_msg'] = 'Kullanıcı için mail adresi bulunamadı';
                $response->setContent(json_encode(Result::$SUCCESS_EMPTY->setContent( $data )));
                return $response;
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

}