<?php

namespace lib;

use DateTime;
use Exception;


class FunctionalHelper
{
    /**
     * Returns the post value, or an empty string if no value was posted
     *
     * @param string $name
     * @param string $type
     *
     * @return string|array
     */
    public static function post(string $name, string $type = 'string') :string|array
    {
        if( isset( $_POST[$name] ) )
        {
            if( $type === 'array' )
            {
                return $_POST[$name];
            }

            return TextHelper::encode( $_POST[$name] );
        }

        return '';
    }


    /**
     * Returns the get value, or an empty string if no value was got
     *
     * @param string $name
     *
     * @return string
     */
    public static function get(string $name) :string
    {
        $getValue = '';

        if( isset( $_GET[$name] ) )
        {
            $getValue = TextHelper::encode( $_GET[$name] );
        }

        return $getValue;
    }


    /**
     * Generates uniqid for submit-token check
     *
     * @return string
     */
    public static function submitToken() :string
    {
        return uniqid();
    }


    /**
     * Generates unique random string. Default length is 8 chars
     *
     * @param int $length
     *
     * @return string
     * @throws Exception
     */
    public static function getRandomString(int $length = 8) :string
    {
        return bin2hex( random_bytes( $length / 2 ) );
    }


    /**
     * Generates unique random pin. Default length is 4 numbers
     *
     * @param int $length
     *
     * @return int
     */
    public static function getRandomPin(int $length = 4) :int
    {
        $min = '';
        $max = '';
        for( $i = 1; $i <= $length; $i++ )
        {
            $min .= '1';
            $max .= '9';
        }
        return rand( (int) $min, (int) $max );
    }


    /**
     * creates redirect URL with get values
     *
     * @param string $url
     * @param array  $values
     *
     * @return void
     */
    public static function redirect(string $url = '', array $values = []) :void
    {
        if( $url === '' )
        {
            die( 'Redirect failed' );
        }

        $getValues = '';
        if( !empty( $values ) )
        {
            $getValues  .= '?';
            $valueCount = count( $values );

            foreach( $values as $name => $value )
            {
                $getValues .= $name . '=' . $value;

                $valueCount--;
                if( $valueCount !== 0 )
                {
                    $getValues .= '&';
                }
            }
        }

        $completeUrl = $url . $getValues;
        header( "Location: $completeUrl" );
        exit();
    }


    /**
     * Function calculates the difference of 2 dates and formats them in text form.
     * If not specified, the 2nd parameter is used with the current date
     *
     * @param string $date
     *
     * @return string
     * @throws Exception
     */
    public static function getDateDifference(string $date) :string
    {
        $startDate   = new DateTime( $date );
        $currentDate = new DateTime();
        $interval    = $startDate->diff( $currentDate );
        $day         = (int) $interval->format( '%d' );
        $month       = (int) $interval->format( '%m' );

        $string      = 'heute';
        $monthString = '';
        $dayString   = '';

        if( 0 < $month )
        {
            if( 1 === $month )
            {
                $monthString .= $month . ' Monat';
            }
            elseif( 1 < $month )
            {
                $monthString .= $month . ' Monate';
            }

            $string = 'vor ' . $monthString;
        }

        if( 0 < $day )
        {
            if( 1 === $day )
            {
                $dayString .= $day . ' Tag';
            }
            elseif( 1 < $day )
            {
                $dayString .= $day . ' Tage';
            }


            if( $month === 0 )
            {
                $string = 'vor ' . $dayString;
            }
            else
            {
                $string .= ' und ' . $dayString;
            }
        }


        return $string;
    }


    /**
     * @param $plaintext
     * @param $password
     *
     * @return string
     */
    public static function encrypt($plaintext, $token) :string
    {
        $method = "AES-256-CBC";
        $key    = hash( 'sha256', $token, true );
        $iv     = openssl_random_pseudo_bytes( 16 );

        $ciphertext = openssl_encrypt( $plaintext, $method, $key, OPENSSL_RAW_DATA, $iv );
        $hash       = hash_hmac( 'sha256', $ciphertext . $iv, $key, true );

        return base64_encode( $iv . $hash . $ciphertext );
    }


    /**
     * @param $encryptedText
     * @param $password
     *
     * @return string|null
     */
    public static function decrypt($encryptedText, $token) :?string
    {
        $encryptedText = base64_decode( $encryptedText );

        $method     = "AES-256-CBC";
        $iv         = substr( $encryptedText, 0, 16 );
        $hash       = substr( $encryptedText, 16, 32 );
        $ciphertext = substr( $encryptedText, 48 );
        $key        = hash( 'sha256', $token, true );

        if( !hash_equals( hash_hmac( 'sha256', $ciphertext . $iv, $key, true ), $hash ) )
        {
            return null;
        }

        return openssl_decrypt( $ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv );
    }


    /**
     * generates a password like "pmbrq0-od27xi-2q6w65"
     *
     * @param int $length
     *
     * @return string
     */
    public static function getPassword(int $length = 20) :string
    {
        $numbers  = [
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
        ];
        $chars    = [
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'n',
            'w',
            'x',
            'y',
            'z',
        ];
        $specials = [
            '!',
            '$',
            '%',
            '&',
            '-',
            '_',
            '=',
            ':',
            ';',
            '@',
            '?',
            '~',
            '#',
            '/',
        ];
        $password = '';

        for( $i = 0; $i < $length; $i++ )
        {
            $charOrNumber = rand( 1, 100 );

            // 80% chance for char
            if( $charOrNumber <= 80 )
            {
                $char       = $chars[mt_rand( 0, sizeof( $chars ) - 1 )];
                $bigOrSmall = rand( 1, 100 );

                // 25% chance for upper char
                if( $bigOrSmall <= 25 )
                {
                    $char = strtoupper( $char );
                }
                $password .= $char;
            }
            // 20% chance for number
            elseif( $charOrNumber >= 81 )
            {
                $password .= $numbers[mt_rand( 0, sizeof( $numbers ) - 1 )];
            }

            // add "-" in pw string
            if( $i === 5 )
            {
                $password .= '-';
                $i++;
            }
            if( $i === 12 )
            {
                $password .= '-';
                $i++;
            }
        }

        return $password;
    }
}