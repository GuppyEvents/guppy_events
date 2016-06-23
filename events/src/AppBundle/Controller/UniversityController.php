<?php

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\Result;
use AppBundle\Entity\Address;
use AppBundle\Entity\University;
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

    /**
     * @Route("/get", name="university_get")
     * @Security("has_role('ROLE_USER')")
     */
    public function getAction(Request $request)
    {

        $universityList = $this->getDoctrine()->getRepository('AppBundle:University')->findAll();

        $universities = array();
        for($i=0; $i<count($universityList);$i++){
            $university['university_name'] = $universityList[$i]->getName();
            $university['university_link'] = $universityList[$i]->getLink();
            $university['university_active'] = $universityList[$i]->getIsActive();
            $university['university_city'] = $this->getDoctrine()->getRepository('AppBundle:City')->find($universityList[$i]->getAddress()->getCityId())->getName();
            array_push($universities,$university);
        }

        return $this->render(
            'AppBundle:admin:university.html.twig',
            array('universities'=>$universities)
        );
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
     * @Route("/post", name="university_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function postAction(Request $request)
    {
        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getEntityManager();
            $em->getConnection()->beginTransaction();

            try{

                // 1.1) University Repository should try to register university
                $city = $em->getRepository('AppBundle:City')->find($request->get('address_city_id'));
                $borough = $em->getRepository('AppBundle:Borough')->find($request->get('address_borough_id'));

                $address = new Address();
                $address->setCityId( $city );
                $address->setBoroughId( $borough );

                $university = new University();
                $university->setName( $request->get('university_name') );
                $university->setLink( $request->get('university_web_address') );
                $university->setAddress( $address );

                $em->persist($university);
                $em->flush();

                // 1.2) Try and commit the transaction
                $em->getConnection()->commit();

            } catch (Exception $e){
                $em->getConnection()->rollback();
            }

            // Redirect route to university list page
            return $this->redirectToRoute('university_get');
        }


        // 2) DEFAULT CASE
        $cities = $this->getDoctrine()->getRepository('AppBundle:City')->findAll();

        return $this->render(
            'AppBundle:university:universityRegister.html.twig',
            array('cities' => $cities)
        );
    }

}
