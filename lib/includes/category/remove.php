<?php

if ($admin)
{
    if ($id)
    {
        if (($cat = $sql->query("SELECT `refid` FROM `mod_lib` WHERE `id` = " . $id . " AND `type` = 'cat'")->fetch()) !== FALSE)
        {

            if (intval($sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `refid` = " . $id)->result()) > 0)
            {

                /* Категория содержит статьи или вложенные категории. Удаление невозможно. */
                $error = 'Необходимо удалить или переместить содержимое категории.'.
                    '<a href="?act=category&amp;mod=view&amp;id=' . $id . '">Назад</a>';

            }
            else
            {

                echo '<div class="fmenu">' . 'Библиотека' . ' | Удалить категорию</div>';

                if (isset($_GET['yes']))
                {

                    /* Удаляем категорию */

                    if ($cat['refid'] != 0)
                    {

                        /* Обновляем счетчик в родительской категории */
                        $ref = $sql->query("SELECT `counter` FROM `mod_lib` WHERE `id` = " . $cat['refid'])->fetch();
                        $sql->query("UPDATE `mod_lib` SET `counter` = " . ($ref['counter'] - 1) . " WHERE `id` = " . $cat['refid'] . ";");

                    }

                     $sql->query("DELETE FROM `mod_lib` WHERE `id` = " . $id . ";");
                     echo '<div class="msg">Категория удалена' .
                          '.&#160;<a href="?act=category&amp;mod=view&amp;id=' .
                          $cat['refid'] . '">Продолжить</a></div>';

                }
                else
                {

                    /* Форма подтверждения */
                    echo '<div class="rmenu">Вы действительно хотите удалить категорию?<br />' .
                         '<a href="?act=category&amp;mod=remove&amp;id=' . $id . '&amp;yes">Да</a>  |  ' .
                         '<a href="?act=category&amp;mod=view&amp;id=' . $id . '">Нет</a></div>';

                }

                echo '<div class="fmenu"><a href="index.php">В библиотеку</a></div>';

            }

        }
        else
        {

            /* Категория не существует */
            $error = 'Категория не найдена';

        }

    }
    else
    {

        /* Неверный идентификатор */
        $error = 'Ошибка принятых данных';

    }

}
else
{

    /* Доступ запрещен */
    $error = 'Доступ запрещен';

}