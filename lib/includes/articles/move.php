<?php

if ($admin)
{

    if ($id)
    {

        $arc = $sql->query("SELECT `refid`, `name` FROM `mod_lib` WHERE `id` = " . $id . " AND `type` = 'arc' AND `mod` = 0")->fetch();

        if ($arc !== FALSE)
        {

            /* Существуют ли категории в которые можно переместить статью? */
            $total = $sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `type` = 'cat' AND `id` != '" . $arc['refid'] . "'")->result();

            if ($total)
            {
                if (!empty($_POST))
                {
                    $cid = isset($_POST['cat']) ? abs(intval($_POST['cat'])) : 0;

                    /* Перемещаем статью */
                    if ($cid != $arc['refid'])
                    {

                        if ($cid != 0)
                        {
                            /* Проверка категории на существование */
                            $parent = $sql->query("SELECT `count_arc` FROM `mod_lib` WHERE `id` = '" . $cid . "' AND `type` = 'cat'")->fetch();

                        }

                        if (isset($parent) && $parent === FALSE)
                        {
                            $error = 'Статья не найдена';
                        }

                        if (!isset($error))
                        {

                            /* Перемещаем статью */
                            $sql->query("UPDATE `mod_lib` SET `refid` = '" . $cid . "' WHERE `id` = '" . $id . "'");

                            /* Обновляем счетчики */
                            if ($arc['refid'] != 0)
                            {

                                $ref = $sql->query("SELECT `count_arc` FROM `mod_lib` WHERE `id` = '" . $arc['refid'] . "'")->fetch();
                                $sql->query("UPDATE `mod_lib` SET `count_arc` = '" . ($ref['count_arc'] - 1) . "' WHERE `id` = '" . $arc['refid'] . "'");

                            }
                            if (isset($parent))
                            {

                                $sql->query("UPDATE `mod_lib` SET `count_arc` = '" . ($parent['count_arc'] + 1) ."' WHERE `id` = '" . $cid . "'");

                            }

                            echo '<div class="fmenu">' . 'Библиотека' . ' | Переместить статью: ' .
                                 htmlentities($arc['name'], ENT_QUOTES, 'UTF-8') .
                                 '</div><div class="msg">Статья перемещена
                                 &#160;<a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Продолжить</a></div>' .
                                 '<div class="fmenu"><a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

                        }

                    }
                    else
                    {

                        $error = 'Ошибка принятых данных';

                    }

                }
                else
                {
                    /* Список категорий */
                    echo '<div class="fmenu">' . 'Библиотека' . ' | Переместить статью: ' . htmlentities($arc['name'], ENT_QUOTES, 'UTF-8') .
                         '</div><div class="menu"><form action="?act=articles&amp;mod=move&amp;id=' . $id . '" method="post">' .
                         '<select name="cat">' . ($arc['refid'] != 0 ? '<option value="0">В корень</option>' : '');

                    $query = $sql->query("SELECT `id`, `name` FROM `mod_lib` WHERE `type` = 'cat' AND `id` != '" . $arc['refid'] . "'");

                    while($cat = $sql->fetch())
                    {

                        echo '<option value="' . $cat['id'] . '">' . htmlentities($cat['name'], ENT_QUOTES, 'UTF-8') . '</option>';

                    }

                    echo '</select><input type="submit" name="submit" value="Переместить статью" /></form></div><div class="fmenu"><a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

                }

            }
            else
            {

                $error = 'Список категорий пуст.';

            }

        }
        else
        {

            $error = 'Статья не найдена';

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

if (isset($error))
{

    $error .= '<br /><a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Назад</a>';

}