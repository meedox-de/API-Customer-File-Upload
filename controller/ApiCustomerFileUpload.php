<?php

namespace controller;

use CURLFile;
use lib\DebugHelper;
use lib\FileHelper;


class ApiCustomerFileUpload
{
    private const MIME_TYPES_PDF        = 'application/pdf';
    private const MIME_TYPES_IMAGE_HEIC = 'image/heic';
    private const MIME_TYPES_IMAGE_JPG  = 'image/jpg';
    private const MIME_TYPES_IMAGE_JPEG = 'image/jpeg';
    private const MIME_TYPES_IMAGE_GIF  = 'image/gif';
    private const MIME_TYPES_IMAGE_PNG  = 'image/png';

    private const EXTENSION_PDF        = 'pdf';
    private const EXTENSION_IMAGE_HEIC = 'heic';
    private const EXTENSION_IMAGE_JPG  = 'jpg';
    private const EXTENSION_IMAGE_JPEG = 'jpeg';
    private const EXTENSION_IMAGE_GIF  = 'gif';
    private const EXTENSION_IMAGE_PNG  = 'png';

    private const ALLOWED_MIME_TYPES = [
        self::MIME_TYPES_PDF        => self::EXTENSION_PDF,
        self::MIME_TYPES_IMAGE_HEIC => self::EXTENSION_IMAGE_HEIC,
        self::MIME_TYPES_IMAGE_JPG  => self::EXTENSION_IMAGE_JPG,
        self::MIME_TYPES_IMAGE_JPEG => self::EXTENSION_IMAGE_JPEG,
        self::MIME_TYPES_IMAGE_GIF  => self::EXTENSION_IMAGE_GIF,
        self::MIME_TYPES_IMAGE_PNG  => self::EXTENSION_IMAGE_PNG,
    ];

    private const MAX_FILE_SIZE  = 100000000;
    private const MAX_FILE_COUNT = 5;
    private const URL            = 'http://console.throemer.local?t=kd83k38c';
    private const TOKEN          = 'lkkf90dufuehjfkl';
    private const API_USER       = 'apiuser';
    private const API_PASSWORD   = 'kiov894ughjioj3212e2';

    private array $postData = [
        'token' => self::TOKEN,
    ];


    public function __construct()
    {
        if( $this->collectFiles() === 0 )
        {
            exit();
        }

        $this->sendRequest();


        $this->deleteFiles();
    }

    /**
     * Function collect and prepare files for sending
     *
     * @return int
     */
    private function collectFiles() :int
    {
        $files     = scandir( DATA );
        $fileCount = 0;

        foreach( $files as $file )
        {
            if( $fileCount >= self::MAX_FILE_COUNT )
            {
                break;
            }

            if( $file === '.' || $file === '..' )
            {
                continue;
            }

            $filePath = DATA . $file;

            if( is_dir( $filePath ) )
            {
                continue;
            }

            $mimeType = FileHelper::getMimeType( $filePath );
            if( !array_key_exists( $mimeType, self::ALLOWED_MIME_TYPES ) )
            {
                continue;
            }

            $fileSize = filesize( $filePath );

            if( $fileSize > self::MAX_FILE_SIZE )
            {
                continue;
            }

            $this->postData[] = new CURLFile( $filePath, $mimeType, $file );

            $fileCount++;
        }

        return $fileCount;
    }


    private function sendRequest()
    {
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, self::URL );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

        curl_setopt( $curl, CURLOPT_POST, true );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $this->postData );

        curl_setopt( $curl, CURLOPT_USERPWD, self::API_USER . ':' . self::API_PASSWORD );

        // only for testing
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

        $result       = curl_exec( $curl );
        $responseCode = curl_getinfo( $curl, CURLINFO_RESPONSE_CODE );
        curl_close( $curl );

        var_dump( $responseCode );
        var_dump( $result );
        DebugHelper::pre( json_decode( $result ) );
        die();

        if( $responseCode !== 200 )
        {
            exit();
        }
    }


    private function deleteFiles()
    {

    }
}