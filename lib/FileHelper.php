<?php

namespace lib;

use TCPDF;


class FileHelper
{
    public const DIRECTORY_PERMISSION = 0755;


    /**
     * Function checks whether a specified directory exists.
     * If not, this will be created.
     *
     * @param string $path
     *
     * @return void
     */
    public static function checkDirectory(string $path) :void
    {
        if( !is_dir( $path ) )
        {
            mkdir( $path, self::DIRECTORY_PERMISSION );
        }
    }


    /**
     * Function gets MIME Type from given file
     *
     * @param string $file
     *
     * @return false|string
     */
    public static function getMimeType(string $file) :false|string
    {
        $info     = finfo_open( FILEINFO_MIME_TYPE );
        $mimeType = finfo_file( $info, $file );
        finfo_close( $info );

        return $mimeType;
    }


    /**
     * Function fetches the uploaded files and performs a standard validation.
     * An array containing the correct files is then returned.
     *
     * @param array $allowedMimeTypes
     *
     * @return array|null
     */
    public static function checkUploadedFiles(array $allowedMimeTypes) :?array
    {
        $uploadedFiles = $_FILES['fileInput'];

        // check if a file was selected
        if( empty( $uploadedFiles['tmp_name'] ) )
        {
            Html::addAlert( 'danger', 'Es wurden keine Dateien ausgewählt' );
            return null;
        }

        $validatedFiles = [];

        if( is_array( $uploadedFiles['tmp_name'] ) )
        {
            foreach( $uploadedFiles['tmp_name'] as $key => $values )
            {
                $file             = [];
                $file['name']     = $uploadedFiles['name'][$key];
                $file['type']     = $uploadedFiles['type'][$key];
                $file['tmp_name'] = $uploadedFiles['tmp_name'][$key];
                $file['error']    = $uploadedFiles['error'][$key];
                $file['size']     = $uploadedFiles['size'][$key];

                if( self::validateFile( $file, $allowedMimeTypes ) )
                {
                    $validatedFiles[] = $file;
                }
            }

            return $validatedFiles;
        }
        elseif( is_string( $uploadedFiles['tmp_name'] ) )
        {
            if( self::validateFile( $uploadedFiles, $allowedMimeTypes ) )
            {
                $validatedFiles[] = $uploadedFiles;
            }
        }

        if( empty( $validatedFiles ) )
        {
            return null;
        }

        return $validatedFiles;
    }


    /**
     * Validate uploaded files
     *
     * @param array $file
     * @param array $allowedMimeTypes
     *
     * @return bool
     */
    private static function validateFile(array $file, array $allowedMimeTypes) :bool
    {
        // check no errors
        if( $file['error'] !== 0 )
        {
            Html::addAlert( 'danger', 'Bei der Datei <strong>"' . $file['name'] . '"</strong>, ist folgender Fehler aufgetreten: <strong>"' . $file['error'] . '"</strong>' );
            return false;
        }

        // check file exists
        if( !file_exists( $file['tmp_name'] ) || is_dir( $file['tmp_name'] ) )
        {
            Html::addAlert( 'danger', 'Die Datei <strong>"' . $file['name'] . '"</strong> wurde nicht korrekt hochgeladen.' );
            return false;
        }

        // check mime type
        if( !array_key_exists( self::getMimeType( $file['tmp_name'] ), $allowedMimeTypes ) )
        {
            Html::addAlert( 'danger', 'Die Datei <strong>"' . $file['name'] . '"</strong> ist keine erlaubte Datei und wurde nicht hochgeladen.' );
            return false;
        }

        return true;
    }


    /**
     * generates a pdf/a from uploaded file
     *
     * @param string $filePath
     * @param string $fileExtension
     * @param string $originFileName
     * @param string $targetFileName
     *
     * @return bool
     */
    public static function convertToPDFA(string $filePath, string $fileExtension, string $originFileName, string $targetFileName) :bool
    {
        // create new PDF document
        $pdf = new TCPDF( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true );

        // set document information
        $pdf->SetCreator( 'Thrömer Portal' );
        $pdf->SetAuthor( 'Martin Thrömer' );
        $pdf->SetTitle( $originFileName );

        // set default header data
        $pdf->SetHeaderData();

        // set header and footer fonts
        $pdf->setHeaderFont( [
                                 PDF_FONT_NAME_MAIN,
                                 '',
                                 PDF_FONT_SIZE_MAIN,
                             ] );
        $pdf->setFooterFont( [
                                 PDF_FONT_NAME_DATA,
                                 '',
                                 PDF_FONT_SIZE_DATA,
                             ] );

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont( PDF_FONT_MONOSPACED );

        // set margins
        $pdf->SetMargins( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
        $pdf->SetHeaderMargin( PDF_MARGIN_HEADER );
        $pdf->SetFooterMargin( PDF_MARGIN_FOOTER );

        // set auto page breaks
        $pdf->SetAutoPageBreak( true, PDF_MARGIN_BOTTOM );

        // set image scale factor
        $pdf->setImageScale( PDF_IMAGE_SCALE_RATIO );

        // add a page
        $pdf->AddPage();

        // set JPEG quality
        $pdf->setJPEGQuality( 100 );

        $pdf->Image( $filePath . '.' . $fileExtension, null, null, 0, 0, '', '', '', false, 300, '', false, false, 0, false, false, true );

        $pdf->Output( $targetFileName, 'F' );


        if( !file_exists( $targetFileName ) )
        {
            return false;
        }

        return true;
    }
}