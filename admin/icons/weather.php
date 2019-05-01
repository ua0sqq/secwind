<?php
    include '../../engine/includes/start.php';

    if (!$creator)
        Core::stop();
        
    if (file_exists($_SERVER['DOCUMENT_ROOT'].'/style/themes/weather_'.$_GET['city'].'.png') && (filemtime($_SERVER['DOCUMENT_ROOT'].'/style/themes/weather_'.$_GET['city'].'.png') > (time() - 30000)))
    {
        $im = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].'/style/themes/weather_'.$_GET['city'].'.png');
    }
    else
    {
        $weather = json_decode(file_get_contents('http://api.openweathermap.org/data/2.5/weather?q='.$_GET['city']), true);
        //unset($weather['coord'], $weather['sys']);
        if ($weather['cod'] == 404)
        {
            $im = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].'/admin/icons/screen.png');
            imagestring($im, 3 , 6 , 10 , 'unknown' , imagecolorallocate($im, 0, 0, 0));
        }
        else
        {
        /*echo '<pre>';
        foreach ($weather as $item=>$val)
        {
            if (is_array($val))
            {echo PHP_EOL .$item . ' ['.PHP_EOL ;
                foreach ($val as $item2=>$val2)
                {
                    if (is_array($val2))
                    {echo PHP_EOL .$item2 .'['.PHP_EOL ;
                        foreach ($val2 as $item3=>$val3)echo ' ---'.$item3 . ' > ' . $val3. PHP_EOL;
                        echo ']'.PHP_EOL . PHP_EOL;
                    }
                    else
                    {
                        echo ' --'.$item2 . ' > ' . $val2. PHP_EOL;
                    }
                }
               echo ']'.PHP_EOL .PHP_EOL ;
            }
            else
            {
                echo ' -'.$item . ' > ' . $val . PHP_EOL;
            }
        }echo '</pre>';
        */
            file_put_contents(H.'style/themes/weather_'.$_GET['city'].'.png', file_get_contents('http://openweathermap.org/img/w/'.$weather['weather'][0]['icon'].'.png'));
    
            $im = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].'/style/themes/weather_'.$_GET['city'].'.png');
    
            imagestring($im, 3 , 6 , 35 , $weather['name'] , imagecolorallocate($im, 0, 0, 0));
            imagestring($im, 3 , 6 , 5 , ceil($weather['main']['temp'] - 273).' C' , imagecolorallocate($im, 0, 0, 0));
            imagepng($im, $_SERVER['DOCUMENT_ROOT'].'/style/themes/weather_'.$_GET['city'].'.png');
    
        }
    }
    header('Content-type: image/png');

    imagepng($im);
    imagedestroy($im);
    
    