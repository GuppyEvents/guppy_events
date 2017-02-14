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
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->getSeruventCommunity();

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


    /**
     * @Route("/seruvent/uyeler", name="seruvent_member")
     */
    public function seruventMemberAction(Request $request)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->getSeruventCommunity();

        if($community){
            $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$community));
            $acceptState = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoleState')->findAcceptState();
            $communityUserRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser, 'state' => $acceptState));
            $data['communityAllUserRoles'] = $communityUserRole;
            $dates= array();

            foreach($communityUserRole as $comm){
                $dates[(string)$comm->getRegisterDate()->getTimestamp() * 1000] = 1;
            }
            $data['registerDates'] = $dates;

            //BAŞVURULABİLECEK ROLLERİ GETİR
            $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityRole')->findAll();
            $data["userRoles"] = $communityUserRoles;

            if($this->getUser()){
                $userRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findBy(array('community'=>$community , 'user'=>$this->getUser()));
                $communityUserRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findOneBy(array('communityUser'=>$userRole, 'state' => $acceptState));
                $data['userRole'] = $communityUserRole;
            }

            // İlgili kullanıcının topluluğun yöneticisi olup olunmadığına bakılır
            $data['isUserCommunityAdmin'] = $this->getUser() ? $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser(),$community) : false;

            $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$this->getUser()));
            $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser));
            $data['communityUserRoles'] = $communityUserRoles;
        }

        $data['community'] = $community;
        $data['seruvent'] = true;
        return $this->render('AppBundle:community:communityUserList.html.twig' , $data);
    }


    /**
     * @Route("/seruvent/etkinlikler", name="seruvent_events")
     */
    public function seruventEventsAction(Request $request)
    {
        $data = array();
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->getSeruventCommunity();
        $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByCommunityId($community->getId());

        foreach ($eventList as $event){
            $ticket = $this->getDoctrine()->getRepository('AppBundle:Ticket')->findLowestPriceTicketByEventId($event->getId());
            if($this->getUser()) {
                $eventUser = $this->getDoctrine()->getRepository('AppBundle:EventUserRating')->findOneBy(array('user'=>$this->getUser()->getId() , 'event'=>$event->getId()));
                $event->is_saved = $eventUser ? $eventUser->getIsSaved() : false;
            }
            $event->ticket_price = $ticket && $ticket->getPrice()>0 ? $ticket->getPrice() . ' TL' : null;
        }

        $communityUser = $this->getDoctrine()->getRepository('AppBundle:CommunityUser')->findOneBy(array('community'=>$community , 'user'=>$this->getUser()));
        $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findBy(array('communityUser'=>$communityUser));
        $data['communityUserRoles'] = $communityUserRoles;

        $data['community'] = $community;
        $data['eventList'] = $eventList;
        $data['isUserCommunityAdmin'] = $this->getUser() ? $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser(),$community) : false;

        $data['seruvent'] = true;
        return $this->render('AppBundle:community:communityEvents.html.twig' , $data);
    }


}