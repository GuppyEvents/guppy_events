<?php

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\CommunityLink;
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
 * @Route("/socialnetwork", name="social_network")
 */
class SocialNetworkController extends Controller
{

    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @Route("/list", name="social_network_list")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listSocialNetworks(Request $request){

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to Get Social Network List
        try{

            $socialNetworkList = $this->getDoctrine()->getRepository('AppBundle:SocialNetwork')->findAll();

            $socialNetworkArray = array();
            foreach ($socialNetworkList as $socialNetwork){
                $socialNetworkTmp = array();
                $socialNetworkTmp['id'] = $socialNetwork->getId();
                $socialNetworkTmp['name'] = $socialNetwork->getName();
                array_push($socialNetworkArray, $socialNetworkTmp);
            }

            // -- 2.2 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent( $socialNetworkArray )));
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
