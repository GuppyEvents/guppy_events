<?php

namespace AppBundle\Controller;

use AppBundle\Entity\City;
use AppBundle\Entity\Community;
use AppBundle\Entity\CommunityUser;
use AppBundle\Entity\Event;
use AppBundle\Entity\Result;
use AppBundle\Entity\Address;
use AppBundle\Entity\University;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\Utils;

/**
 * @Route("/etkinlik", name="event")
 */
class EventController extends Controller
{


    /**
     * @Route("/h/{eventId}", name="user_event_mainpage")
     */
    public function eventMainPageAction($eventId)
    {
        $data = array();

        //$state = $this->getDoctrine()->getRepository('AppBundle:State')->findPublishState();
        $event = $this->getDoctrine()->getRepository('AppBundle:Event')->findOneBy(array('id'=>$eventId));
        $tickets = $this->getDoctrine()->getRepository('AppBundle:Ticket')->findBy(array('event'=>$eventId));

        // --1-- kullanıcı varsa ve topluluk yöneticisi ise userIsAdmin true döner
        // --2-- kullanıcı varsa ve topluluk yöneticisi değilse userIsAdmin false döner
        // --3-- eğer kullanıcı yoksa null döner ve register butonu da kaldırılır
        if(isset($event)){
            $community = $this->getDoctrine()->getRepository('AppBundle:Community')->findOnePublishCommunity($event->getCommunityUser()->getCommunity());
            if($community){
                if($this->getUser()){
                    $communityAdminRole = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findCommunityAdminRoles($this->getUser());
                    $data['userIsAdmin'] = $communityAdminRole and count($communityAdminRole)>0 ? true : false;
                    $data['canEditEvent'] = false;
                    foreach($communityAdminRole as $car){
                        if($car->getCommunityUser()->getCommunity()->getId() == $community->getId()){
                            $data['canEditEvent'] = true;
                            break;
                        }
                    }
                }
            }else{
                $_SESSION['error_message'] = 'Etkinlik Topluluğu Yayında Değil';//redirect edilen sayfada mesaj gosterilmesi için sessiona mesaj atanır
            }

        }else{
            $data['pageError'] = "Üzgünüz şuan içeriğe ulaşılamıyor";
            $data['pageErrorBody'] = "Etkinlik Bulunamadı ya da yayında kaldırıldı";
        }


        if($this->getUser()){
            $eventUser = $this->getDoctrine()->getRepository('AppBundle:EventUserRating')->findOneBy(array('user'=>$this->getUser(),'event'=>$event));
            if($eventUser){
                $data['eventUser'] = $eventUser;
            }
        }

        $data['event'] = $event;
        $data['tickets'] = $tickets;
        $data = array_merge($data,Utils::getSessionToastMessages());
        return $this->render('AppBundle:event:eventMain.html.twig' , $data);
    }

    /**
     * @Route("/edit/{eventId}", name="event_edit_page")
     * @Security("has_role('ROLE_USER')")
     */
    public function eventEditPageAction($eventId)
    {
        $data = array();

        $event = $this->getDoctrine()->getRepository('AppBundle:Event')->findOneBy(array('id'=>$eventId));
        if(isset($event)){

            $isUserCommunityAdmin = $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser()->getId(), $event->getCommunityUser()->getCommunity()->getId());
            if($isUserCommunityAdmin){

                $tickets = $this->getDoctrine()->getRepository('AppBundle:Ticket')->findBy(array('event'=>$eventId));
                $community = $this->getDoctrine()->getRepository('AppBundle:Community')->findOnePublishCommunity($event->getCommunityUser()->getCommunity());
                // -- Eger topluluk yayında değilse etkinlik sayfasında bu gösterilmeli
                if(!isset($community)){
                    $data['pageWarning'] = "Üzgünüz şuan içeriğe ulaşılamıyor";
                    $data['pageWarningBody'] = "Etkinlik Topluluğu Yayında Değil";
                }

                $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findCommunityAdminRoles($this->getUser()->getId());
                $data["communityAdminRoles"] = $communityUserRoles;
                $data['event'] = $event;
                $data['tickets'] = $tickets;

            }else{
                $data['pageError'] = "Üzgünüz şuan içeriğe ulaşılamıyor";
                $data['pageErrorBody'] = "Etkinlik için erişim izniniz bulunmamaktadır";
            }

        }else{
            $data['pageError'] = "Üzgünüz şuan içeriğe ulaşılamıyor";
            $data['pageErrorBody'] = "Etkinlik Bulunamadı ya da yayında kaldırıldı";
        }
        
