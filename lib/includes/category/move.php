<?php

if ($admin)
{
    if ($id)
    {
        $cat = $sql->query("SELECT `refid`, `name` FROM `mod_lib` WHERE `id` = '" . $id . "' AND `type` = 'cat'")->fetch();

        if ($cat !== FALSE)
        {
            /* Количество категорий в которые можно переместить */
            $total = $sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `type` = 'cat' AND `id` != '" . $id . "'")->result();

            if ($total > 0)
            {
                if (!empty($_POST))
                {
                    /* ID категории в которую будем перемещать */
                    $cid = isset($_POST['category']) ? abs(intval($_POST['category'])) : 0;

                    if ($cid != $cat['refid'])
                    {
                        if ($cid != 0) $parent = $sql->query("SELECT `counter` FROM `mod_lib` WHERE `id` = '" . $cid . "' AND `type` = 'cat'")->result();

                        /* Проверка категории на существование */
                        if (isset($parent) && $parent === FALSE)
                        {
                            $error = 'Категория не найдена';
                        }

                        if (!isset($error))
                        {
                            /* Обновляем счетчик бывшей категории */
                            if ($cat['refid'] != 0)
                            {
                                $ref = $sql->query("SELECT `counter` FROM `mod_lib` WHERE `id` = '" . $cat['refid'] . "'")->fetch();
                                $sql->query("UPDATE `mod_lib` SET `counter` = '" . ($ref['counter'] - 1) . "' WHERE `id` = '" . $cat['refid'] . "'");
                            }

                            /* Обновляем счетчик новой категории */
                            if (isset($parent) && $parent !== FALSE)
                            {
                                $sql->query("UPDATE `mod_lib` SET `counter` = '" . ($parent['counter'] + 1) . "' WHERE `id` = '" . $cid . "'");
                            }

                            /* Перемещаем категорию */
                            $sql->query("UPDATE `mod_lib` SET `refid` = '" . $cid . "' WHERE `id` = '" . $id . "'");
                            echo '<div class="fmenu">' . 'Библиотека' . ' | Переместить категорию | ' . htmlentities($cat['name'], ENT_QUOTES, 'UTF-8') .
                                 '</div><div class="msg">Категория перемещена' .
                                 '.&#160;<a href="?act=category&amp;mod=view&amp;id=' . $id . '">Продолжить</a>' .
                                 '</div><div class="fmenu"><a href="?act=category&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

                        }

                    }
                    else
                    {

                        /* Попытка переместить в ту же категорию в которой сейчас находится эта категория */
                        $error = 'Ошибка принятых данных';

                    }

                }
                else
                {

                    /* Список категорий */
                    echo '<div class="fmenu">' . 'Библиотека' . ' | Переместить категорию | ' . htmlentities($cat['name'], ENT_QUOTES, 'UTF-8') . '</div>' .
                         '<div class="post"><form action="?act=category&amp;mod=move&amp;id=' . $id .'" method="post">Выберите категорию'  .
                         ':<br /><select name="category">' . ($cat['refid'] != 0 ? '<option value="0">В корень</option>' : '');

                    $sql->query("SELECT `id`, `name` FROM `mod_lib` WHERE `type` = 'cat' AND `id` != '" . $id . "'");

                    while ($all = $sql->fetch())
                    {
                        echo '<option value="' . $all['id'] . '">' . htmlentities($all['name'], ENT_QUOTES, 'UTF-8') . '</option>';
                    }

                    echo '</select><input type="submit" name="submit" value="Переместить" /></form></div>' .
                         '<div class="fmenu"><a href="?act=category&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

                }

            }
            else
            {

                /* Нет категорий в которые можно было переместить */
                $error = 'Список категорий пуст.';

            }

        }
        else
        {

            /* Категория не найдена */
            $error = 'Категория не найдена';

        }

    }
    else
    {

        /* Неверный ID */
        $error = 'Ошибка принятых данных';

    }

}
else
{

    /* Доступ запрещен */
    $error = 'Доступ запрещен';

}


if (!empty($error))
{

    $error .= '<br /><a href="?act=category&amp;mod=view&amp;id=' . $id . '">Продолжить</a>';

}