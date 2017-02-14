<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Result;
use AppBundle\Entity\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;

class SeruventController extends Controller
{

    /**
     * @Route("/seruvent", name="seruvent_about")
     */
    public function seruventAction(Request $request)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find(1);

        $community->link_facebook = null;
        $community->link_twitter = null;
        $community->link_instagram = null;
        $communityLinks = $this->getDoctrine()->getRepository('AppBundle:CommunityLink')->findCommunitySocialNetworksByCommunityId($community->getId());
        foreach ($communityLinks as $communityLink){
            switch ($communityLink->getSocialNetwork()->getId()){
                case 5001:
                    $community->link_facebook = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                    break;
                case 5002:
                    $community->link_twitter = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                    break;
                case 5003:
                    $community->link_instagram = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                    break;
                default:
                    break;
            }
        }

        $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$this->getUser()));
        $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser));

        // İlgili kullanıcının topluluğun yöneticisi olup olunmadığına bakılır
        $data['isUserCommunityAdmin'] = $this->getUser() ? $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser(),$community) : false;

        $data['community'] = $community;
        $data['communityUser'] = $communityUser;
        $data['communityUserRoles'] = $communityUserRoles;

        $data['seruvent'] = true;
        return $this->render('AppBundle:community:communityHome.html.twig' , $data);
    }


}