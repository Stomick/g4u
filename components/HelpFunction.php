<?php
/**
 * Created by PhpStorm.
 * User: agsto
 * Date: 21.09.2018
 * Time: 13:39
 */

namespace app\components;

use keltstr\simplehtmldom\SimpleHTMLDom as SHD;

class HelpFunction
{
    private $useragent = 'Loximi sfFacebookPhoto PHP5 (cURL)';
    private $curl = null;
    private $response_meta_info = array();

    public function __construct() {
        $this->curl = curl_init();
        register_shutdown_function(array($this, 'shutdown'));
    }

    static public function getDuration($date, $currentDate){
        $date_from = explode('-', $date);
        $date_till = explode('-', $currentDate);

        $time_from = mktime(0, 0, 0, $date_from[1], $date_from[2], $date_from[0]);
        $time_till = mktime(0, 0, 0, $date_till[1], $date_till[2], $date_till[0]);

        $diff = abs( intval(($time_till - $time_from)/60/60/24/356));
        //$diff = date('d', $diff); - как делал))
        return $date_from[2] > $date_till[2] ? $diff - 1 : $diff;
    }
    static public function transliterate($string)
    {
        $roman = array("Sch", "sch", 'Yo', 'Zh', 'Kh', 'Ts', 'Ch', 'Sh', 'Yu', 'ya', 'yo', 'zh', 'kh', 'ts', 'ch', 'sh', 'yu', 'ya', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', '', 'Y', '', 'E', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', '', 'y', '', 'e', '_');
        $cyrillic = array("Щ", "щ", 'Ё', 'Ж', 'Х', 'Ц', 'Ч', 'Ш', 'Ю', 'я', 'ё', 'ж', 'х', 'ц', 'ч', 'ш', 'ю', 'я', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Ь', 'Ы', 'Ъ', 'Э', 'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'ь', 'ы', 'ъ', 'э', ' ');
        return str_replace($cyrillic, $roman, $string);
    }

    static public function create_zip($dir, $destination = '', $overwrite = true) {

        $valid_files=[];
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." ) {
                    $valid_files[] = $entry;
                }
            }
            closedir($handle);
        }
        //var_dump($valid_files);
        /*
        if(file_exists($destination) && !$overwrite) { return false; };
        $valid_files = array();
        if(is_array($files)) {
            foreach($files as $file) {
                if(file_exists($_SERVER['DOCUMENT_ROOT'].$file)) {
                    $valid_files[] = $file;
                };
            };
        };
*/

        if(count($valid_files)) {
            $zip = new \ZipArchive();
            if(file_exists($destination)){
                $flag = \ZIPARCHIVE::OVERWRITE;
            }else{
                $flag = \ZIPARCHIVE::CREATE;
            }
            if($zip->open($destination,$flag ) !== TRUE) {
                var_dump($valid_files);
                return false;
            };
            foreach($valid_files as $file) {
                $zip->addFile($dir.'/'.$file , $file);
                //var_dump($dir.'/'.$file);
            };
            $zip->close();
            return $destination;
        } else {
            return false;
        }

    }
    static public function getFacebookImage($url){
        $imgSrc = [];
        $options = [
            'http' => [
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                    "Content-Type: application/json\r\n" .
                    "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
                    "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            ]
        ];

        $context = stream_context_create($options);
        $html_source = SHD::file_get_html($url, false, $context);
        $element = $html_source->find('a');

        $ind = 0;
        foreach ($element as $k => $el) {
            if(isset($el->attr['data-sigil']) && $el->attr['data-sigil'] == 'photoset_thumbnail') {
                foreach (SHD::file_get_html("https://www.facebook.com/" . $el->href, false, $context)->find('meta') as $v => $t) {
                    if(isset($t->attr['property']) && $t->attr['property'] == 'og:image') {
                        if($t->attr['content']){
                        $imgSrc[$ind++] = $t->attr['content'];
                        }
                    }
                }
            }

        }
        return $imgSrc;
    }

}