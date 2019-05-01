<?php

//ID аккаунта на banbase.ru
define('BANBASE_ID', 1);
//ID сайта, добавленного в banbase.ru
define('BANBASE_SITE', 3);
//API KEY от banbase.ru
define('BANBASE_API_KEY', '1D87E04062AFEDB066F4EF924DE949B4');
//Сервер базы
define('BANBASE_SERVER', 'http://banbase.ru/api');

class Banbase {

    //Поиск по строке
    static function search($str, $page = 1) {
        $str = urlencode($str);
        $page = (int) $page;
        $content = self::http(BANBASE_SERVER . '/' . $str . '?serialize&p=' . $page);
        $arr = unserialize($content);
        return $arr;
    }

    //Поиск по параметрам
    static function search_arr($search_arr) {
        $str = '';
        foreach ($search_arr AS $key => $val) {
            $str.= $key . '=' . urlencode($val) . '&';
        }
        $res = unserialize(@file_get_contents(BANBASE_SERVER . '/search/?serialize&' . $str));
		return $res['answer'];
    }

    //Добавление записи в базу
    //Необходим curl, и аккаунт на сервере базы
    //info_arr
    //wnid, icq, email, skype, url, descr, name
    static function add($info_arr) {
        $info_arr['id'] = BANBASE_ID;
        $info_arr['api_key'] = BANBASE_API_KEY;
        $info_arr['site'] = BANBASE_SITE;
        $content = self::http(BANBASE_SERVER . '/add/?serialize', Array(), $info_arr);
        $arr = unserialize($content);
        return $arr;
    }

    //Запрос по http
    static function http($url, $headers = Array(), $post_array = Array()) {
        //if (!function_exists('curl_init')) {
            return file_get_contents($url);
        //}

        $head = Array();
        if (is_array($headers)) {
            foreach ($headers AS $key => $val) {
                $head[] = $key . ': ' . $val;
            }
        }

        $post = '';
        if ($post_array) {
            $c = count($post_array);
            $i = 0;
            foreach ($post_array AS $key => $val) {
                $i++;
                $post.= $key . '=' . $val;
                if ($i <> $c) {
                    $post .= '&';
                }
            }
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'curl_pb_callback');      //Функция для ограничения трафика по размеру
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');

        if ($head){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $head);  //Заголовки
        }



            
//curl_setopt($ch, CURLOPT_HEADER,         true);         // для включения заголовков в вывод.
        //curl_setopt($ch, CURLOPT_NOBODY,         true);         // для исключения тела ответа из вывода. Метод запроса устанавливается в HEAD. 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);           //Количество секунд ожидания при попытке соединения    
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);           //Максимально позволенное количество секунд для выполнения cURL-функций.
        //curl_setopt($ch, CURLINFO_HEADER_OUT,        true);

        $result = curl_exec($ch);

        //print_r(curl_getinfo ($ch ,CURLINFO_HEADER_OUT));
        //Возвращаем false, устанавливаем сообщение об ошибке----
        $err = curl_error($ch);
        if ($err) {
            $result = Array();
             $result['error'] = $err;
            return serialize($result);
        }
        //-------------------------------------------------------

        curl_close($ch);

        return $result;
    }

}

//call back функция для curl, ограничивает размер скачиваемого файла
function curl_pb_callback($download_size, $downloaded, $upload_size, $uploaded) {
    global $ch;
    static $summ;
    if (!isset($summ)) {
        $summ = 0;
    }
    $summ+=$downloaded;
    if ($summ > 500 * 1024) {
        throw new Exception('Too long content');
    }
    return false;
}
