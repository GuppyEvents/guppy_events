<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\UniversityUser;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Model\ChangePassword;
use AppBundle\Form\ChangePasswordType;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

/**
 * @Route("/user", name="user")
 * @Security("has_role('ROLE_USER')")
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
     * @Route("/profile", name="user_profile_account")
     * @Security("has_role('ROLE_USER')")
     */
    public function userProfileAction(Request $request)
    {
        $data = array();
        $em = $this->getDoctrine()->getManager();

        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            try{
                // --1.1-- Get post parameter
                $user_name = $request->get('uname');
                $user_surname = $request->get('usurname');
                $user_sex = intval($request->get('usex'));
                $user_uphone = $request->get('uphone');
                $user_image = $request->get('profile_image_base64');
                $user_birthdate = \DateTime::createFromFormat('m/d/Y', $request->get('ubirthdate'));

                // --1.2-- check that user exist
                $user = $this->getUser();
                if($user){
                    $user->setName($user_name);
                    $user->setSurname($user_surname);
                    $user->setPhone($user_uphone);
                    $user->setImageBase64($user_image);
                    $user->setSex($user_sex);

                    if($user_birthdate)
                        $user->setBirthDate($user_birthdate);

                    $em->persist($user);
                    $em->flush();

                    $data['success_msg'] = 'Kullanıcı bilgileri güncellendi';

                }else {
                    $data['error_msg'] = 'Lütfen öncelikle giriş yapınız';
                }


            } catch (Exception $e){}
        }

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

        // 1) POST OPERATION
        if($request->getMethod() == 'POST'){

            try{
                // --1.1-- Get post parameter
                $mailAddress = $request->get('mail_address');
                if(!$mailAddress){
                    $data['error_msg']='Mail adresi boş olamaz';
                }else{

                    // --1.2-- check that user has mail_address
                    $userUniversityMail = $em->getRepository('AppBundle:UniversityUser')->findBy(array('user'=>$this->getUser()->getId() , 'email'=>$mailAddress));
                    if($userUniversityMail){
                        $data['error_msg'] = 'Eklenmek istenen mail adresi zaten bulunmaktadır';

                    }else {
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

        return $this->render('AppBundle:user:profile_badges.html.twig', $data);
    }
}
