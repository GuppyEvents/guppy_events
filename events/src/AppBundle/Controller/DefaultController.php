<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        $communities = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy(array('university'=>5));

        return $this->render('default/home.html.twig' , array(
            'communities' => $communities
        ));

    }

    /**
     * @Route("/club", name="homepage_club")
     */
    public function clubAction(Request $request)
    {
        return $this->render('default/club.html.twig');
    }

}
