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

use Google\Cloud\Storage\StorageClient;

class DefaultController extends Controller
{

    /**
     * @Route("/eb9d2a57c52b.html", name="yandex_mail")
     */
    public function yandexMailAction(Request $request)
    {
        return $this->render('AppBundle:default:yandex.html.twig');
    }

    /**
     * @Route("/404", name="error_404")
     */
    public function pegeNotFoundAction(Request $request)
    {
        $data = array();
        $data['referer'] = $request->headers->get('referer') ? $request->headers->get('referer') : $this->get('router')->generate('homepage');
        $data['content'] = '' ;
        return $this->render('AppBundle:error:error.html.twig',$data);
    }

    /**
     * @Route("/", name="homepage")
     */
    public function homeAction(Request $request)
    {
//        return $this->redirectToRoute('coming_soon');
        return $this->redirectToRoute('home_events');
    }

    /**
     * @Route("/cok-yakinda", name="coming_soon")
     */
    public function comingSoonAction(Request $request)
    {
        // --1-- build the form
        $data = array();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $data['form'] = $form->createView();

        // --2-- handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $url = "https://www.google.com/recaptcha/api/siteverify";
            $apikey = '6LcQ_BIUAAAAAKZQf656kBuRqpB7Z3LuUpGftB8u';
            $response=file_get_contents($url.'?secret='.$apikey.'&response='.$request->get('g-recaptcha-response'));
            $dataResponse = json_decode($response);

            if(isset($dataResponse->success) && $dataResponse->success==true){
                $acceptedMailAddress = '@ug.bilkent.edu.tr';
                if(substr($user->getEmail(), -strlen($acceptedMailAddress)) === $acceptedMailAddress){

                    // --3-- Encode the password (you could also do this via Doctrine listener)
                    $password = $this->get('security.password_encoder')
                        ->encodePassword($user, $user->getPassword());
                    $user->setPassword($password);

                    // --4-- save the User!
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                    $data['success_msg'] = "Kaydınız başarıyla gerçekleşmiştir";
                    //return $this->redirectToRoute('homepage');

                }else{
                    $data['error_msg'] = "Bilkent mail adresi ile kayıt olmanız gerekiyor. (@ug.bilkent.edu.tr)";
                }
            }else{
                $data['error_msg'] = "Recaptcha doğrulama hatası";
            }
        }

