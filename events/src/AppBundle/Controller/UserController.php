<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\UniversityUser;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ChangePasswordType;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Acl;
use AppBundle\Entity\Utils;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

/**
 * @Route("/user", name="user")
 */
class UserController extends Controller
{


    /**
     * @Route("/a/profile", name="admin_profile")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function adminProfileAction(Request $request)
    {
        return $this->render('AppBundle:user:admin_profile.html.twig', array(
            'aa'=>'aa',
        ));

    }



    /**
     * @Route("/profile/about", name="user_profile_account")
     * @Security("has_role('ROLE_USER')")
     */
    public function userProfileAction(Request $request)
    {
        $data = array();
        $data['user'] = $this->getUser();
        $data['isProfileOwner'] = true;
        $data['hasCommunityAdminRole'] = $this->getDoctrine()->getRepository('AppBundle:User')->hasUserCommunityAdmin($this->getUser());
        $em = $this->getDoctrine()->getManager();

        // 1) POST OPERATION
        if ($request->getMethod() == 'POST') {

            try {
                // --1.1-- Get post parameter
                $user_name = $request->get('uname');
                $user_surname = $request->get('usurname');
                $user_sex = intval($request->get('usex'));
                $user_uphone = $request->get('uphone');
                $user_image = $request->get('profile_image_base64');

                if(strlen($user_image)>0){
                    $fileName = "";
                    if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                        $fileName .= "dev/";
                    }
                    $extension = substr($user_image, 0, strpos($user_image, ";"));
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

                    $user_image = Utils::uploadBase64ToServer($user_image, $fileName);
                }

                $user_birthdate = \DateTime::createFromFormat('m/d/Y', $request->get('ubirthdate'));
                // --1.2-- check that user exist
                $user = $this->getUser();
                if ($user) {
                    $user->setName($user_name);
                    $user->setSurname($user_surname);
                    $user->setPhone($user_uphone);
                    if(strlen($user_image)>0){
                        $user->setImageBase64($user_image);
                    }

                    $user->setSex($user_sex);

                    if ($user_birthdate)
                        $user->setBirthDate($user_birthdate);

                    $em->persist($user);
                    $em->flush();

                    $data['success_msg'] = 'Kullanıcı bilgileri güncellendi';

                } else {
                    $data['error_msg'] = 'Lütfen öncelikle giriş yapınız';
                }


            } catch (Exception $e) {
            }
        }

