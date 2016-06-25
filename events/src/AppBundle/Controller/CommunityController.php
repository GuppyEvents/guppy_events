<?php

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\Community;
use AppBundle\Entity\Result;
use AppBundle\Entity\Address;
use AppBundle\Entity\University;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/community", name="community")
 */
class CommunityController extends Controller
{

    /**
     * @Route("/list", name="community_list")
     * @Security("has_role('ROLE_USER')")
     */
    public function listAction(Request $request)
    {

        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findAll();

        return $this->render(
            'AppBundle:community:communityList.html.twig',
            array(
                'communityList'=>$communityList
            )
        );
    }

    /**
     * @Route("/post", name="community_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function postAction(Request $request)
    {
        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getManager();

            try{

                // 1.1) University Repository should try to register university
                $university = $em->getRepository('AppBundle:University')->find($request->get('university_id'));

                $community = new Community();
                $community->setName( $request->get('community_name') );
                $community->setDescription( $request->get('community_description') );
                $community->setUniversity( $university );

                $em->persist($community);
                $em->flush();

            } catch (Exception $e){}

            // Redirect route to university list page
            return $this->redirectToRoute('university_get');
        }


        // 2) DEFAULT CASE
        $universities = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        return $this->render(
            'AppBundle:community:communityRegister.html.twig',
            array('universities'=>$universities)
        );
    }

    /**
     * @Route("/put/{communityId}", name="community_put")
     * @Security("has_role('ROLE_USER')")
     */
    public function putAction(Request $request , $communityId)
    {

        // 1) POST OPERATION
        if($request->getMethod() == 'POST' && false){

//            try{
//                $em = $this->getDoctrine()->getManager();
//
//                // 1.1) University Repository should try to register university
//                $university = $em->getRepository('AppBundle:University')->find($communityId);
//
//                // 1.2) Check University exist
//                if (!$university) {
//                    throw $this->createNotFoundException(
//                        'No product found for id '.$communityId
//                    );
//                }
//
//                $university->setName( $request->get('university_name') );
//                $university->setLink( $request->get('university_web_address') );
//                $em->flush();
//
//            } catch (Exception $e){}
//
//            // Redirect route to university list page
//            return $this->redirectToRoute('university_get');
        }

        // 2) DEFAULT CASE
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);

        return $this->render(
            'AppBundle:community:communityUpdate.html.twig',
            array(
                'community' => $community,
            )
        );
    }

    /*******************************************************************************************************************
     *******************************************************************************************************************
                                                    UTIL FUNCTIONS
     *******************************************************************************************************************
     *******************************************************************************************************************
     */

    // checker functions will be here ...


}
