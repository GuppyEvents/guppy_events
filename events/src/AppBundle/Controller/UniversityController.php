<?php

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\Result;
use AppBundle\Entity\Address;
use AppBundle\Entity\University;
use AppBundle\Entity\UniversityMail;
use AppBundle\Form\UniversityType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/university", name="university")
 */
class UniversityController extends Controller
{


    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/add-mail", name="university_add_mailserver")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addMailServer(Request $request){

        // -- 1 -- Initialization
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to Add New Mail Server
            try{

            // -- 2.1 -- Get borough list
                $universityId = $request->get('uid');
                $mailName = $request->get('mailServer');

                $university = $this->getDoctrine()->getRepository('AppBundle:University')->find($universityId);
                $universityMail = new UniversityMail();
                $universityMail->setHostname($mailName);
                $universityMail->setUniversity($university);

                $this->getDoctrine()->getManager()->persist($universityMail);
                $this->getDoctrine()->getManager()->flush();

                $mail = array();
                $mail['id'] = $universityMail->getId();
                $mail['name'] = $universityMail->getHostname();

            // -- 2.2 -- Return Result
                $response->setContent(json_encode(Result::$SUCCESS->setContent( $mail )));
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
     * @Route("/remove-mail", name="university_remove_mailserver")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeMailServer(Request $request){

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to Add New Mail Server
        try{

            // -- 2.1 -- Get borough list
            $universityId = $request->get('uid');
            $universityMailId = $request->get('mid');

            $em = $this->getDoctrine()->getManager();

            $universityMail = $em->getRepository('AppBundle:UniversityMail')->find($universityMailId);
            if (!$universityMail) {
                throw $this->createNotFoundException(
                    'No product found for id '.$universityMail
                );
            }

            $em->remove($universityMail);
            $em->flush();

            // -- 2.2 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent( 'University mail server removed!' )));
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
     * @Route("/borough-list", name="university_borough_list")
     * @Security("has_role('ROLE_USER')")
     */
    public function getBoroughAction(Request $request)
    {

        // -- 1 -- Initialization
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to Get Borough List
            try{

            // -- 2.1 -- Get borough list
                $city_id = $request->get('cid');
                $boroughList = $this->getDoctrine()
                    ->getRepository('AppBundle:Borough')
                    ->findBy(array('cityId'=>$city_id));

                $boroughResponse = array();
                for($i=0; $i<count($boroughList);$i++){
                    $borough['borough_name'] = $boroughList[$i]->getName();
                    $borough['borough_id'] = $boroughList[$i]->getId();
                    array_push($boroughResponse,$borough);
                }

            // -- 2.1 -- Return Result
                $response->setContent(json_encode(Result::$SUCCESS->setContent( $boroughResponse )));
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
     * @Route("/community-list", name="university_community_list")
     * @Security("has_role('ROLE_USER')")
     */
    public function getCommunityListAction(Request $request)
    {

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to Get Borough List
        try{

            // -- 2.1 -- Get borough list
            $university_id = $request->get('uid');
            $communityList = $this->getDoctrine()
                ->getRepository('AppBundle:Community')
                ->findBy(array('university'=>$university_id));

            $communityResponse = array();
            for($i=0; $i<count($communityList);$i++){
                $community['community_name'] = $communityList[$i]->getName();
                $community['community_id'] = $communityList[$i]->getId();
                array_push($communityResponse,$community);
            }

            // -- 2.1 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent( $communityResponse )));
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
