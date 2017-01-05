<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/search", name="search")
 */
class SearchController extends Controller
{

    /**
     * @Route("/", name="search_all")
     */
    public function indexAction(Request $request)
    {
        $data = array();
        $searchKey = $request->get('search_key');

        $data['search_key'] = $searchKey;
        $data['communityList'] = $this->getDoctrine()->getRepository('AppBundle:Community')->findCommunityListByName($searchKey);
        $data['eventList'] = $this->getDoctrine()->getRepository('AppBundle:Event')->findEventListByName($searchKey);

        return $this->render('AppBundle:search:index.html.twig', $data);
    }


}