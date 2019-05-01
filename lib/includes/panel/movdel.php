<?php

if ($admin)
{

    $id = $id ? $id : 0;

    if ((!empty($_POST['data']) && (isset($_POST['move']) || isset($_POST['delete']))))
    {

        /* Получаем и обрабатываем идентификаторы объектов */
        $ids = (isset($_POST['cat']) && isset($_POST['move'])) ? unserialize($_POST['data']) : $_POST['data'];
        $ids = array_map('intval', $ids);

        /* Перемещение категорий и статей */
        if (isset($_POST['move']))
        {

            /* Проверяем список объектов на тип и существование */
            $sql->query(
                "SELECT `id`, `refid`, `type` " .
                "FROM `mod_lib` WHERE `id` IN ('" . implode('\',\'', $ids) .
                "') AND (`type` = 'arc' OR `type` = 'cat')"
            );
            /* Список объектов прошедших проверку */
            $moves = array();
            $total_cats = 0;
            $total_arcs = 0;

            while($data = $sql->fetch())
            {

                if($data['type'] == 'cat')
                    $total_cats++;
                else
                    $total_arcs++;
                $moves[$data['refid']][] = $data['id'];

            }

            unset($data, $ids);

            /* Все ли объекты находятся в одной и той же категории? */
            if (count($moves) > 1)
            {

                $error = 'Ошибка принятых данных';

            }
            else
            {

                /* Идентификатор категории в которой находятся объекты */
                $refid = key($moves);
                /* Список объетов */
                $moves = array_shift($moves);
                /* Перемещаем объекты */
                if (isset($_POST['cat']))
                {

                    $cat = isset($_POST['cat']) ? abs(intval($_POST['cat'])) : 0;
                    if ( /* Проверка категории на существование */
                        isset($_POST['cat'])
                        && ($newcat = $sql->query(
                                "SELECT `counter`, `count_arc` FROM `mod_lib` " .
                                "WHERE `type` = 'cat' AND `id` = '" . $cat . "'"
                            )->fetch()) !== FALSE
                    )
                    {

                        if ($refid != 0)
                        {
                            /* Обновляем счетчики объектов в предыдущей категории */
                            $refcat = $sql->query("SELECT `counter`, `count_arc` FROM `mod_lib` WHERE `id` = '" . $refid . "'")->fetch();
                            $sql->query(
                                "UPDATE `mod_lib` SET " .
                                "`counter` = '" . ($refcat['counter'] - $total_cats) . "', " .
                                "`count_arc` = '" . ($refcat['count_arc'] - $total_arcs) . "' " .
                                "WHERE `id` = '" . $refid . "'"
                            );

                        }

                        if ($cat != 0)
                        {
                            /* Обновляем счетчики объектов в новой категории */
                            $newcat = $sql->query("SELECT `counter`, `count_arc` FROM `mod_lib` WHERE `id` = '" . $cat . "'")->fetch();
                            $sql->query(
                                "UPDATE `mod_lib` SET " .
                                "`counter` = '" . ($newcat['counter'] + $total_cats) . "', " .
                                "`count_arc` = '" . ($newcat['count_arc'] + $total_arcs) . "' " .
                                "WHERE `id` = '" . $cat . "'"
                            );

                        }
                        /* Перемещаем объекты */
                        $sql->query("UPDATE `mod_lib` SET `refid` = '" . $cat . "' WHERE `id` IN ('" . implode('\',\'', $moves) . "')");
                        $message = 'Отмеченные статьи и категории перемещены';

                    }
                    else
                    {

                        $error = 'Категория не найдена';

                    }

                }
                else
                {

                    /* Получаем список категорий */
                    $sql->query(
                        "SELECT `id`, `name` FROM `mod_lib` WHERE `id` NOT IN ('" .
                        implode('\',\'', $moves) . "', '" . $refid .
                        "') AND `type` = 'cat'"
                    );


                    echo '<div class="fmenu">' . 'Библиотека' . ' | ' . $set['title'] . '</div>' .
                         '<div class="menu"><form action="?act=panel&amp;mod=movdel&amp;id=' . $id . '" method="post">' .
                         'Выберите категорию<br /><select name="cat">' .
                         ($refid !== 0 ? '<option value="0">В корень</option>' : '');

                    while($data = $sql->fetch())
                    {
                        echo '<option value="' . $data['id'] . '">' . htmlentities($data['name'], ENT_QUOTES, 'UTF-8') . '</option>';
                    }


                    echo '</select><input type="hidden" name="data" value=\'' . (serialize($moves)) . '\' />' .
                         '<input type="submit" name="move" value="Переместить" /></form></div>' .
                         '<div class="fmenu"><a href="?act=panel&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

                }

            }


        }
        /* Удаляем объекты */
        elseif (isset($_POST['delete']))
        {

            /* Проверка объектов на тип и существование */
            $data = array('arc' => array(), 'cat' => array());
            $sql->query(
                "SELECT `refid`, `type`, `id` " .
                "FROM `mod_lib` WHERE `id` IN ('" .
                implode("', '", $ids) . "') AND " .
                "(`type` = 'arc' OR `type` = 'cat')"
            );
            while ($obj = $sql->fetch())
            {

                switch($obj['type'])
                {

                    case 'arc':
                        $data['arc'][$obj['refid']][] = $obj['id'];
                        break;
                    case 'cat':
                        $data['cat'][$obj['refid']][] = $obj['id'];
                        break;
                    default:

                }

            }

            /* Все ли объекты на ходятся в одной категории? */
            if ((count($data['arc']) > 1)
                || (count($data['cat']) > 1)
                || (   (key($data['arc']) != key($data['cat']))
                    && (!empty($data['cat']) && !empty($data['arc']))
                   )
            )
            {

                $error = 'Ошибка принятых данных';

            }
            else
            {

                $refid = !empty($data['arc']) ? key($data['arc']) : key($data['cat']);
                $cats = array_shift($data['cat']);
                $arcs = array_shift($data['arc']);

                /* Удаляем категории */
                if (!empty($cats))
                {

                    $sql->query("DELETE FROM `mod_lib` WHERE `id` IN('" . implode('\',\'', $cats) . "') AND `count_arc` = '0' AND `counter` = '0'");
                    if ($refid != 0)
                    {
                        $total_cats = mysqli_affected_rows($sql->db);
                        $refcat = $sql->query("SELECT `counter` FROM `mod_lib` WHERE `id` = '" . $refid . "'")->fetch();
                        $sql->query("UPDATE `mod_lib` SET `counter` = '" . ($refcat['counter'] - $total_cats) . "' WHERE `id` = '" . $refid . "'");

                    }

                }

                /* Удаляем статьи */
                if (!empty($arcs))
                {

                    $arcs_q = implode('\',\'', $arcs);
                    /* Удаляем комментарии */
                    $sql->query("DELETE FROM `mod_lib_comments` WHERE `sub_id` IN('" . $arcs_q . "')");
                    /* Удаляем счетчики и закладки */
                    $sql->query("DELETE FROM `mod_lib_counters` WHERE `aid` IN('" . $arcs_q . "')");
                    /* Удаляем файлы */
                    $sql->query("SELECT `name` FROM `mod_lib_files` WHERE `aid` IN('" . $arcs_q . "')");
                    if ($sql->num_rows())
                    {

                        while ($file = $sql->fetch())
                        {

                            $filename = FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $file['name'];
                            $ext = explode('.', $file['name']);

                            if (isImage($ext[1]))
                            {

                                if (file_exists(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $ext[0] . '_preview.png'))
                                {

                                    unlink(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $ext[0] . '_preview.png');

                                }

                            }

                            if (file_exists($filename))
                            {
                                unlink($filename);
                            }

                        }

                        $sql->query("DELETE FROM `mod_lib_files` WHERE `aid` IN('" . $arcs_q . "')");

                    }

                    foreach($arcs as $aid)
                    {

                        if (file_exists(FILESDIR . 'download' .DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $aid . '.zip'))
                        {

                            unlink(FILESDIR . 'download' .DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $aid . '.zip');

                        }
                        if (file_exists(FILESDIR . 'download' .DIRECTORY_SEPARATOR . 'txt' . DIRECTORY_SEPARATOR . $aid . '.zip'))
                        {

                            unlink(FILESDIR . 'download' .DIRECTORY_SEPARATOR . 'txt' . DIRECTORY_SEPARATOR . $aid . '.zip');

                        }

                    }

                    $sql->query("DELETE FROM `mod_lib` WHERE `id` IN('" . $arcs_q . "')");
                    if ($refid != 0)
                    {
                        $total_arcs = mysqli_affected_rows($sql->db);
                        $refcat = $sql->query("SELECT `count_arc` FROM `mod_lib` WHERE `id` = '" . $refid . "'")->fetch();
                        $sql->query("UPDATE `mod_lib` SET `count_arc` = '" . ($refcat['count_arc'] - $total_arcs) . "' WHERE `id` = '" . $refid . "'");
                    }

                }


            }

            $message = 'Отмеченные статьи и категории удалены';

        }

    }
    else
    {

        $message = 'Ошибка принятых данных';

    }

    if (!empty($message) && empty($error))
    {

        echo '<div class="fmenu">' . 'Библиотека' . ' | ' . $set['title'] . '</div>' .
             '<div class="menu">' . $message . '.<br /><a href="' .
             (isset($_GET['moderation']) ? '?act=panel&amp;mod=moderation' : '?act=panel&amp;mod=view&amp;id=' . $id) .
             '">Назад</a></div><div class="fmenu"><a href="index.php">В библиотеку</a></div>';

    }

}
else
{

    $error = 'Доступ запрещен';

}