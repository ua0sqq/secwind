<?php

if ($user_id)
{
    echo '<div class="fmenu">' . 'Библиотека' . ' | Удаление закладок</div>';

    $total = $sql->query("SELECT COUNT(*) FROM `mod_lib_counters` WHERE `uid` = '" . $user_id . "' AND `type` = '2'")->result();

    if ($total)
    {
        if (!empty($_POST))
        {

            $sql->query("DELETE FROM `mod_lib_counters` WHERE `uid` = '" . $user_id . "' AND `type` = '2'");
            $message = 'Закладки удалены';

        }
        else
        {

            $message = '<form action="?act=bookmarks&amp;mod=clean" method="post">Вы действительно хотите удалить все закладки?<br />' .
                       '<input type="submit" name="submit" value="Да" /> &nbsp; &nbsp; ' .
                       '<a href="?act=bookmarks&amp;mod=view" style="text-decoration: none">' .
                       '<input type="button" value="Нет" /></a></form>';
        }

    }
    else
    {

        $message = 'Список пуст';

    }

    echo '<div class="post">' . $message . '</div><div class="fmenu"><a href="?act=bookmarks&amp;mod=view">Назад</a></div>';
}
else
{
    $error = 'Доступ запрещен';
}