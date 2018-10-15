<?php
/**
 * Created by PhpStorm.
 * User: Stomick
 * Date: 20.09.2018
 * Time: 6:14
 */

namespace app\components\makeGraf;

class CreateGraff
{
    public function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            if (file_exists($dirPath) !== false) {
                unlink($dirPath);
            }
            return;
        }

        if ($dirPath[strlen($dirPath) - 1] != '/') {
            $dirPath .= '/';
        }

        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dirPath);
    }
    public function grafGames($id, $logo1, $logo2, $name1, $name2, $league, $date, $tour, $stadium)
    {
        $logo1 = $_SERVER['DOCUMENT_ROOT'] . $logo1;
        $logo2 = $_SERVER['DOCUMENT_ROOT'] . $logo2;
// Создание изображения
        $im = imagecreatefrompng(__DIR__ . '/img/oblozhka.png');

// Создание цветов
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);
        //imagefilledrectangle($im, 0, 0, 399, 29, $white);
        imagesavealpha($im, true);
// Текст надписи

// Замена пути к шрифту на пользовательский
        $font = __DIR__ . '/fonts/monster/montserrat.ttf';

        $png1 = $this->resize($logo1, 330, 330);
        $png2 = $this->resize($logo2, 330, 330);


        imagealphablending($png2, true);
        imagesavealpha($png2, true);
        imagealphablending($png1, true);
        imagesavealpha($png1, true);

        try {
            imagettftext($im, 32, 0, 50, 80, $white, $font, $league);
            imagettftext($im, 32, 0, 1250, 80, $white, $font, $date);

            imagettftext($im, 55, 0, 730, 180, $white, $font, $tour . ' Tour');

            imagettftext($im, 32, 0, 350 - (strlen($name1) / 2 * 25), 640, $black, $font, $name1);
            imagettftext($im, 32, 0, 1250 - (strlen($name2) / 2 * 25), 640, $black, $font, $name2);
            imagettftext($im, 32, 0, 800 - (strlen($stadium) / 2 * 25), 800, $white, $font, $stadium);

            imagecopy($im, $png1, 200, 260, 0, 0, imagesx($png1), imagesy($png1));
            imagecopy($im, $png2, 1110, 260, 0, 0, imagesx($png2), imagesy($png2));

            if (!is_dir('./img/graf/' . $id)) {
                mkdir('./img/graf/' . $id);
            }
            $file = "/img/graf/" . $id . "/cover.png";
            imagepng($im, '.' . $file);
            imagedestroy($im);
            return $file;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


    public function grafGamesScreen($id, $logo1, $logo2, $name1, $name2, $league, $date, $tour, $stadium)
    {
        $logo1 = $_SERVER['DOCUMENT_ROOT'] . $logo1;
        $logo2 = $_SERVER['DOCUMENT_ROOT'] . $logo2;
// Создание изображения
        $im = imagecreatefrompng(__DIR__ . '/img/zastavka.png');

// Создание цветов
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);
        //imagefilledrectangle($im, 0, 0, 399, 29, $white);
        imagesavealpha($im, true);
// Текст надписи

// Замена пути к шрифту на пользовательский
        $font = __DIR__ . '/fonts/monster/montserrat.ttf';

        $png1 = $this->resize($logo1, 330, 330);
        $png2 = $this->resize($logo2, 330, 330);


        imagealphablending($png2, true);
        imagesavealpha($png2, true);
        imagealphablending($png1, true);
        imagesavealpha($png1, true);

        try {
            imagettftext($im, 32, 0, 50, 80, $white, $font, $league);
            imagettftext($im, 32, 0, 1250, 80, $white, $font, $date);

            imagettftext($im, 55, 0, 730, 180, $white, $font, $tour . ' Tour');

            imagettftext($im, 32, 0, 350 - (strlen($name1) / 2 * 25), 640, $black, $font, $name1);
            imagettftext($im, 32, 0, 1250 - (strlen($name2) / 2 * 25), 640, $black, $font, $name2);
            imagettftext($im, 32, 0, 800 - (strlen($stadium) / 2 * 25), 800, $white, $font, $stadium);

            imagecopy($im, $png1, 200, 260, 0, 0, imagesx($png1), imagesy($png1));
            imagecopy($im, $png2, 1110, 260, 0, 0, imagesx($png2), imagesy($png2));

            if (!is_dir('./img/graf/' . $id)) {
                mkdir('./img/graf/' . $id);
            }
            $file = "/img/graf/" . $id . "/screen.png";
            imagepng($im, '.' . $file);
            imagedestroy($im);
            return $file;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function grafGamesAnons($id, $logo1, $logo2, $name1, $name2, $league, $date, $tour)
    {
        $logo1 = $_SERVER['DOCUMENT_ROOT'] . $logo1;
        $logo2 = $_SERVER['DOCUMENT_ROOT'] . $logo2;
// Создание изображения
        $im = imagecreatefrompng(__DIR__ . '/img/anons.png');

// Создание цветов
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);
        //imagefilledrectangle($im, 0, 0, 399, 29, $white);
        imagesavealpha($im, true);
// Текст надписи

// Замена пути к шрифту на пользовательский
        $font = __DIR__ . '/fonts/monster/montserrat.ttf';

        $png1 = $this->resize($logo1, 330, 330);
        $png2 = $this->resize($logo2, 330, 330);


        imagealphablending($png2, true);
        imagesavealpha($png2, true);
        imagealphablending($png1, true);
        imagesavealpha($png1, true);

        try {
            imagettftext($im, 32, 0, 50, 80, $white, $font, $league);
            imagettftext($im, 32, 0, 1250, 80, $white, $font, $date);

            imagettftext($im, 55, 0, 730, 180, $white, $font, $tour . ' Tour');

            imagettftext($im, 32, 0, 350 - (strlen($name1) / 2 * 25), 640, $black, $font, $name1);
            imagettftext($im, 32, 0, 1250 - (strlen($name2) / 2 * 25), 640, $black, $font, $name2);
            //imagettftext($im, 32, 0, 800 - (strlen($stadium) /2 *25), 800 , $white, $font, $stadium);

            imagecopy($im, $png1, 200, 260, 0, 0, imagesx($png1), imagesy($png1));
            imagecopy($im, $png2, 1110, 260, 0, 0, imagesx($png2), imagesy($png2));

            if (!is_dir('./img/graf/' . $id)) {
                mkdir('./img/graf/' . $id);
            }
            $file = "/img/graf/" . $id . "/anons.png";
            imagepng($im, '.' . $file);
            imagedestroy($im);
            return $file;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function grafGamesGoal($id, $logo, $number1, $number2, $name1, $name2, $minute)
    {
        $logo = $_SERVER['DOCUMENT_ROOT'] . $logo;

// Создание изображения
        $im = imagecreatefrompng(__DIR__ . '/img/goal.png');

// Создание цветов
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);
        //imagefilledrectangle($im, 0, 0, 399, 29, $white);
        imagesavealpha($im, true);
// Текст надписи

// Замена пути к шрифту на пользовательский
        $font = __DIR__ . '/fonts/monster/montserrat.ttf';

        $png1 = $this->resize($logo, 90, 90);

        imagealphablending($png1, true);
        imagesavealpha($png1, true);

        try {
            imagettftext($im, 20, 0, 1045, 790, $black, $font, $name1);
            imagettftext($im, 18, 0, 1000, 830, $black, $font, $name2);
            imagettftext($im, 20, 0, 1420, 790, $black, $font, $minute . '\'');
            imagettftext($im, 20, 0, 975, 790, $black, $font, $number1);
            imagettftext($im, 18, 0, 975, 830, $black, $font, $number2);
            //imagettftext($im, 32, 0, 800 - (strlen($stadium) /2 *25), 800 , $white, $font, $stadium);

            imagecopy($im, $png1, 870, 750, 0, 0, imagesx($png1), imagesy($png1));

            if (!is_dir('./img/graf/' . $id)) {
                mkdir('./img/graf/' . $id);
            }
            $file = "/img/graf/" . $id . "/" . $minute . "_goal.png";
            imagepng($im, '.' . $file);
            imagedestroy($im);
            return $file;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function grafGamesScore($id, $number1, $number2, $name1, $name2, $minute, $color1, $color2)
    {


        $im = imagecreatefrompng(__DIR__ . '/img/score.png');

        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagesavealpha($im, true);
        $col1 = $this->html2rgb($color1);
        $col2 = $this->html2rgb($color2);
        $font = __DIR__ . '/fonts/monster/montserrat.ttf';

        $centrX = 205 - strlen($number1 . ':' . $number2) / 2 * 25;
        $number1 >= 3 ? $centrX +=7 : '';
        try {
            imagettftext($im, 25, 0, 45, 95, $black, $font, $name1);
            imagettftext($im, 25, 0, 280, 95, $black, $font, $name2);
            imagettftext($im, 35, 0, $number1 == 1 ? $centrX + 14 : ($number1 == 2 ? $centrX + 5: $centrX), 100, $black, $font, $number1 . ':' . $number2);
            imagefilledrectangle($im, 31, 105, 143, 110, imagecolorallocate($im, $col1[0], $col1[1], $col1[2]));
            imagefilledrectangle($im, 261, 105, 373, 110, imagecolorallocate($im, $col2[0], $col2[1], $col2[2]));

            if (!is_dir('./img/graf/' . $id)) {
                mkdir('./img/graf/' . $id);
            }
            $file = "/img/graf/" . $id . "/" . $minute . "_" . $number1 . "-" . $number2 . "_score.png";
            imagepng($im, '.' . $file);
            imagedestroy($im);
            return $file;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function grafGamesResult($id, $logo1, $logo2, $name1, $name2, $goalsIn, $goalsOut, $tour, $stitle)
    {
        $logo1 = $_SERVER['DOCUMENT_ROOT'] . $logo1;
        $logo2 = $_SERVER['DOCUMENT_ROOT'] . $logo2;
// Создание изображения
        $im = imagecreatefrompng(__DIR__ . '/img/result.png');

// Создание цветов
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 0, 0, 0);
        //imagefilledrectangle($im, 0, 0, 399, 29, $white);
        imagesavealpha($im, true);
// Текст надписи

// Замена пути к шрифту на пользовательский
        $font = __DIR__ . '/fonts/monster/montserrat.ttf';

        $png1 = $this->resize($logo1, 330, 330);
        $png2 = $this->resize($logo2, 330, 330);


        imagealphablending($png2, true);
        imagesavealpha($png2, true);
        imagealphablending($png1, true);
        imagesavealpha($png1, true);

        try {
            if(count($goalsIn)) {
                foreach ($goalsIn as $k => $goal) {
                    imagettftext($im, 32, 0, 50, 80, $white, $font, $goal);
                }
            }
            if(count($goalsOut)) {
                foreach ($goalsOut as $k => $goal) {
                    imagettftext($im, 32, 0, 50, 80, $white, $font, $goal);
                }
            }
            imagettftext($im, 55, 0, 730, 180, $white, $font, $tour . ' Tour');

            imagettftext($im, 32, 0, 350 - (strlen($name1) / 2 * 25), 640, $black, $font, $name1);
            imagettftext($im, 32, 0, 1250 - (strlen($name2) / 2 * 25), 640, $black, $font, $name2);
            //imagettftext($im, 32, 0, 800 - (strlen($stadium) /2 *25), 800 , $white, $font, $stadium);

            imagecopy($im, $png1, 200, 260, 0, 0, imagesx($png1), imagesy($png1));
            imagecopy($im, $png2, 1110, 260, 0, 0, imagesx($png2), imagesy($png2));

            if (!is_dir('./img/graf/' . $id)) {
                mkdir('./img/graf/' . $id);
            }
            $file = "/img/graf/" . $id . "/result.png";
            imagepng($im, '.' . $file);
            imagedestroy($im);
            return $file;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


    function html2rgb($color)
    {
        if ($color[0] == '#')
            $color = substr($color, 1);

        if (strlen($color) == 6)
            list($r, $g, $b) = array($color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]);
        elseif (strlen($color) == 3)
            list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        else
            return false;

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return array($r, $g, $b);
    }

    public function grafGamesCart($id, $logo, $number1, $name1, $minute, $type)
    {
        $logo = $_SERVER['DOCUMENT_ROOT'] . $logo;

// Создание изображения
        $im = imagecreatefrompng(__DIR__ . '/img/goal.png');

// Создание цветов
        $yelow = imagecolorallocate($im, 255, 255, 0);
        $red = imagecolorallocate($im, 255, 0, 0);
        $black = imagecolorallocate($im, 0, 0, 0);
        //imagefilledrectangle($im, 0, 0, 399, 29, $white);
        imagesavealpha($im, true);
// Текст надписи

// Замена пути к шрифту на пользовательский
        $font = __DIR__ . '/fonts/monster/montserrat.ttf';

        $png1 = $this->resize($logo, 90, 90);

        imagealphablending($png1, true);
        imagesavealpha($png1, true);

        try {
            imagettftext($im, 20, 0, 1045, 790, $black, $font, $name1);
            imagettftext($im, 18, 0, 1420, 830, $black, $font, $minute . '\'');
            imagettftext($im, 20, 0, 975, 790, $black, $font, $number1);
            imagefilledrectangle($im, 1430, 762, 1450, 790, $type == 'yelow' ? $yelow : $red);

            //imagettftext($im, 32, 0, 800 - (strlen($stadium) /2 *25), 800 , $white, $font, $stadium);

            imagecopy($im, $png1, 870, 750, 0, 0, imagesx($png1), imagesy($png1));

            if (!is_dir('./img/graf/' . $id)) {
                mkdir('./img/graf/' . $id);
            }
            $file = "/img/graf/" . $id . "/" . $minute . "_" . $type . "_cart.png";
            imagepng($im, '.' . $file);
            imagedestroy($im);
            return $file;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    function resize($file, $width, $height)
    {

        list($w, $h) = getimagesize($file);

        $ratio = max($width / $w, $height / $h);
        $h = ceil($height / $ratio);
        $x = ($w - $width / $ratio) / 2;
        $w = ceil($width / $ratio);

        $image = imagecreatefrompng($file);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $tmp = imagecreatetruecolor($width, $height);
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        imagecopyresampled($tmp, $image, 0, 0, $x, 0, $width, $height, $w, $h);
        return $tmp;
    }
}