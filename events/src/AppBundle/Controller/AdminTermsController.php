<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Terms;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Community;

/**
 * @Route("/admin/terms", name="admin_terms")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminTermsController extends Controller
{

    /**
     * @Route("/", name="admin_terms_get")
     */
    public function getAction(Request $request)
    {
        // --1-- Init
        $data = array();
        $em = $this->getDoctrine()->getManager();

        // --2-- GET OPERATION
        try{
            // -2.1- Find latest terms
            $terms = $em->getRepository('AppBundle:Terms')->findOneBy(array(),array('id' => 'DESC'));
            $data['terms'] = $terms;

        } catch (Exception $e){}

        // --3-- DEFAULT CASE

        // --4-- RENDERING
        return $this->render('AppBundle:admin:terms.html.twig', $data);
    }




    /**
     * @Route("/update", name="admin_terms_update")
     */
    public function updateAction(Request $request)
    {
        // --1-- Init
        $data = array();
        $termsDesc = $request->get('terms_description');

        // --2-- POST OPERATION
        if($request->getMethod() == 'POST' && $termsDesc!=''){

            $em = $this->getDoctrine()->getManager();

            try{

                // -2.1- try to update terms
                $terms = new Terms();
                $terms->setTermsOfUse($termsDesc);

                $em->persist($terms);
                $em->flush();

            } catch (Exception $e){}
        }

        // --3-- DEFAULT CASE

        // --4-- RENDERING
        return $this->redirectToRoute('admin_terms_get');
    }

}
