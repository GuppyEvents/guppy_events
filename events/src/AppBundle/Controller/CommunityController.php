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

        $selectedUniversityId = null;
        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findAll();
        $universityList = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){
            $em = $this->getDoctrine()->getManager();

            try{
                $selectedUniversityId = $request->get('university_id');

                // eğer universite id değeri varsa o universitenin topluluk listesi getir
                if($selectedUniversityId && $this->getDoctrine()->getRepository('AppBundle:University')->find($request->get('university_id'))){
                    $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy(array('university'=>$request->get('university_id')));
                    $communityIdList = array();
                    for($i=0; $i<count($communityList);$i++){
                        array_push($communityIdList ,$communityList[$i]->getId());
                    }
                }

            } catch (Exception $e){

            }
        }

        return $this->render(
            'AppBundle:community:communityList.html.twig', array(
                'communityList'=>$communityList,
                'universityList'=>$universityList,
                'selectedUniversityId'=>$selectedUniversityId
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
                $community->setImageBase64($request->get('community_image_base64'));
                $community->setUniversity( $university );

                $em->persist($community);
                $em->flush();

            } catch (Exception $e){}

            // Redirect route to university list page
            return $this->redirectToRoute('community_list');
        }


        // 2) DEFAULT CASE
        $universities = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        return $this->render(
            'AppBundle:community:communityRegister.html.twig', array(
                'universities'=>$universities
            )
        );
    }

    /**
     * @Route("/put/{communityId}", name="community_put")
     * @Security("has_role('ROLE_USER')")
     */
    public function putAction(Request $request , $communityId)
    {

        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            // 1.1) try to update community
            try{
                $em = $this->getDoctrine()->getManager();

                // 1.1.1) University Repository should try to register university
                $community = $em->getRepository('AppBundle:Community')->find($communityId);

                // 1.1.2) Check University exist
                if (!$community) {
                    throw $this->createNotFoundException(
                        'No product found for id '.$communityId
                    );
                }

                // 1.1.3) Update
                $community->setName( $request->get('community_name') );
                $community->setDescription( $request->get('community_description') );
                $community->setImageBase64($request->get('community_image_base64'));
                
                $em->persist($community);
                $em->flush();

            } catch (Exception $e){}

            // 1.2) Redirect route to university list page
            return $this->redirectToRoute('community_list');
        }

        // 2) DEFAULT CASE
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($communityId);

        return $this->render(
            'AppBundle:community:communityUpdate.html.twig', array(
                'community' => $community,
            )
        );
    }


    /**
     * @Route("/delete/{communityId}", name="community_delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction($communityId)
    {

        // 1) POST OPERATION
        // 1.1) try to delete community
        try{
            $em = $this->getDoctrine()->getManager();

            $community = $em->getRepository('AppBundle:Community')->find($communityId);
            if (!$community) {
                throw $this->createNotFoundException(
                    'No product found for id '.$communityId
                );
            }

            $em->remove($community);
            $em->flush();

        } catch (Exception $e){}

        // 1.2) Redirect route to community list page
        return $this->redirectToRoute('community_list');

    }


    /*******************************************************************************************************************
     *******************************************************************************************************************
                                                    UTIL FUNCTIONS
     *******************************************************************************************************************
     *******************************************************************************************************************
     */

    // checker functions will be here ...


}
