<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Address;
use AppBundle\Entity\University;

/**
 * @Route("/admin/university", name="admin_university")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminUniversityController extends Controller
{

    /**
     * @Route("/post", name="admin_university_post")
     */
    public function postAction(Request $request)
    {
        // --1-- Init
        $data = array();

        // --2-- POST OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getEntityManager();
            $em->getConnection()->beginTransaction();

            try{
                // -2.1- University Repository should try to register university
                $city = $em->getRepository('AppBundle:City')->find($request->get('address_city_id'));
                $borough = $em->getRepository('AppBundle:Borough')->find($request->get('address_borough_id'));

                $address = new Address();
                $address->setCityId( $city );
                $address->setBoroughId( $borough );

                $university = new University();
                $university->setName( $request->get('university_name') );
                $university->setLink( $request->get('university_web_address') );
                $university->setImageBase64( $request->get('university_image_base64') );
                $university->setAddress( $address );

                $em->persist($university);
                $em->flush();

                // -2.2- Try and commit the transaction
                $em->getConnection()->commit();

            } catch (Exception $e){
                $em->getConnection()->rollback();
            }

            // Redirect route to university list page
            return $this->redirectToRoute('admin_university_list');
        }

        // --3-- DEFAULT CASE
        $data['cities'] = $this->getDoctrine()->getRepository('AppBundle:City')->findAll();

        // --4-- RENDERING
        return $this->render('AppBundle:admin:university_register.html.twig', $data);
    }




    /**
     * @Route("/update/{universityId}", name="admin_university_update")
     */
    public function updateAction(Request $request, $universityId)
    {
        // --1-- Init
        $data = array();

        // --2-- UPDATE OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getManager();

            try{
                // -2.1- University Repository should try to register university
                $university = $em->getRepository('AppBundle:University')->find($universityId);

                // -2.2- Check University exist
                if (!$university) {
                    throw $this->createNotFoundException(
                        'No product found for id '.$universityId
                    );
                }

                $university->setName( $request->get('university_name') );
                $university->setLink( $request->get('university_web_address') );
                $university->setImageBase64( $request->get('university_image_base64') );
                $em->flush();

            } catch (Exception $e){}

            // Redirect route to university list page
            return $this->redirectToRoute('admin_university_list');
        }

        // --3-- DEFAULT CASE
        $university = $this->getDoctrine()->getRepository('AppBundle:University')->find($universityId);
        $city = $university->getAddress()->getCityId();
        $borough = $university->getAddress()->getBoroughId();
        $universityMails = $this->getDoctrine()->getRepository('AppBundle:UniversityMail')->findBy(array('university'=>$university));

        $data['university'] = $university;
        $data['universityMails'] = $universityMails;
        $data['city'] = $city;
        $data['borough'] = $borough;

        // --4-- RENDERING
        return $this->render('AppBundle:admin:university_update.html.twig', $data);
    }
    
    
    
    
    /**
     * @Route("/list", name="admin_university_list")
     */
    public function listAction(Request $request)
    {
        // --1-- Init
        $data = array();
        $data['universities'] = array();

        // --2-- OPERATION DONE
        $universityList = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        for($i=0; $i<count($universityList);$i++){
            $university['university_id'] = $universityList[$i]->getId();
            $university['university_name'] = $universityList[$i]->getName();
            $university['university_link'] = $universityList[$i]->getLink();
            $university['university_active'] = $universityList[$i]->getIsActive();
            $university['university_city'] = $this->getDoctrine()->getRepository('AppBundle:City')->find($universityList[$i]->getAddress()->getCityId())->getName();
            array_push($data['universities'],$university);
        }

        // --3-- RENDERING
        return $this->render('AppBundle:admin:university_list.html.twig', $data);
    }




    /**
     * @Route("/delete/{universityId}", name="admin_university_delete")
     */
    public function deleteAction($universityId)
    {
        // --1-- POST OPERATION
        // -1.1- Try to delete university
        try{
            $em = $this->getDoctrine()->getManager();

            $university = $em->getRepository('AppBundle:University')->find($universityId);
            if (!$university) {
                throw $this->createNotFoundException(
                    'No product found for id '.$university
                );
            }

            $em->remove($university);
            $em->flush();

        } catch (Exception $e){}

        // -1.2- Redirect route to university list page
        return $this->redirectToRoute('admin_university_list');
    }

}
