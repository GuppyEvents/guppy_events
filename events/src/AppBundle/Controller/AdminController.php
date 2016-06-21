<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin", name="admin")
 * @Security("has_role('ROLE_USER')")
 */
class AdminController extends Controller
{

    /**
     * @Route("/", name="admin_homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:admin:index.html.twig');
    }
    
}
