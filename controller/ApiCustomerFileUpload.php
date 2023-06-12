<?php

namespace controller;

use CURLFile;
use lib\DebugHelper;
use lib\FileHelper;
use stdClass;


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

    private array $postData = [
        'token' => API_CUSTOMER_FILE_UPLOAD_TOKEN,
    ];


    public function __construct()
    {
        if( $this->collectFiles() === 0 )
        {
            exit();
        }

        $processedFiles = $this->sendRequest();
        $this->processFiles( $processedFiles );
    }

    /**
     * Function collect and prepare files for sending
     *
     * @return int
     */
    private function collectFiles() :int
    {
        // scan directory and get files
        $files = scandir( DATA );
        // shuffle $files array
        shuffle( $files );

        $fileCount = 0;

        foreach( $files as $file )
        {
            if( $fileCount >= self::MAX_FILE_COUNT )
            {
                break;
            }

            // ignore directory links
            if( $file === '.' || $file === '..' )
            {
                continue;
            }

            // ignore files with "exists__exists__" prefix. These files processed already 2 times and failed
            if( str_starts_with( $file, 'exists__exists__' ) )
            {
                continue;
            }
            elseif( str_starts_with( $file, 'cant_move__cant_move__' ) )
            {
                continue;
            }
            elseif( str_starts_with( $file, 'cant_convert__cant_convert__' ) )
            {
                continue;
            }
            elseif( str_starts_with( $file, 'cant_save__cant_save__' ) )
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

    /**
     * Function send files to api
     *
     * @return array
     */
    private function sendRequest() :stdClass
    {
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, API_CUSTOMER_FILE_UPLOAD_URL );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

        curl_setopt( $curl, CURLOPT_POST, true );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $this->postData );

        curl_setopt( $curl, CURLOPT_USERPWD, API_CUSTOMER_FILE_UPLOAD_API_USER . ':' . API_CUSTOMER_FILE_UPLOAD_API_PASSWORD );

        // only for testing
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

        $result = json_decode( curl_exec( $curl ) );
        curl_close( $curl );
        DebugHelper::pre( $result );

        if( isset( $result->success ) && !$result->success )
        {
            echo 'An error occurred.';
            exit();
        }

        return $result->processedFiles;
    }

    /**
     * Function rename or delete processed files
     *
     * @param array $processedFiles
     *
     * @return void
     */
    private function processFiles(stdClass $processedFiles) :void
    {
        foreach( $processedFiles as $fileName => $responseCode )
        {
            switch( $responseCode )
            {
                case 0:
                    // delete file
                    var_dump( "delete file" );
                    var_dump( unlink( DATA . $fileName ) );
                    break;

                case 1:
                    // file exists, rename file
                    var_dump( "file exists" );
                    var_dump( rename( DATA . $fileName, DATA . 'exists__' . $fileName ) );
                    break;
                case 2:
                    // file cant move, rename file2
                    rename( DATA . $fileName, DATA . 'cant_move__' . $fileName );
                    break;
                case 3:
                    // file cant converted to pdf/a, rename file
                    rename( DATA . $fileName, DATA . 'cant_convert__' . $fileName );
                    break;
                case 4:
                    // file cant save to database, rename file
                    rename( DATA . $fileName, DATA . 'cant_save__' . $fileName );
                    break;
            }
        }
    }
}