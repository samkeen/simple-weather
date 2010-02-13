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
}
?>
