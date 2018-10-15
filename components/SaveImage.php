<?php
/**
 * Created by PhpStorm.
 * User: agsto
 * Date: 06.08.2018
 * Time: 16:28
 */

namespace app\components;


class SaveImage
{
    private static function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

            return $dat;
        } else {
            return $dat;
        }
    }

    static public function getScreenShot($url, $nane, $screen, $size, $format = "jpg"){
        $result = "http://mini.s-shot.ru/".$screen."/".$size."/".$format."/?".$url; // делаем запрос к сайту, который делает скрины
        $pic = file_get_contents($result); // получаем данные. Ответ от сайта
        $file = "/img/" . $nane . "." . $format;
        file_put_contents("." . $file, $pic); // сохраняем полученную картинку
        return $file;
    }
}