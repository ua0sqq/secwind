<?php

if ($admin)
{

    if ($id === FALSE) $id = 0;

    if (!empty($_POST))
    {

        $error = array();

        if (empty($_POST['name']))
        {

            $error[] = 'Вы не ввели название.';

        }
        else
        {

            $title = my_esc($_POST['name']);

            /* Проверка длины заголовка */
            if (mb_strlen($title) > 255)
            {

                $error[] = 'Превышена допустимая длина заголовка.';

            }
            /* Проверка заголовка на существование */
            elseif ($sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `name` = '" . $title . "'")->result() > 0)
            {

                $error[] = 'Такое название уже есть';

            }

        }

        $desc = !empty($_POST['desc']) ? my_esc($_POST['desc']) : '';

        /* Проверка длины описания */
        if (mb_strlen($desc) > 255)
        {

            $error[] = 'Превышена допустимая длина описания';

        }

        if ( ($id !== 0))
        {

            $parent = $sql->query("SELECT `counter` FROM `mod_lib` WHERE `id` = " . $id . " AND `type` = 'cat'")->fetch();

            if ($parent === FALSE)
            {

                $error[] = 'Неверный тип родительской категории';

            }

        }

        if (!empty($error))
        {

            $error[] = '<a href="?act=category&amp;mod=add">Повторить</a>';

        }
        else
        {

            /* Добавляем категорию */
            $sql->query(
                "INSERT INTO `mod_lib` SET " .
                "`refid` = " . $id . "," .
                "`name` = '" . $title . "'," .
                "`announce` = '" . $desc . "'," .
                "`type` = 'cat', " .
                "`mod` = " . (isset($_POST['users']) ? "1" : "0") . ";"
            );

            $cid = mysqli_insert_id($sql->db);

            /* Обновляем счетчик содержимого в родительском каталоге */
            if (isset($parent))
            {

                $sql->query("UPDATE `mod_lib` SET `counter` = " . ($parent['counter'] + 1) . " WHERE `id` = " . $id);

            }
			
			if (file_exists(tmpDir . 'index_page.swc'))
				unlink(tmpDir . 'index_page.swc');

            echo '<div class="fmenu"><b>' . 'Библиотека' . '&#160;|&#160; Добавить категорию</b></div>' .
                 '<div class="msg">Добавлено &#160;<a href="?act=category&amp;mod=view&amp;id=' . $cid . '">Продолжить
                 </a></div><div class="msg"><a href="index.php">Назад</a></div>';

        }

    }
    else
    {

        echo '<div class="fmenu"><b>' . 'Библиотека' . '&#160;|&#160; Добавить категорию</b></div>' .
             '<div class="post"><form action="?act=category&amp;mod=add&amp;id=' . $id . '" method="post">
             Название &#160;<small>(max 120)</small>:<br /><input type="text" name="name" /><br />
             Описание &#160;<small>(max 300)</small>:<br /><textarea name="desc" rows="10"></textarea><br />' .
             '<input type="checkbox" name="users" />&#160; разрешить пользователям добавлять статьи<br />' .
             '<input type="submit" name="submit" value="Добавить" />' .
             '</form></div><div class="menu"><a href="index.php">Назад</a></div>';

    }

}
else
{

    /* Доступ запрещен */
    $error = 'Доступ запрещен';

}