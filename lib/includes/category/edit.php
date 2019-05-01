<?php

if ($admin)
{
    if ($id)
    {
        $cat = $sql->query("SELECT `name`, `announce`, `mod` FROM `mod_lib` WHERE `id` = " . $id . " AND `type` = 'cat'")->fetch();

        if ($cat === FALSE)
        {
            $error = 'Категория не найдена';
        }
        else
        {
            if (!empty($_POST))
            {
                $error = array();
                if (empty($_POST['name']))
                {

                    $error[] = 'Вы не ввели название';

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
                    elseif (
                        ($title != my_esc($cat['name']))
                        && ($sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `name` = '" . $title . "'")->result() > 0)
                    )
                    {
                        $error[] = 'Такое название уже есть.';
                    }
                }

                $desc = !empty($_POST['desc']) ? my_esc($_POST['desc']) : '';

                /* Проверка длины описания */
                if (mb_strlen($desc) > 255)
                {

                    $error[] = 'Превышена допустимая длина описания.';

                }

                if (!empty($error))
                {

                    $error[] = '<a href="?act=category&amp;mod=add">Повторить</a>';

                }
                else
                {

                    /* Добавляем категорию */
                    $sql->query(
                        "UPDATE `mod_lib` SET " .
                        "`name` = '" . $title . "', " .
                        "`announce` = '" . $desc . "', " .
                        "`mod` = " . (isset($_POST['users']) ? "1" : "0") .
                        " WHERE `id` = " . $id . ";");

                    echo '<div class="fmenu"><b>' . 'Библиотека' . '&#160;|&#160; Добавить категорию</b></div>' .
                         '<div class="msg">Сохранено.&#160;<a href="?act=category&amp;mod=view&amp;id=' . $id . '">' .
                         'Продолжить</a></div><div class="fmenu"><a href="index.php">Назад</a></div>';

                }

            }
            else
            {

                echo '<div class="fmenu"><b>' . 'Библиотека' . ' | Редактировать категорию</b></div>' .
                     '<div class="menu"><form action="?act=category&amp;mod=edit&amp;id=' . $id . '" method="post">' .
                     'Название &#160;<small>(max 120)</small>:<br />' .
                     '<input type="text" name="name" value="' . htmlentities($cat['name'], ENT_QUOTES, 'UTF-8') . '" /><br />' .
                     'Описание &#160;<small>(max 300)</small>:<br />' .
                     '<textarea name="desc" rows="10">' . htmlentities($cat['announce'], ENT_QUOTES, 'UTF-8') . '</textarea><br />' .
                     '<input type="checkbox" name="users" ' . ($cat['mod'] == 1 ? 'checked="checked"' : '') . ' />&#160;' .
                     'разрешить пользователям добавлять статьи<br />' .
                     '<input type="submit" name="submit" value="Сохранить" /></form></div>' .
                     '<div class="fmenu"><a href="?act=category&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';
            }

        }

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