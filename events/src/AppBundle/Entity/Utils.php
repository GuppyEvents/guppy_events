<?php

namespace AppBundle\Entity;
use Google\Cloud\Storage\StorageClient;
use Google_Client;
use Google_Service_Storage;
use Google_Service_Storage_StorageObject;
use Google\Cloud\Storage\Acl;
use Mailgun\Mailgun;
use Facebook\Facebook;

class Utils
{
    /*
     *
     * Uploads the file to google cloud storage and
     * returns google cloud link
     * putenv('GOOGLE_APPLICATION_CREDENTIALS=/var/www/events/google-api/guppy-seruvent-180809-baa920e18626.json');
     * should be called once to put envvironment variable to server machine
     *
     */
    public static function uploadBase64ToServer($imageBase64, $fileName){
        putenv('GOOGLE_APPLICATION_CREDENTIALS=/var/www/events/google-api/guppy-seruvent-180809-baa920e18626.json');
        $result=null;
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(\Google_Service_Storage::DEVSTORAGE_FULL_CONTROL);

        $bucket	= "seruvent_storage";


        $data = explode(',', $imageBase64);

        $file_content = base64_decode($data[1]);

        $f = finfo_open();

        $mime_type = finfo_buffer($f, $file_content, FILEINFO_MIME_TYPE);


        $storageService = new Google_Service_Storage($client);
        /***
         * Write file to Google Storage
         */
        try
        {
            $postbody = array(
                'name' => $fileName,
                'data' => $file_content,
                'uploadType' => 'media',
                'predefinedAcl' =>'publicRead',
                'mimeType' => $mime_type

            );
            $gsso = new Google_Service_Storage_StorageObject();
            $gsso->setName( $fileName );
            $result = $storageService->objects->insert( $bucket, $gsso, $postbody );
            if($result != null){
                return "https://storage.googleapis.com/seruvent_storage/" . $fileName;
            }
        }
        catch (Exception $e)
        {
            print $e->getMessage();
        }
        return null;
    }

    /*
     *
     * Uploads the file to google cloud storage and
     * returns google cloud link
     * putenv('GOOGLE_APPLICATION_CREDENTIALS=/var/www/events/google-api/guppy-seruvent-180809-baa920e18626.json');
     * should be called once to put envvironment variable to server machine
     *
     */
    public static function uploadBytesToServer($imageBytes, $fileName){
        putenv('GOOGLE_APPLICATION_CREDENTIALS=/var/www/events/google-api/guppy-seruvent-180809-baa920e18626.json');
        $result=null;
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(\Google_Service_Storage::DEVSTORAGE_FULL_CONTROL);

        $bucket	= "seruvent_storage";




        $file_content = $imageBytes;

        $f = finfo_open();

        $mime_type = finfo_buffer($f, $file_content, FILEINFO_MIME_TYPE);


        $storageService = new Google_Service_Storage($client);
        /***
         * Write file to Google Storage
         */
        try
        {
            $postbody = array(
                'name' => $fileName,
                'data' => $file_content,
                'uploadType' => 'media',
                'predefinedAcl' =>'publicRead',
                'mimeType' => $mime_type

            );
            $gsso = new Google_Service_Storage_StorageObject();
            $gsso->setName( $fileName );
            $result = $storageService->objects->insert( $bucket, $gsso, $postbody );
            if($result != null){
                return "https://storage.googleapis.com/seruvent_storage/" . $fileName;
            }
        }
        catch (Exception $e)
        {
            print $e->getMessage();
        }
        return null;
    }

    public static function getGUID()
    {

        mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }

    public static function mailSendSingle($toAddress, $subject, $content){
        # First, instantiate the SDK with your API credentials and define your domain.
        $mg = new Mailgun("key-0bf7967ba162156ff166b8151ec9d4b6");
        $domain = "mg.seruvent.com";

        # Now, compose and send your message.
        $mg->sendMessage($domain, array('from'    => 'Seruvent Team<info@seruvent.com>',
            'to'      => $toAddress,
            'subject' => $subject,
            'text'    => $content));
    }

    public static function setUserCanAddEvent($session, $canAdd){
        $session->set('user_can_add_event', $canAdd);
    }

    public static function getUserCanAddEvent($env){
        $session = $env->get('session');
        return $session->get('user_can_add_event');
        //return $_SESSION['user_can_add_event'];
    }

    public static function getSessionToastMessages(){
        $messages = array();
        if(isset($_SESSION['success_message'])){
            $messages["success_msg"] = $_SESSION['success_message'];
            unset($_SESSION['success_message']); // clear the value so that it doesn't display again
        }
        if(isset($_SESSION['warning_message'])){
            $messages["warning_msg"] = $_SESSION['warning_message'];
            unset($_SESSION['warning_message']); // clear the value so that it doesn't display again
        }
        if(isset($_SESSION['error_message'])){
            $messages["error_msg"] = $_SESSION['error_message'];
            unset($_SESSION['error_message']); // clear the value so that it doesn't display again
        }
        return $messages;
    }

    public static function getFbUserFromFbToken($token){
        $fb = new Facebook([
            'app_id' => '1807236222874605',
            'app_secret' => '5d206a0119067c93b68055bfc3082fcf',
            'default_graph_version' => 'v2.8',
            //'default_access_token' => '{access-token}', // optional
        ]);

        try {
            $response = $fb->get('/me?fields=name,email,id,picture.width(500).height(500)', $token);
            return $response->getGraphUser();
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            //echo 'Graph returned an error: ' . $e->getMessage();
            //exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            //echo 'Facebook SDK returned an error: ' . $e->getMessage();
            //exit;
        }
    }
}

?>