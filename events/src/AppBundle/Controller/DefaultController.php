<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Result;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;

class DefaultController extends Controller
{

    /**
     * @Route("/f240212d605c.html", name="yandex_mail")
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

        // PREVIOUS WEEK
        // calculate first day of current week
        $prevPrevWeekStartDay = new \DateTime();
        $prevPrevWeekStartDay->setTime(0,0);
        $wk_ts  = strtotime('+' . $prevPrevWeekStartDay->format('W')-3 . ' weeks', strtotime('2017' . '0101'));
        $mon_ts = strtotime('-' . date('w', $wk_ts) + 1 . ' days', $wk_ts);
        $prevPrevWeekStartDay->setTimestamp($mon_ts);

        $i = 0;
        $prevPrevWeekEventList = array();
        $dayOne = clone $prevPrevWeekStartDay;
        do {
            $dayTwo = clone $dayOne;
            $dayTwo->add(new \DateInterval("P1D"));
            $eventMain = array();
            $eventMain['eventDate'] = clone $dayOne;
            $eventMain['eventList'] = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByDate( $dayOne,$dayTwo);
            array_push($prevPrevWeekEventList,$eventMain);
            $dayOne->add(new \DateInterval("P1D"));
            $i++;
        } while ($i < 7);
        $data['prevPrevWeekEventDays'] = $prevPrevWeekEventList;


        // PREVIOUS WEEK
        // calculate first day of current week
        $prevWeekStartDay = new \DateTime();
        $prevWeekStartDay->setTime(0,0);
        $wk_ts  = strtotime('+' . $prevWeekStartDay->format('W')-2 . ' weeks', strtotime('2017' . '0101'));
        $mon_ts = strtotime('-' . date('w', $wk_ts) + 1 . ' days', $wk_ts);
        $prevWeekStartDay->setTimestamp($mon_ts);

        $i = 0;
        $previousWeekEventList = array();
        $dayOne = clone $prevWeekStartDay;
        do {
            $dayTwo = clone $dayOne;
            $dayTwo->add(new \DateInterval("P1D"));
            $eventMain = array();
            $eventMain['eventDate'] = clone $dayOne;
            $eventMain['eventList'] = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByDate( $dayOne,$dayTwo);
            array_push($previousWeekEventList,$eventMain);
            $dayOne->add(new \DateInterval("P1D"));
            $i++;
        } while ($i < 7);
        $data['previousWeekEventDays'] = $previousWeekEventList;


        // CURRENT WEEK
        $currentDay = new \DateTime();
        $currentDay->setTime(0,0);
        $weekStartDay = new \DateTime();
        $weekStartDay->setTime(0,0);
        // calculate first day of current week
        $wk_ts  = strtotime('+' . $weekStartDay->format('W')-1 . ' weeks', strtotime('2017' . '0101'));
        $mon_ts = strtotime('-' . date('w', $wk_ts) + 1 . ' days', $wk_ts);
        $weekStartDay->setTimestamp($mon_ts);

        $i = 0;
        $currentWeekEventList = array();
        $dayOne = clone $weekStartDay;
        do {
            $dayTwo = clone $dayOne;
            $dayTwo->add(new \DateInterval("P1D"));
            $eventMain = array();
            $eventMain['eventDate'] = clone $dayOne;
            $eventMain['eventList'] = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByDate( $dayOne,$dayTwo);
            if($currentDay<=$dayOne){
                $eventMain['isFuture'] = true;
            }

            if($currentDay==$dayOne){
                $eventMain['isToday'] = true;
            }
            array_push($currentWeekEventList,$eventMain);
            $dayOne->add(new \DateInterval("P1D"));
            $i++;
        } while ($i < 7);
        $data['eventDays'] = $currentWeekEventList;


        // NEXT WEEK
        $nextWeekStartDay = new \DateTime();
        $nextWeekStartDay->setTime(0,0);
        $wk_ts  = strtotime('+' . $nextWeekStartDay->format('W')+0 . ' weeks', strtotime('2017' . '0101'));
        $mon_ts = strtotime('-' . date('w', $wk_ts) + 1 . ' days', $wk_ts);
        $nextWeekStartDay->setTimestamp($mon_ts);

        $i = 0;
        $nextWeekEventList = array();
        $dayOne = clone $nextWeekStartDay;
        do {
            $dayTwo = clone $dayOne;
            $dayTwo->add(new \DateInterval("P1D"));
            $eventMain = array();
            $eventMain['eventDate'] = clone $dayOne;
            $eventMain['eventList'] = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByDate( $dayOne,$dayTwo);
            array_push($nextWeekEventList,$eventMain);
            $dayOne->add(new \DateInterval("P1D"));
            $i++;
        } while ($i < 7);
        $data['nextWeekEventDays'] = $nextWeekEventList;


        // NEXT NEXT WEEK
        $nextNextWeekStartDay = new \DateTime();
        $nextNextWeekStartDay->setTime(0,0);
        $wk_ts  = strtotime('+' . $nextNextWeekStartDay->format('W')+1 . ' weeks', strtotime('2017' . '0101'));
        $mon_ts = strtotime('-' . date('w', $wk_ts) + 1 . ' days', $wk_ts);
        $nextNextWeekStartDay->setTimestamp($mon_ts);

        $i = 0;
        $nextNextWeekEventList = array();
        $dayOne = clone $nextNextWeekStartDay;
        do {
            $dayTwo = clone $dayOne;
            $dayTwo->add(new \DateInterval("P1D"));
            $eventMain = array();
            $eventMain['eventDate'] = clone $dayOne;
            $eventMain['eventList'] = $this->getDoctrine()->getRepository('AppBundle:Event')->findPublishEventsByDate( $dayOne,$dayTwo);
            array_push($nextNextWeekEventList,$eventMain);
            $dayOne->add(new \DateInterval("P1D"));
            $i++;
        } while ($i < 7);
        $data['nextNextWeekEventDays'] = $nextNextWeekEventList;



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