        return $this->render('AppBundle:event:eventEdit.html.twig' , $data);
    }


    /**
     * @Route("/ekle", name="event_add_page")
     * @Security("has_role('ROLE_USER')")
     */
    public function eventAddPageAction(Request $request)
    {
        $data = array();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $data["selectedDate"] = $request->get('sd')? str_replace('-' ,'/',$request->get('sd')) : null; // -> selected day
        $communityUserRoles = $this->getDoctrine()->getRepository('AppBundle:CommunityUserRoles')->findCommunityAdminRoles($this->getUser()->getId());
        if($communityUserRoles){
            $data["communityAdminRoles"] = $communityUserRoles;
        }else{
            return $this->redirectToRoute('home_events');
        }

        return $this->render('AppBundle:event:eventAdd.html.twig' , $data);
    }



    /**
     * @Route("/editEvent/", name="event_edit_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function editEventAction(Request $request)
    {
        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getManager();

            try{

                // --1.1-- Event have to added by user
                // --1.1.1-- Eğer kullanıcı admin ise izin ver
                // --1.2-- Event may contains community
                $user = $this->getUser();
                $community = $em->getRepository('AppBundle:Community')->find($request->get('community_id'));
                if($community){
                    $communityUser = $em->getRepository('AppBundle:CommunityUser')->findBy(array('user'=>$user->getId() , 'community'=>$community->getId()));
                    $isUserCommunityAdmin = $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser()->getId(), $community->getId());

                    // --2.1-- Eğer böyle bir topluluk kullanıcısı varsa o kullanıcı ile işlem yap
                    if(count($communityUser)>0){
                        $communityUser = $communityUser[0];
                    }else if(!$isUserCommunityAdmin){
                        return $this->redirectToRoute('home_events');
                    }else{
                        return $this->redirectToRoute('event_add_page');
                    }

                    $request_date = \DateTime::createFromFormat('d/m/Y H:i', $request->get('event_date'));
                    $request_permission = $request->get('event_permission') ? $request->get('event_permission') : 'PUBLIC';

                    $event = $em->getRepository('AppBundle:Event')->find($request->get('event_id'));
                    $event->setTitle( $request->get('event_title') );
                    $event->setDescription( $request->get('event_description') );
                    $event->setPermission($request_permission);
                    $event->setStartDate( $request_date );
                    $event->setMaxParticipantNum( $request->get('event_participant_count') );
                    // yeni resim eklenip eklenmediği kontrol edilir
                    $event_image = $request->get('event_image_base64');
                    if(strlen($event_image)>0){
                        $fileName = "";
                        if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                            $fileName .= "dev/";
                        }
                        $extension = substr($event_image, 0, strpos($event_image, ";"));
                        $fileName .= Utils::getGUID();
                        if (strpos($extension, "data:image") !== false) {
                            if (strpos($extension, "jpeg") !== false) {
                                $fileName .= ".jpg";
                            } else if (strpos($extension, "png") != false) {
                                $fileName .= ".png";
                            } else {
                                $data['error_msg'] = 'Desteklenmeyen görüntü biçimi.';
                                return $this->render('AppBundle:user:profile_settings_account.html.twig', $data);
                            }
                        } else {
                            $data['error_msg'] = 'Desteklenmeyen dosya biçimi.';
                            return $this->render('AppBundle:user:profile_settings_account.html.twig', $data);
                        }
                        $event_image = Utils::uploadBase64ToServer($event_image, $fileName);
                        $event->setImageBase64($event_image);
                    }
                    $event->setGpsLocationLat($request->get('event_location_lat'));
                    $event->setGpsLocationLng($request->get('event_location_lng'));
                    $event->setCommunityUser( $communityUser );
                    $event->setLocationName($request->get('search_event_location'));

                    $ticket = $em->getRepository('AppBundle:Ticket')->find($request->get('ticket_id'));
                    $ticket->setPrice(intval($request->get('event_price')));

                    $em->flush();

                    return $this->redirectToRoute('user_event_mainpage', array('eventId' => $event->getId()));
                }else{
                    return $this->redirectToRoute('home_events');
                }
            } catch (Exception $e){
                $logger = $this->get('monolog.logger.graylog');
                $logger->addError($e->getMessage());
                return $this->redirectToRoute('event_add_page');
            }

        }
    }
    


    /**
     * @Route("/addEvent", name="event_add_post")
     * @Security("has_role('ROLE_USER')")
     */
    public function addPostAction(Request $request)
    {
        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            $em = $this->getDoctrine()->getManager();

            try{

                // --1.1-- Event have to added by user
                // --1.1.1-- Eğer kullanıcı admin ise izin ver
                // --1.2-- Event may contains community
                $user = $this->getUser();
                $community = $em->getRepository('AppBundle:Community')->find($request->get('community_id'));
                if($community){
                    $communityUser = $em->getRepository('AppBundle:CommunityUser')->findBy(array('user'=>$user->getId() , 'community'=>$community->getId()));
                    $isUserCommunityAdmin = $this->getDoctrine()->getRepository('AppBundle:User')->isUserCommunityAdmin($this->getUser()->getId(), $community->getId());

                    // --2.1-- Eğer böyle bir topluluk kullanıcısı varsa o kullanıcı ile işlem yap
                    if(count($communityUser)>0){
                        $communityUser = $communityUser[0];
                    }else if(!$isUserCommunityAdmin){
                        return $this->redirectToRoute('home_events');
                    }else{
                        return $this->redirectToRoute('event_add_page');
                    }

                    $request_date = \DateTime::createFromFormat('d/m/Y H:i', $request->get('event_date'));
                    $request_permission = $request->get('event_permission') ? $request->get('event_permission') : 'PUBLIC';
                    $publishState = $this->getDoctrine()->getRepository('AppBundle:State')->findPublishState();

                    $event = new Event();
                    $event->setTitle( $request->get('event_title') );
                    $event->setDescription( $request->get('event_description') );
                    $event->setPermission($request_permission);
                    $event->setStartDate( $request_date );
                    $event->setMaxParticipantNum( $request->get('event_participant_count') );

                    $event_image = $request->get('event_image_base64');
                    if(strlen($event_image)>0){
                        $fileName = "";
                        if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                            $fileName .= "dev/";
                        }
                        $extension = substr($event_image, 0, strpos($event_image, ";"));
                        $fileName .= Utils::getGUID();
                        if (strpos($extension, "data:image") !== false) {
                            if (strpos($extension, "jpeg") !== false) {
                                $fileName .= ".jpg";
                            } else if (strpos($extension, "png") != false) {
                                $fileName .= ".png";
                            } else {
                                $data['error_msg'] = 'Desteklenmeyen görüntü biçimi.';
                                return $this->render('AppBundle:user:profile_settings_account.html.twig', $data);
                            }
                        } else {
                            $data['error_msg'] = 'Desteklenmeyen dosya biçimi.';
                            return $this->render('AppBundle:user:profile_settings_account.html.twig', $data);
                        }
                        $event_image = Utils::uploadBase64ToServer($event_image, $fileName);
                        $event->setImageBase64($event_image);
                    }

                    $event->setGpsLocationLat($request->get('event_location_lat'));
                    $event->setGpsLocationLng($request->get('event_location_lng'));
                    $event->setCommunityUser( $communityUser );
                    $event->setState($publishState);

                    $ticket = new Ticket();
                    $ticket->setPrice(intval($request->get('event_price')));
                    $ticket->setEvent($event);
                    $em->persist($event);
                    $em->persist($ticket);
                    $em->flush();
                    return $this->redirectToRoute('user_event_mainpage', array('eventId' => $event->getId()));
                }else{
                    return $this->redirectToRoute('home_events');
                }
            } catch (Exception $e){
                $logger = $this->get('monolog.logger.graylog');
                $logger->addError($e->getMessage());
                return $this->redirectToRoute('event_add_page');
            }

        }
    }
    

}
