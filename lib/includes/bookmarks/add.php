<?php

if ($user_id)
{

    if ($id)
    {

        echo '<div class="fmenu">' . 'Библиотека' . ' | Добавить в закладки</div>';

        $arc = $sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `id` = '" . $id . "' AND `type` = 'arc' AND `mod` = '0'")->result();
        if ($arc)
        {
            $bookmark = intval($sql->query(
                "SELECT COUNT(*) FROM `mod_lib_counters` WHERE `aid` = '" . $id . "'" .
                " AND `uid` = '" . $user_id . "' AND `type` = '2'"
            )->result());

            if ($bookmark === 0)
            {
                $sql->query("INSERT INTO `mod_lib_counters` SET `aid` = '" . $id . "', `uid` = '" . $user_id . "', `type` = '2'");
                $message = 'Закладка добавлена';
            }
            else
            {
                $message = 'Закладка уже есть';
            }

        }
        else
        {

            $message = 'Статья не найдена';

        }

        echo '<div class="msg">' . $message .
             '</div><div class="fmenu"><a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

    }
    else
    {

        $error = 'Ошибка принятых данных';

    }

}
else
{

    $error = 'Доступ запрещен';

}