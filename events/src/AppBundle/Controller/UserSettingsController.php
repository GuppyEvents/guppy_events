<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Result;
use AppBundle\Entity\University;
use AppBundle\Entity\UniversityMail;
use AppBundle\Entity\UniversityUser;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/settings", name="settings")
 */
class UserSettingsController extends Controller
{

    /**
     * @Route("/emails", name="settings_emails")
     * @Security("has_role('ROLE_USER')")
     */
    public function getAction(Request $request)
    {

        $universityUsers = $this->getDoctrine()->getRepository('AppBundle:UniversityUser')->findBy(array('user'=>$this->getUser()));

        return $this->render(
            'AppBundle:settings:settingsEmail.html.twig', array(
                'universityUsers' => $universityUsers,
            )
        );
    }



    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    //                                          APPLICATION/JSON SERVICES
    // -----------------------------------------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * @Route("/emails/add", name="settings_emails_add")
     * @Security("has_role('ROLE_USER')")
     */
    public function addUniversityUserMailAction(Request $request)
    {

        // -- 1 -- Initialization
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');

        // -- 2 -- Try to Add University User Mail
            try{

            // -- 2.1 -- Get mail
                $university_user_mail = $request->get('usermail');
                $university_mail_pre = explode("@", $university_user_mail)[0];
                $university_mail_suf = explode("@", $university_user_mail)[1];

            // -- 2.2 -- Check university mail suffix exist
                $university_mail = $this->getDoctrine()->getRepository('AppBundle:UniversityMail')->findBy(array('hostname'=>$university_mail_suf));
                if (!$university_mail) {
                    $response->setContent(json_encode(Result::$FAILURE_REPORT->setContent('University mail not found')));
                    return $response;
                }else{
                    $university_mail = $university_mail[0];
                }

                $university_user = new UniversityUser();
                $university_user->setEmail($university_user_mail);
                $university_user->setUniversityMail($university_mail);
                $university_user->setUser($this->getUser());

                $this->getDoctrine()->getEntityManager()->persist($university_user);
                $this->getDoctrine()->getEntityManager()->flush();

            // -- 2.3 -- Get University Name
                $university = $this->getDoctrine()->getRepository('AppBundle:University')->find($university_mail->getUniversity()->getId());

                $resultArray = array();
                $resultArray['mail'] = $university_user_mail;
                $resultArray['university'] = $university->getName();


            // -- 2.4 -- Return Result
                $response->setContent(json_encode(Result::$SUCCESS->setContent( $resultArray )));
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
