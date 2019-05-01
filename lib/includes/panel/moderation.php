<?php


if ($admin)
{

    echo '<div class="fmenu"><a href="index.php">' . 'Библиотека' . '</a> | <a href="?act=panel&amp;mod=view">Панель управления</a></div>';

    if (!empty($do))
    {

        if ($do == 'accept_all')
        {

            /* Подтверждаем все статьи */
            $sql->query("UPDATE `mod_lib` SET `mod` = '0' WHERE `mod` = '1' AND `type` = 'arc'");
            echo '<div class="menu">Все статьи приняты. <a href="?act=panel&amp;mod=view">Продолжить</a></div>';

        }
        else
        {

            /* Подтверждаем одну статью */
            $do = abs(intval($do));
            $arc = $sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `id` = '" . $do . "' AND `type` = 'arc' AND `mod` = '1'")->result();
            if ($arc > 0)
            {

                $sql->query("UPDATE `mod_lib` SET `mod` = '0' WHERE `id` = '" . $do . "'");

                echo '<div class="msg">Статья принята.&#160;<a href="?act=panel&amp;mod=moderation">Продолжить</a></div>';

            }
            else
            {

                echo '<div class="err">' . 'Статья не найдена' . '.&#160;<a href="?act=panel&amp;mod=moderation">Продолжить</a></div>';

            }

            echo '<div class="fmenu"><a href="?act=panel&amp;mod=view">Назад</a></div>';

        }

    }
    else
    {
        Core::get('page.class', 'classes');
        $total = $sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `type` = 'arc' AND `mod` = '1'")->result();
        $page = new page($total, $set['p_str']);
        if ($total)
        {

            echo '<form action="?act=panel&amp;mod=movdel&amp;moderation" method="post">';

            $query = $sql->query(
                "SELECT `id`, `name`, `announce`, `author_id`, `author_name`, `time` FROM `mod_lib` " .
                "WHERE `type` = 'arc' AND `mod` = 1 ORDER BY `time` DESC LIMIT " . $page->limit());
            $i = 0;
            while ($arc = $sql->fetch())
            {

                echo '<div class="' . ($i % 2 ? 'p_m' : 'p_t') . '">' .
                    '<input type="checkbox" name="data[' . $i . ']" value="' . $arc['id'] . '" />' .
                    '<a href="?act=articles&amp;mod=view&amp;id=' . $arc['id'] . '">' .
                     htmlentities($arc['name'], ENT_QUOTES, 'UTF-8') . '</a><br />' .
                     htmlentities($arc['announce'], ENT_QUOTES, 'UTF-8') .
                     '<div class="status">Автор: <a href="/pages/user.php?id=' . $arc['author_id'] . '">' .
                     htmlentities($arc['author_name'], ENT_QUOTES, 'UTF-8') . '</a><br />' .
                     'Добавлено: ' . Core::time($arc['time']) .
                     '<br /><span class="status"><a href="?act=panel&amp;mod=moderation&amp;do=' . $arc['id'] . '">Принять</a> | ' .
                     '<a href="?act=articles&amp;mod=remove&amp;id=' . $arc['id'] . '&amp;moderation">Удалить</a></span></div></div>';

                $i++;

            }

            echo '<div class="rmenu"><input type="submit" name="delete" value="Удалить отмеченные" />' .
                 '<a href="?act=panel&amp;mod=moderation&amp;do=accept_all" style="text-decoration: none"><input type="button" name="accept_all" value="Принять все" /></a></div></form>';

        }
        else
        {

            echo '<div class="msg">Список пуст</div>';

        }

        echo '<div class="fmenu"><a href="?act=panel&amp;mod=view">Назад</a></div>';

        $page->display('?act=panel&amp;mod=moderation&amp;');
    }

}
else
{

    $error = 'Доступ запрещен';

}