        return $this->render('AppBundle:default:main_coming_soon.html.twig' , $data);
    }

    /**
     * @Route("/dev", name="dev")
     */
    public function devHomePageAction(Request $request)
    {
        return $this->redirectToRoute('home_events');
    }

    /**
     * @Route("/anasayfa/topluluklar", name="home_communities")
     */
    public function communitiesAction(Request $request)
    {

        // TODO: Şuan için sadece BİLKENT ÜNİVERSİTESİ toplulukları getirilmekte
        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findCommunityListByUniversity();


        for($i=0; $i<count($communityList);$i++){

            $communityList[$i]->link_facebook = null;
            $communityList[$i]->link_twitter = null;
            $communityList[$i]->link_instagram = null;
            $communityLinks = $this->getDoctrine()->getRepository('AppBundle:CommunityLink')->findCommunitySocialNetworksByCommunityId($communityList[$i]->getId());
            foreach ($communityLinks as $communityLink){
                switch ($communityLink->getSocialNetwork()->getId()){
                    case 5001:
                        $communityList[$i]->link_facebook = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                        break;
                    case 5002:
                        $communityList[$i]->link_twitter = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                        break;
                    case 5003:
                        $communityList[$i]->link_instagram = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                        break;
                    default:
                        break;
                }
            }
        }
        $data['communities'] = $communityList;
        return $this->render('AppBundle:default:main_communities.html.twig' , $data);
    }


    /**
     * @Route("/anasayfa/etkinlikler", name="home_events")
     */
    public function eventsAction(Request $request)
    {
        $data = array();
        $data = array_merge($data,Utils::getSessionToastMessages());

        $currentDay = new \DateTime();      // takvim üzerinden bugun öncesi ve sonrası olarak ayrılacak
        $currentDay->setTime(0,0);

        $param = $request->get('d'); // d=1702 => 2017-02
        if(strlen($param)>=4){
            $pyear = substr($param, 0, 2);
            $pmonth = substr($param, 2, 2);
        }

        // --1-- CHECKER
        // -1.1- Yıl > 15 & Yıl < 99 olmalıdır
        if(isset($pyear) && !(intval($pyear)>15)){
            $data['referer'] = $request->headers->get('referer') ? $request->headers->get('referer') : $this->get('router')->generate('homepage');
            $data['content'] = 'Etkinliklerin gelmesini istediğiniz yıl için hata aldık. 2015 yılından önce etkinlik bulunmamaktadır ' ;
            return $this->render('AppBundle:error:error.html.twig' , $data);
        }

        // -1.2- ay > 0 & ay < 13 olmalıdır
        if(isset($pmonth) && (!(intval($pmonth)>0) || !(intval($pmonth)<13))){
            $data['referer'] = $request->headers->get('referer') ? $request->headers->get('referer') : $this->get('router')->generate('homepage');
            $data['content'] = 'Etkinliklerin gelmesini istediğiniz ay için hata aldık.';
            return $this->render('AppBundle:error:error.html.twig' , $data);
        }

        $targetyear = isset($pyear) ? '20'.$pyear : $currentDay->format('Y');
        $targetmonth = isset($pmonth) ? $pmonth : $currentDay->format('m');

        $firstDayOfMonth = new \DateTime($targetyear.'-'.$targetmonth.'-01');
        $firstDayOfMonth->setTime(0,0);
        $lastDayOfMonth = new \DateTime();
        $lastDayOfMonth->setTimestamp(strtotime('+' . 1 . ' month', $firstDayOfMonth->getTimestamp()));
        $lastDayOfMonth->setTimestamp(strtotime('-' . 1 . ' days', $lastDayOfMonth->getTimestamp()));

        // bir sonraki ve önceki ay için parametreler
        $nextMonthFirstDay = clone $lastDayOfMonth;
        $nextMonthFirstDay->setTimestamp(strtotime('+1 days', $nextMonthFirstDay->getTimestamp()));
        $data['nextMonthDateParam'] = substr($nextMonthFirstDay->format('Y'),2,2) . $nextMonthFirstDay->format('m');
        $prevMonthLastDay = clone $firstDayOfMonth;
        $prevMonthLastDay->setTimestamp(strtotime('-1 days', $prevMonthLastDay->getTimestamp()));
        $data['prevMonthDateParam'] = substr($prevMonthLastDay->format('Y'),2,2) . $prevMonthLastDay->format('m');

        // takvim üzerinde yazan yıl ve ayı döner
        $data['calendarHeaderYear'] = $firstDayOfMonth->format('Y');
        switch ($firstDayOfMonth->format('m')) {
            case '1':
                $data['calendarHeaderMonth'] = 'Ocak';
                break;
            case '2':
                $data['calendarHeaderMonth'] = 'Şubat';
                break;
            case '3':
                $data['calendarHeaderMonth'] = 'Mart';
                break;
            case '4':
                $data['calendarHeaderMonth'] = 'Nisan';
                break;
            case '5':
                $data['calendarHeaderMonth'] = 'Mayıs';
                break;
            case '6':
                $data['calendarHeaderMonth'] = 'Haziran';
                break;
            case '7':
                $data['calendarHeaderMonth'] = 'Temmuz';
                break;
            case '8':
                $data['calendarHeaderMonth'] = 'Ağustos';
                break;
            case '9':
                $data['calendarHeaderMonth'] = 'Eylül';
                break;
            case '10':
                $data['calendarHeaderMonth'] = 'Ekim';
                break;
            case '11':
                $data['calendarHeaderMonth'] = 'Kasım';
                break;
            case '12':
                $data['calendarHeaderMonth'] = 'Aralık';
                break;
            default:
                $data['calendarHeaderMonth'] = 'HATA';
                break;
        }


        $data['eventListWeeks'] = array();
        $flag = true;
        // ay içerisindeki haftalar tek tek alınır
        while ($firstDayOfMonth->format('W')%52 <= $lastDayOfMonth->format('W') && $flag) {

            // yılın bası ve sonunda sorun oldugundan eklendi
            if($firstDayOfMonth->format('W')==$lastDayOfMonth->format('W')){
                $flag = false;
            }

            // burada haftanın ilk günü bulunur
            $firstDayOfWeek = clone $firstDayOfMonth;
            $backDayCount = date('w', $firstDayOfWeek->getTimestamp()) == 0 ? 7 : date('w', $firstDayOfWeek->getTimestamp());
            $mon_ts = strtotime('-' . $backDayCount + 1 . ' days', $firstDayOfWeek->getTimestamp());
            $firstDayOfWeek->setTimestamp($mon_ts);

            // haftanın günlerinin tek tek etkinlikleri alınır
            $i = 0;
            $eventListOfWeek = array();
            $dayOne = clone $firstDayOfWeek;
            do {
                $dayTwo = clone $dayOne;
                $dayTwo->add(new \DateInterval("P1D"));
                $eventMain = array();
                $eventMain['a'] = 'a';
                $eventMain['eventDate'] = clone $dayOne;
                $eventMain['eventCount'] = count($this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByDate( $dayOne,$dayTwo));
                $eventMain['eventList'] = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByDate( $dayOne,$dayTwo,5);

                if($currentDay<=$dayOne){
                    $eventMain['isFuture'] = true;
                }

                if($currentDay==$dayOne){
                    $eventMain['isToday'] = true;
                }

                array_push($eventListOfWeek,$eventMain);
                $dayOne->add(new \DateInterval("P1D"));
                $i++;
            } while ($i < 7);
            array_push($data['eventListWeeks'],$eventListOfWeek);
            $firstDayOfMonth = $firstDayOfMonth->setTimestamp(strtotime('+' . 1 . ' weeks', $firstDayOfMonth->getTimestamp()));
        }


        // varsa bugunun etkinlikleri alınır
        $today = new \DateTime();
        $today->setTime(0,0);
        $tomorrow = clone $today;
        $tomorrow->add(new \DateInterval("P1D"));
        $data['todayDate'] = $today;
        $data['hasCommunityAdminRole'] = $this->getDoctrine()->getRepository('AppBundle:User')->hasUserCommunityAdmin($this->getUser());

        return $this->render('AppBundle:default:main_events.html.twig' , $data);
    }


    /**
     * @Route("/home/about", name="home_about")
     */
    public function aboutAction(Request $request)
    {
        $data = array();
        return $this->render('AppBundle:default:main_about.html.twig' , $data);

    }


    /**
     * @Route("/anasayfa/merak-edilen-sorular", name="faq")
     */
    public function faqAction(Request $request)
    {
        $data = array();
        return $this->render('AppBundle:default:main_faq.html.twig' , $data);
    }

    /**
     * @Route("/sozlesme", name="home_terms")
     */
    public function termsAction(Request $request)
    {
        $data = array();
        $em = $this->getDoctrine()->getManager();
        try{
            // -2.1- Find latest terms
            $terms = $em->getRepository('AppBundle:Terms')->findOneBy(array(),array('id' => 'DESC'));
            $data['terms'] = $terms;
        } catch (Exception $e){}

        // --3-- DEFAULT CASE
        return $this->render('AppBundle:default:main_terms.html.twig' , $data);
    }



    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/s/community-list", name="service_community_list")
     */
    public function getMoreSearchResult(Request $request){

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $data = array();

        // -- 2 -- Try to Get More Community Result
        try{

            // -- 2.1 -- Get parameter list
            $page = intval($request->get('page'));
            $pageSize = $request->get('pageSize') ? intval($request->get('pageSize')) : 12;

            $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findCommunityListByUniversity($page,$pageSize);

            $data['communityList'] = array();
            foreach ($communityList as $community) {
                $communityObj = array();
                $communityObj['id'] = $community->getId();
                $communityObj['name'] = $community->getName();
                $communityObj['image'] = $community->getImageBase64();
                $communityObj['universityName'] = $community->getUniversity()->getName();


                $communityObj['link_facebook'] = null;
                $communityObj['link_twitter'] = null;
                $communityObj['link_instagram'] = null;
                $communityLinks = $this->getDoctrine()->getRepository('AppBundle:CommunityLink')->findCommunitySocialNetworksByCommunityId($community->getId());
                foreach ($communityLinks as $communityLink){
                    switch ($communityLink->getSocialNetwork()->getId()){
                        case 5001:
                            $communityObj['link_facebook'] = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                            break;
                        case 5002:
                            $communityObj['link_twitter'] = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                            break;
                        case 5003:
                            $communityObj['link_instagram'] = $communityLink->getSocialNetwork()->getName() . $communityLink->getLink();
                            break;
                        default:
                            break;
                    }
                }

                $communityObj['homepagelink'] = $this->get('router')->generate('user_community_events_homepage' , array(
                    'communityId' => $community->getId(),
                ));
                
                array_push($data['communityList'], $communityObj);
            }

            // -- 2.2 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent( $data )));
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
     * @Route("/s/daily-events-list", name="service_daily_events_list")
     */
    public function getEventsForDay(Request $request){

        // -- 1 -- Initialization
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $data = array();

        // -- 2 -- Try to Get More Community Result
        try{

            // -- 2.1 -- Get parameter list
            $date = $request->get('date');

            $dateTimeStart = \DateTime::createFromFormat('Y/m/d', $date);
            $dateTimeStart->setTime(0,0);

            $dateTimeEnd= \DateTime::createFromFormat('Y/m/d', $date);
            $dateTimeEnd->setTime(23,59);

            $eventList = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByDate( $dateTimeStart,$dateTimeEnd);

            $resultEventList = array();
            foreach ($eventList as $event) {
                $eventMain = array();
                $eventMain['eventLink'] = $this->get('router')->generate('user_event_mainpage' , array(
                    'eventId' => $event->getId()
                ),true);
                $eventMain['event'] = $event;

                $eventMain['isSaved'] = false;
                if($this->getUser()){
                    $eventUser = $this->getDoctrine()->getRepository('AppBundle:EventUserRating')->findOneBy(array('user'=>$this->getUser(),'event'=>$event));
                    if($eventUser){
                        $eventMain['isSaved'] = $eventUser->getIsSaved();
                    }
                }
                
                array_push($resultEventList, $eventMain);
            }

            $data["eventList"] = $resultEventList;

            // -- 2.2 -- Return Result
            $response->setContent(json_encode(Result::$SUCCESS->setContent( $data )));
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

}
