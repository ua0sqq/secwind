<?php
    include 'engine/includes/start.php';
    include incDir.'head.php';

    if (isset($_GET['err']))
    {
        $err = intval($_GET['err']);
        switch($err)
        {
            case 400:
                $desc = 'Обнаруженная ошибка в запросе';
                    break;

            case 401:
                $desc = 'Нет прав для выдачи документа';
                    break;

            case 402:
                $desc = 'Не реализованный код запроса';
                    break;

            case 403:
                $desc = 'Доступ запрещен';
                    break;

            case 404:
                $desc = 'Нет такой страницы';
                    break;

            case 500:
                $desc = 'Внутренняя ошибка сервера';
                    break;

            case 502:
                $desc = 'Сервер получил недопустимые ответы другого сервера';
                    break;

            default:
                $desc = 'Неизвестная ошибка';
                    break;
        }
        error_catch(null, null, null, null, $desc, 'server');
        echo $desc;
        include incDir.'foot.php';
    }
    else
        Core::stop();