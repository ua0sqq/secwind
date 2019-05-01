<?php

if ($user_id)
{
    if ($id)
    {

        echo '<div class="fmenu">' . 'Библиотека' . ' | Удалить из закладок</div>';

        $arc =$sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `id` = '" . $id . "' AND `type` = 'arc' AND `mod` = '0'")->result();
        if ($arc)
        {

            $bookmark = $sql->query(
                "SELECT COUNT(*) FROM `mod_lib_counters` WHERE `aid` = '" . $id . "'" .
                " AND `uid` = '" . $user_id . "' AND `type` = '2'"
            )->result();

            if ($bookmark)
            {

                $sql->query("DELETE FROM `mod_lib_counters` WHERE `aid` = '" . $id . "' AND `uid` = '" . $user_id . "' AND `type` = '2'");
                $message = 'Закладка удалена';

            }
            else
            {

                $message = 'Закладка не найдена';

            }

        }
        else
        {

            $message = 'Статья не найдена';

        }

        echo '<div class="menu">' . $message .
             '</div><div class="fmenu"><a href="' .
             (isset($_GET['bookmarks'])
             ? '?act=bookmarks&amp;mod=view'
             : '?act=articles&amp;mod=view&amp;id=' . $id ) .
             '">Назад</a></div>';

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