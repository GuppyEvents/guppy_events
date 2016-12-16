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
 * @Route("/community/link", name="community_link")
 */
class CommunityLinkController extends Controller
{

    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/add", name="community_link_add")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addCommunityLink(Request $request){

        // -- 1 -- Initialization
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to Add New Mail Server
            try{

            // -- 2.1 -- Get borough list
                $communityId = $request->get('cid');
                $communityLinkStr = $request->get('clink');

                $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);

                $communityLink = new CommunityLink();
                $communityLink->setLink($communityLinkStr);
                $communityLink->setCommunity($community);

                $this->getDoctrine()->getManager()->persist($communityLink);
                $this->getDoctrine()->getManager()->flush();

                $newLink = array();
                $newLink['id'] = $communityLink->getId();
                $newLink['linkname'] = $communityLink->getLink();

            // -- 2.2 -- Return Result
                $response->setContent(json_encode(Result::$SUCCESS->setContent( $newLink )));
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
     * @Route("/remove", name="community_link_remove")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeCommunityLink(Request $request){

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to Add New Mail Server
        try{

            // -- 2.1 -- Get community link
            $communityLinkId = $request->get('clid');

            $em = $this->getDoctrine()->getManager();

            $communityLink = $em->getRepository('AppBundle:CommunityLink')->find($communityLinkId);
            if (!$communityLink) {
                throw $this->createNotFoundException(
                    'No product found for id '.$communityLinkId
                );
            }

            $em->remove($communityLink);
            $em->flush();

            // -- 2.2 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent( 'Community Link removed!' )));
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
     * @Route("/list", name="community_link_list")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listCommunityLinks(Request $request){

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to Get Community Link List
        try{

            $distinctCommunityLinkType = $this->getDoctrine()->getRepository('AppBundle:CommunityLink')->findCommunityLinkTypeList();

            // -- 2.2 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent( $distinctCommunityLinkType )));
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