        return $this->render('AppBundle:user:profile_settings_account.html.twig', $data);
    }

    function base64_to_jpeg($base64_string, $output_file) {
        $ifp = fopen($output_file, "wb");

        $data = explode(',', $base64_string);

        fwrite($ifp, base64_decode($data[1]));
        fclose($ifp);

        return $output_file;
    }


    /**
     * @Route("/profile/about/{userId}", name="user_profile_with_id")
     */
    public function userProfileWithIdAction($userId)
    {
        $data = array();
        $em = $this->getDoctrine()->getManager();
        $data['isProfileOwner'] = false;

        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($userId);
        if($user){
            
            // Profil sayfasına dışarıdan erişim istenirse
            if($this->getUser() && $this->getUser()->getId()){
                $data['isProfileOwner'] = $userId ? $userId==$this->getUser()->getId() : true;
            }else{
                $data['isProfileOwner'] = false;
            }
        }

        $data['user'] = $user;
        $data['urlContinueWithId'] = true;
        return $this->render('AppBundle:user:profile_settings_account.html.twig', $data);
    }




    /**
     * @Route("/profile/mail", name="user_profile_mail")
     * @Security("has_role('ROLE_USER')")
     */
    public function userProfileMailAction(Request $request)
    {
        $data = array();
        $em = $this->getDoctrine()->getManager();
        $data['hasCommunityAdminRole'] = $this->getDoctrine()->getRepository('AppBundle:User')->hasUserCommunityAdmin($this->getUser());

        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            try{
                // --1.1-- Get post parameter
                $mailAddress = $request->get('mail_address');
                if(!$mailAddress){
                    $data['error_msg']='Mail adresi boş olamaz';
                }else{

                    // --1.2.1-- check if mail address is valid

                    $emailConstraint = new EmailConstraint();

                    $mailErrors = $this->get('validator')->validateValue(
                        $mailAddress,
                        $emailConstraint 
                    );

                    $isUserMailValid = false;
                    if (count($mailErrors) == 0 && strlen( $mailAddress) <= 255) { //Mail adresleri 255 karakterden fazla olamaz
                        
                        $isUserMailValid = true;
                    }

                    // --1.2.2-- check that user has mail_address
                    $userUniversityMail = $em->getRepository('AppBundle:UniversityUser')->findBy(array('user'=>$this->getUser()->getId() , 'email'=>$mailAddress));
                    if($userUniversityMail){

                        $data['error_msg'] = 'Eklenmek istenen mail adresi zaten bulunmaktadır';
                    }

                    else if( !$isUserMailValid) {

                        $data['error_msg'] = 'Mail adresi geçerli değildir';
                    }

                    else {
                        $universityUser = new UniversityUser();
                        $universityUser->setEmail($mailAddress);
                        $universityUser->setIsValidated(false);
                        $universityUser->setRegisterDate(new \DateTime());
                        $universityUser->setUpdateDate(new \DateTime());
                        $universityUser->setUser($this->getUser());

                        // TODO: university_id değeri kaldırılacaktır
                        // Kişinin mail adresini update etmesi durumunda okul mail adresini kontrol edilmesi yerine, okul mail adresine uygun ise adres kişiye okul etkinliklerine erişim hakkı verilir.
                        $universityUser->setUniversityMail($em->getRepository('AppBundle:UniversityMail')->find(21));

                        $em->persist($universityUser);
                        $em->flush();

                        $data['success_msg'] = 'Mail Başarılı Şekilde Eklenmiştir';
                    }
                }

            } catch (Exception $e){}
        }

        $userUniversityMailList = $em->getRepository('AppBundle:UniversityUser')->findBy(array('user'=>$this->getUser()->getId()));
        $data['userUniversityMailList'] = $userUniversityMailList;
        $data['user'] = $this->getUser();
        $data['isProfileOwner'] = true;
        return $this->render('AppBundle:user:profile_settings_mail.html.twig', $data);
    }



    /**
     * @Route("/profile/mail/{userId}", name="user_profile_mail_with_id")
     */
    public function userProfileMailWithIdAction($userId)
    {
        $data = array();
        $em = $this->getDoctrine()->getManager();

        $data['isProfileOwner'] = false;
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($userId);
        if($user){

            // Profil sayfasına dışarıdan erişim istenirse
            if($this->getUser() && $this->getUser()->getId()){
                $data['isProfileOwner'] = $userId ? $userId==$this->getUser()->getId() : true;
            }else{
                $data['isProfileOwner'] = false;
            }

            $userUniversityMailList = $em->getRepository('AppBundle:UniversityUser')->findBy(array('user'=>$user->getId()));
            $data['userUniversityMailList'] = $userUniversityMailList;
            $data['user'] = $user;
        }

        $data['urlContinueWithId'] = true;
        return $this->render('AppBundle:user:profile_settings_mail.html.twig', $data);
    }


    /**
     * @Route("/profile/password", name="user_profile_password")
     * @Security("has_role('ROLE_USER')")
     */
    public function userProfilePasswordAction(Request $request)
    {

        $data = array();
        $em = $this->getDoctrine()->getManager();
        $data['hasCommunityAdminRole'] = $this->getDoctrine()->getRepository('AppBundle:User')->hasUserCommunityAdmin($this->getUser());

        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            try{
                // --1.1-- Get post parameters
                $oldPassword = $request->get('oldPassword');
                $newPassword = $request->get('newPassword');
                $newPasswordRep = $request->get('newPasswordRep');

                if(!$oldPassword || !$newPassword || !$newPasswordRep) {
                    $data['error_msg'] = 'Şifre boş bırakılamaz';
                }else if($newPassword!=$newPasswordRep){
                    $data['error_msg'] = 'Şifreleriniz uyumsuz';
                }else{

                    $encoder = $this->container->get('security.password_encoder');
                    $valid = $encoder->isPasswordValid($this->getUser(), $oldPassword);

                    if($valid){
                        $user = $this->getUser();
                        $password = $this->get('security.password_encoder')->encodePassword($user, $newPassword);
                        $user->setPassword($password);

                        $em->persist($user);
                        $em->flush();

                        $data['success_msg'] = 'Şifre başarılı şekilde değiştirildi.';
                    }else{
                        $data['error_msg'] = 'Eski şifrenizi yanlış girdiniz.';
                    }

                }

            } catch (Exception $e){}
        }

        $data['user'] = $this->getUser();
        $data['isProfileOwner'] = true;
        return $this->render('AppBundle:user:profile_settings_password.html.twig', $data);
    }


    /**
     * @Route("/profile/badges", name="user_profile_badges")
     * @Security("has_role('ROLE_USER')")
     */
    public function userProfileBadgesAction(Request $request)
    {

        $data = array();
        $em = $this->getDoctrine()->getManager();
        $data['hasCommunityAdminRole'] = $this->getDoctrine()->getRepository('AppBundle:User')->hasUserCommunityAdmin($this->getUser());

        $data['user'] = $this->getUser();
        $data['isProfileOwner'] = true;
        return $this->render('AppBundle:user:profile_badges.html.twig', $data);
    }


    /**
     * @Route("/profile/badges/{userId}", name="user_profile_badges_with_id")
     */
    public function userProfileBadgesWithIdAction($userId)
    {

        $data = array();
        $em = $this->getDoctrine()->getManager();

        $data['isProfileOwner'] = false;
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($userId);
        if($user){

            // Profil sayfasına dışarıdan erişim istenirse
            if($this->getUser() && $this->getUser()->getId()){
                $data['isProfileOwner'] = $userId ? $userId==$this->getUser()->getId() : true;
            }else{
                $data['isProfileOwner'] = false;
            }

            $data['user'] = $user;
        }

        $data['urlContinueWithId'] = true;
        return $this->render('AppBundle:user:profile_badges.html.twig', $data);
    }


    /**
     * @Route("/profile/communities", name="user_profile_communities")
     * @Security("has_role('ROLE_USER')")
     */
    public function userProfileCommunitiesAction(Request $request)
    {

        $data = array();
        $em = $this->getDoctrine()->getManager();
        $data['hasCommunityAdminRole'] = $this->getDoctrine()->getRepository('AppBundle:User')->hasUserCommunityAdmin($this->getUser());

        $data['communityUserAdminRoles'] = $em->getRepository('AppBundle:CommunityUserRoles')->findCommunityAdminRoles($this->getUser());
        $data['communityUserMemberRoles'] = $em->getRepository('AppBundle:CommunityUserRoles')->findCommunityMemberRoles($this->getUser());
        $data['communityUserApplyRoles'] = $em->getRepository('AppBundle:CommunityUserRoles')->findCommunityAppyRoles($this->getUser());

        $data['user'] = $this->getUser();
        $data['isProfileOwner'] = true;
        return $this->render('AppBundle:user:profile_settings_communities.html.twig', $data);
    }


    /**
     * @Route("/profile/communities/{userId}", name="user_profile_communities_with_id")
     */
    public function userProfileCommunitiesWithIdAction($userId)
    {

        $data = array();
        $em = $this->getDoctrine()->getManager();

        $data['isProfileOwner'] = false;
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($userId);
        if($user){

            // Profil sayfasına dışarıdan erişim istenirse
            if($this->getUser() && $this->getUser()->getId()){
                $data['isProfileOwner'] = $userId ? $userId==$this->getUser()->getId() : true;
            }else{
                $data['isProfileOwner'] = false;
            }

            $data['communityUserAdminCommunities'] = $em->getRepository('AppBundle:CommunityUser')->findBy(array('status'=>1 , 'user'=>$user));
            $data['communityUserMemberCommunities'] = $em->getRepository('AppBundle:CommunityUser')->findBy(array('status'=>2 , 'user'=>$user));
            $data['communityUserApplicationsCommunities'] = $em->getRepository('AppBundle:CommunityUser')->findBy(array('status'=>10 , 'user'=>$user));
            $data['user'] = $user;
        }

        $data['urlContinueWithId'] = true;
        return $this->render('AppBundle:user:profile_settings_communities.html.twig', $data);
    }
}
