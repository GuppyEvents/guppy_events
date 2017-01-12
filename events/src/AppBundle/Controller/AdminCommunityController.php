<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Community;

/**
 * @Route("/admin/community", name="admin_community")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminCommunityController extends Controller
{

    /**
     * @Route("/post", name="admin_community_post")
     */
    public function postAction(Request $request)
    {
        // --1-- Init
        $data = array();

        // --2-- POST OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getManager();

            try{
                // -2.1- University Repository should try to register university
                $university = $em->getRepository('AppBundle:University')->find($request->get('university_id'));

                $community = new Community();
                $community->setName( $request->get('community_name') );
                $community->setDescription( $request->get('community_description') );
                $community->setImageBase64($request->get('community_image_base64'));
                $community->setImageBackgroundBase64($request->get('community_image_background_base64'));
                $community->setUniversity( $university );

                $em->persist($community);
                $em->flush();

            } catch (Exception $e){}

            // Redirect route to community list page
            return $this->redirectToRoute('community_list');
        }

        // --3-- DEFAULT CASE
        $data['universities'] = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        // --4-- RENDERING
        return $this->render('AppBundle:community:communityRegister.html.twig', $data);
    }




    /**
     * @Route("/update/{communityId}", name="admin_community_update")
     */
    public function updateAction(Request $request , $communityId)
    {
        // --1-- Init
        $data = array();

        // --2-- POST OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getManager();

            try{
                // -2.1- try to get community
                $community = $em->getRepository('AppBundle:Community')->find($communityId);

                // -2.2- Check community exist
                if (!$community) {
                    throw $this->createNotFoundException(
                        'No product found for id '.$communityId
                    );
                }

                // -2.3- Update
                $community->setName( $request->get('community_name') );
                $community->setDescription( $request->get('community_description') );
                $community->setImageBase64($request->get('community_image_base64'));
                $community->setImageBackgroundBase64($request->get('community_image_background_base64'));

                $em->persist($community);
                $em->flush();

            } catch (Exception $e){}

            // Redirect route to community list page
            return $this->redirectToRoute('community_list');
        }

        // --3-- DEFAULT CASE
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);
        $data['communityLinkList'] = $this->getDoctrine()->getRepository('AppBundle:CommunityLink')->findCommunitySocialNetworksByCommunityId($community->getId());
        $data['community'] = $community;

        // --4-- RENDERING
        return $this->render('AppBundle:community:communityUpdate.html.twig', $data);
    }

    
}
