<?php
    //include '../engine/includes/start.php';

//    if (!$creator)
  //      Core::stop();

    if (file_exists($_SERVER['DOCUMENT_ROOT'].'/style/themes/last_ver.png') && filemtime($_SERVER['DOCUMENT_ROOT'].'/style/themes/last_ver.png') > (time() - 100000))
    {
        $im = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].'/style/themes/last_ver.png');
    }
    else
    {
        $last_version = @json_decode(file_get_contents('http://secwind.ru/?act=get_last_version'), true);    
        
        if (empty($last_version) || (function_exists('json_last_error') && json_last_error()))
        {
            $last_version['last_version'] = '0.0';
            $last_version['status'] = 'n/a';
        }
    
        $im = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].'/admin/icons/screen.png');
    
        imagestring($im, 3 , 6 , 10 , $last_version['last_version'] , imagecolorallocate($im, 0, 0, 0));
        imagestring($im, 2 , 6 , 20 , $last_version['status'] , imagecolorallocate($im, 0, 0, 0));
        imagepng($im, $_SERVER['DOCUMENT_ROOT'].'/style/themes/last_ver.png');
    }
    
    header('Content-type: image/png');

    imagepng($im);
    imagedestroy($im);