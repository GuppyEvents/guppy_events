<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Result;
use AppBundle\Entity\University;
use AppBundle\Entity\UniversityMail;
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

        return $this->render(
            'AppBundle:settings:settingsEmail.html.twig', array(
                'universityMails' => '',
            )
        );
    }
}
