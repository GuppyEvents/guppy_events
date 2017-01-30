<?php

namespace AppBundle\Entity;
use Google\Cloud\Storage\StorageClient;
use Google_Client;
use Google_Service_Storage;
use Google_Service_Storage_StorageObject;
use Google\Cloud\Storage\Acl;

class Utils
{
    /*
     *
     * Uploads the file to google cloud storage and
     * returns google cloud link
     * putenv('GOOGLE_APPLICATION_CREDENTIALS=/var/www/events/key_27_ocak_14_05.json');
     * should be called once to put envvironment variable to server machine
     *
     */
    public static function uploadBase64ToServer($imageBase64, $fileName){
        putenv('GOOGLE_APPLICATION_CREDENTIALS=/var/www/events/key_27_ocak_14_05.json');
        $result=null;
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(\Google_Service_Storage::DEVSTORAGE_FULL_CONTROL);
        $bucket	= "seruvent_images";


        $data = explode(',', $imageBase64);

        $file_content = base64_decode($data[1]);


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
                'predefinedAcl' =>'publicRead'

            );
            $gsso = new Google_Service_Storage_StorageObject();
            $gsso->setName( $fileName );
            $result = $storageService->objects->insert( $bucket, $gsso, $postbody );
            if($result != null){
                return "https://storage.googleapis.com/seruvent_images/" . $fileName;
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
}

?>