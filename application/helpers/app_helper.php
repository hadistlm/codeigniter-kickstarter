<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Function to generate random number
 *
 * @param $digits = How many number wants to outputed
 */
if (! function_exists('random_number')) {
	function random_number($digits) {
	    $min = pow(10, $digits - 1);
	    $max = pow(10, $digits) - 1;
	    return mt_rand($min, $max);
	}
}

/**
 * Checking in multidimensional array
 *
 */
if (! function_exists('in_array_r')) {
    function in_array_r($item = 'needle', $array = array()){
        return preg_match('/"'.preg_quote($item, '/').'"/i' , json_encode($array));
    }
}

/**
 * Function reformat word 
 */
if (! function_exists('format_word')) {
    function format_word($word){
        return ucfirst(str_replace('_', ' ', $word));
    }
}

/**
 * Function to strpos using in_array 
 */
if (! function_exists('strpos_arr')) {
    function strpos_arr($haystack, $needle, $offset=0) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $query) {
            if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
        }
        return false;
    }
}

/**
 * Function to sent email easily
 *
 */
if (! function_exists('email_helper')) {
    function email_helper($to = '', $subject = '', $message = '', $cc = '', $type = ''){
        $CI =& get_instance();

        $CI->load->library('email');

        $configs = array(
          'protocol'  => 'smtp',
          'smtp_host' => '', // SMTP HOST
          'smtp_user' => '', // SMTP EMAIL
          'smtp_pass' => '', // SMTP PASS
          'smtp_port' => '465',
          'crlf'      => "\r\n",
          'newline'   => "\r\n"
        );

        // if using template from HTML source
        if (!empty($type)) :
            $configHTML = array(
                'charset'   => 'utf-8',
                'wordwrap'  => TRUE,
                'mailtype'  => 'html'
            );

            $configs = array_merge_recursive($configs, $configHTML);
        endif;

        $CI->email->initialize($configs);
        $CI->email->set_mailtype("html");
        $CI->email->from('email@email.com', 'Sender Name');
        $CI->email->to($to);

        if (!empty($cc)) :
            $CI->email->cc($cc);
        endif;
        
        $CI->email->subject($subject);
        $CI->email->message($message);
        
        if (! $CI->email->send() ) {  
            // echo $CI->email->print_debugger();
            return 'Failed to send email';   
        }else{  
            return 'Success to send email';   
        }
    }
}

/**
 * Convert number with unit byte to bytes unit
 * @link https://en.wikipedia.org/wiki/Metric_prefix
 * @param string $value a number of bytes with optinal SI decimal prefix (e.g. 7k, 5mb, 3GB or 1 Tb)
 * @return integer|float A number representation of the size in BYTES (can be 0). otherwise FALSE
 * source : https://gist.github.com/Chengings/9597366
 */
if(!function_exists('str2bytes')){
    function str2bytes($value) {
        // only string
        $unit_byte = preg_replace('/[^a-zA-Z]/', '', $value);
        $unit_byte = strtolower($unit_byte);
        // only number (allow decimal point)
        $num_val = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        switch ($unit_byte) {
            case 'p':   // petabyte
            case 'P':
            case 'pb':
            case 'PB':
                $num_val *= 1024;
            case 't':   // terabyte
            case 'T':
            case 'tb':
            case 'TB':
                $num_val *= 1024;
            case 'g':   // gigabyte
            case 'G':
            case 'gb':
            case 'GB':
                $num_val *= 1024;
            case 'm':   // megabyte
            case 'M':
            case 'mb':
            case 'MB':
                $num_val *= 1024;
            case 'k':   // kilobyte
            case 'K':
            case 'kb':
            case 'KB':
                $num_val *= 1024;
            case 'b':   // byte
            case 'B':
            return $num_val *= 1;
                break; // make sure
            default:
                return FALSE;
        }
        return FALSE;
    }
}

/**
 * Function to check if the given URL are image url or not
 *
 */
if (! function_exists('isImage')) {
    function isImage($url){
        $params = array(
            'http' => array('method' => 'HEAD')
        );
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        
        if (!$fp) return false;  // Problem with url

        $meta = stream_get_meta_data($fp);
        if ($meta === false){
            fclose($fp);
            return false;  // Problem reading data from url
        }

        $wrapper_data = $meta["wrapper_data"];
        if(is_array($wrapper_data)){
          foreach(array_keys($wrapper_data) as $hh){
              if (substr($wrapper_data[$hh], 0, 19) == "Content-Type: image") // strlen("Content-Type: image") == 19 
              {
                fclose($fp);
                return true;
              }
          }
        }

        fclose($fp);
        return false;
    }
}

/**
 * Tests if an input is valid PHP serialized string.
 *
 * Checks if a string is serialized using quick string manipulation
 * to throw out obviously incorrect strings. Unserialize is then run
 * on the string to perform the final verification.
**/
if (!function_exists('is_serialized')) {
    function is_serialized( $value, &$result = null ) {
        // Bit of a give away this one
        if ( ! is_string( $value ) ) {
            return FALSE;
        }
        // Serialized FALSE, return TRUE. unserialize() returns FALSE on an
        // invalid string or it could return FALSE if the string is serialized
        // FALSE, eliminate that possibility.
        if ( $value === 'b:0;' ) {
            $result = FALSE;
            return TRUE;
        }
        $length = strlen($value);
        $end    = '';
        
        if ( isset( $value[0] ) ) {
            switch ($value[0]) {
                case 's':
                    if ( $value[$length - 2] !== '"' )
                        return FALSE;
                    
                case 'b':
                case 'i':
                case 'd':
                    // This looks odd but it is quicker than isset()ing
                    $end .= ';';
                case 'a':
                case 'O':
                    $end .= '}';
        
                    if ($value[1] !== ':')
                        return FALSE;
        
                    switch ($value[2]) {
                        case 0:
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                        case 5:
                        case 6:
                        case 7:
                        case 8:
                        case 9:
                        break;
        
                        default:
                            return FALSE;
                    }
                case 'N':
                    $end .= ';';
                
                    if ( $value[$length - 1] !== $end[0] )
                        return FALSE;
                break;
                
                default:
                    return FALSE;
            }
        }
        
        if ( ( $result = @unserialize($value) ) === FALSE ) {
            $result = null;
            return FALSE;
        }
        
        return TRUE;
    }
}


/* End of file controllername.php */
/* Location: ./application/controllers/controllername.php */