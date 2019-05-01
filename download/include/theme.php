<?php


$theme = urldecode(htmlspecialchars($_GET['file']));
$format = explode('.', $theme);
$type = '.' . strtolower($format[count($format) - 1]);
$name = 'screen/' . time() . '.gif';
$location = 'http://' . str_replace(array('\\', '//'), array('/', '/'), $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $name);


if (file_exists($name)) {
    header('Location: ' . $location, true, 301);
    exit;
}


$size = explode('*', $setup['prev_size']);
$g_preview_image_w = 100; // ширина картинки
$g_preview_image_h = 100; // высота картинки


if ($type == '.nth') {
    include '../incfiles/pclzip.php';

    $nth = new PclZip($theme);

    $content = $nth->extract(PCLZIP_OPT_BY_NAME, 'theme_descriptor.xml', PCLZIP_OPT_EXTRACT_AS_STRING);
    if (!$content) {
        $content = $nth->extract(PCLZIP_OPT_BY_PREG, '\.xml$', PCLZIP_OPT_EXTRACT_AS_STRING);
    }


    $teg = simplexml_load_string($content[0]['content'])->wallpaper['src'] or $teg = simplexml_load_string($content[0]['content'])->wallpaper['main_display_graphics'];
    $image = $nth->extract(PCLZIP_OPT_BY_NAME, trim($teg), PCLZIP_OPT_EXTRACT_AS_STRING);


    $im = array_reverse(explode('.', $teg));
    $im = 'imageCreateFrom' . str_ireplace('jpg', 'jpeg', trim($im[0]));

    file_put_contents($name, $image[0]['content']);
    $f = $im($name);

    $h = imagesy($f);
    $w = imagesx($f);

    $ratio = $w / $h;
    if ($g_preview_image_w / $g_preview_image_h > $ratio) {
        $g_preview_image_w = $g_preview_image_h * $ratio;
    }
    else {
        $g_preview_image_h = $g_preview_image_w / $ratio;
    }


    $new = imagecreatetruecolor($g_preview_image_w, $g_preview_image_h);
    imagecopyresampled($new, $f, 0, 0, 0, 0, $g_preview_image_w, $g_preview_image_h, $w, $h);

    imageGif($new, $name);
} elseif ($type == '.thm') {
    include 'include/class_tar.php';

    $thm = new Archive_Tar($theme);
    if (!$file = $thm->extractInString('Theme.xml') or !$file = $thm->extractInString(pathinfo($theme, PATHINFO_FILENAME) . '.xml')) {

        $list = $thm->listContent();

        $all = sizeof($list);
        for ($i = 0; $i < $all; ++$i) {
            if (pathinfo($list[$i]['filename'], PATHINFO_EXTENSION) == 'xml') {
                $file = $thm->extractInString($list[$i]['filename']);
                break;
            }
        }

    }

    // fix bug in tar.php
    if (!$file) {
        preg_match('/<\?\s*xml\s*version\s*=\s*"1\.0"\s*\?>(.*)<\/.+>/isU', file_get_contents($theme), $arr);
        $file = trim($arr[0]);
    }


    $load = trim((string )simplexml_load_string($file)->Standby_image['Source']);

    if (strtolower(strrchr($load, '.')) == '.swf') {
        $load = '';
    }

    if (!$load) {
        $load = trim((string )simplexml_load_string($file)->Desktop_image['Source']);
    }

    if (strtolower(strrchr($load, '.')) == '.swf') {
        $load = '';
    }


    if (!$load) {
        $load = trim((string )simplexml_load_string($file)->Desktop_image['Source']);
    }

    if (strtolower(strrchr($load, '.')) == '.swf') {
        $load = '';
    }

    if (!$load) {
        exit;
    }


    $image = $thm->extractInString($load);


    $im = array_reverse(explode('.', $load));
    $im = 'imageCreateFrom' . str_ireplace('jpg', 'jpeg', trim($im[0]));

    file_put_contents($name, $image);
    $f = $im($name);

    $h = imagesy($f);
    $w = imagesx($f);

    $ratio = $w / $h;
    if ($g_preview_image_w / $g_preview_image_h > $ratio) {
        $g_preview_image_w = $g_preview_image_h * $ratio;
    }
    else {
        $g_preview_image_h = $g_preview_image_w / $ratio;
    }


    $new = imagecreatetruecolor($g_preview_image_w, $g_preview_image_h);
    imagecopyresampled($new, $f, 0, 0, 0, 0, $g_preview_image_w, $g_preview_image_h, $w, $h);

    imageGif($new, $name);
}

?>