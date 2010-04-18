<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Util
 *
 * @author sam
 */
class Util {
    public static function write_output_file($full_file_path, $content) {
        if ( ! $handle = fopen($full_file_path, 'w')) {
             throw new Exception("Unable to get writable handle for output file: {$full_file_path}");
        }
        if (fwrite($handle, $content) === FALSE) {
            fclose($handle);
            throw new Exception("Unable to write to output file: {$full_file_path}");
        }
        fclose($handle);
    }
    public static function file_exists_and_readable($full_file_path) {
        if( ! file_exists($full_file_path)) {
            throw new Exception("file does not exist: {$full_file_path}");
        }
        if( ! is_readable($full_file_path)) {
            throw new Exception("file is not readable: {$full_file_path}");
        }
        return true;
    }
    public static function file_exists_and_writable($full_file_path) {
        if( ! file_exists($full_file_path)) {
            throw new Exception("file does not exist: {$full_file_path}");
        }
        if( ! is_readable($full_file_path)) {
            throw new Exception("file/dir is not Writable: {$full_file_path}");
        }
        return true;
    }
    public static function digest_xml_file($full_path_to_xml) {
        $xml_obj = null;
        if (self::file_exists_and_readable($full_path_to_xml)) {
            libxml_use_internal_errors(true);
            $fail_message = "";
            $xml_obj = simplexml_load_file($full_path_to_xml);
            if (!$xml_obj) {
                $fail_message = "Failed loading XML\n";
                foreach(libxml_get_errors() as $error) {
                    $fail_message .= "\t{$error->message}";
                }
                throw new Exception("Failed to contruct SimpleXML object :{$fail_message}");
            }
        }
        return $xml_obj;
    }
}
?>
