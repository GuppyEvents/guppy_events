<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Result;
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
        return $this->render('AppBundle:default:yamdex.html.twig');
    }

    /**
     * @Route("/", name="homepage")
     */
    public function homeAction(Request $request)
    {
        return $this->redirectToRoute('coming_soon');
//        return $this->redirectToRoute('home_events');
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
     * @Route("/home/communities", name="home_communities")
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
        return $this->render('AppBundle:default:main_community_list.html.twig' , $data);
    }


    /**
     * @Route("/home/events", name="home_events")
     */
    public function eventsAction(Request $request)
    {

        // öncelikle bulunan ay değeri alınır
        // ayın ilk gününe set edilir ve haftası alınır
        // ayın son gününe set edilir ve haftası alınır
        // döngü ile ilk hafta-son hafta arası etkinlikler alınır

        $currentDay = new \DateTime();      // takvim üzerinden bugun öncesi ve sonrası olarak ayrılacak
        $currentDay->setTime(0,0);
        $currentdate = new \DateTime();
        $firstDayOfMonth = new \DateTime($currentdate->format('Y').'-'.$currentdate->format('m').'-01');
        $firstDayOfMonth->setTime(0,0);
        $lastDayOfMonth = new \DateTime();
        $lastDayOfMonth->setTimestamp(strtotime('+' . 1 . ' month', $firstDayOfMonth->getTimestamp()));
        $lastDayOfMonth->setTimestamp(strtotime('-' . 1 . ' days', $lastDayOfMonth->getTimestamp()));


        $data['eventListWeeks'] = array();
        $lastWeek = $lastDayOfMonth->format('W') % 52;
        // ay içerisindeki haftalar tek tek alınır
        while ($firstDayOfMonth->format('W')%52 <= $lastWeek) {

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
                $eventMain['eventList'] = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByDate( $dayOne,$dayTwo);

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
        $data['todayEvents'] = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByDate( $today,$tomorrow);
        $data['todayDate'] = $today;

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
     * @Route("/home/faq", name="faq")
     */
    public function faqAction(Request $request)
    {
        $data = array();
        return $this->render('AppBundle:default:faq.html.twig' , $data);

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

                $communityObj['homepagelink'] = $this->get('router')->generate('user_community_homepage' , array(
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

            foreach ($eventList as $event) {
                $evetObj =array();
                $evetObj["id"] = $event->getId();
            }

            $data["eventList"] = $eventList;

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
