<?php
/**
 * Created by PhpStorm.
 * User: agsto
 * Date: 30.08.2018
 * Time: 12:15
 */

namespace app\components;


class UploadImage
{

    private function dirs($patch)
    {

        return true;
    }

    static private function validBase64($string)
    {
        $test = explode("base64", $string);
        if (is_array($test) && count($test) >= 2) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    static public function save_image($base64_image_string, $output_file_without_extension, $patch = "")
    {

        if (!self::validBase64($base64_image_string)) {
            return $base64_image_string;
        }

        if ($base64_image_string == null) {
            return null;
        }
        //usage:  if( substr( $img_src, 0, 5 ) === "data:" ) {  $filename=save_base64_image($base64_image_string, $output_file_without_extentnion, getcwd() . "/application/assets/pins/$user_id/"); }
        //
        //data is like:    data:image/png;base64,asdfasdfasdf
        $splited = explode(',', substr($base64_image_string, 5), 2);
        $mime = $splited[0];
        $data = $splited[1];

        $mime_split_without_base64 = explode(';', $mime, 2);
        $mime_split = explode('/', $mime_split_without_base64[0], 2);
        if (count($mime_split) == 2) {
            $extension = $mime_split[1];
            if ($extension == 'jpeg') $extension = 'jpg';
            //if($extension=='javascript')$extension='js';
            //if($extension=='text')$extension='txt';
            $output_file_with_extension = $output_file_without_extension . '.' . $extension;
        }

        if (!is_dir($patch)) {
            if (!mkdir($patch)) {
                return null;
            }

        }
        if (file_put_contents($patch . $output_file_with_extension, base64_decode($data))) {
            return '/' . $patch . $output_file_with_extension;
        }
    }
}