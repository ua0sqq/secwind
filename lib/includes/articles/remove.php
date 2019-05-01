<?php


if ($admin)
{

    if ($id)
    {

        $arc = $sql->query("SELECT `refid` FROM `mod_lib` WHERE `id` = '" . $id . "' AND `type` = 'arc'")->fetch();
        if ($arc)
        {

            if (isset($_GET['moderation']))
            {

                $link_back = '?act=panel&amp;mod=moderation';

            }

            if (!empty($_POST))
            {

                /* Удаляем комментарии */
                $sql->query("DELETE FROM `mod_lib_comments` WHERE `sub_id` = '" . $id . "'");
                /* Удаляем счетчики и закладки */
                $sql->query("DELETE FROM `mod_lib_counters` WHERE `aid` = '" . $id . "'");
                /* Удаляем файлы */
                $files_q = $sql->query("SELECT `name` FROM `mod_lib_files` WHERE `aid` = '" . $id . "'");
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

                    $sql->query("DELETE FROM `mod_lib_files` WHERE `aid` = '" . $id . "'");

                }

                if (file_exists(FILESDIR . 'download' .DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $id . '.zip'))
                {

                    unlink(FILESDIR . 'download' .DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $id . '.zip');

                }
                if (file_exists(FILESDIR . 'download' .DIRECTORY_SEPARATOR . 'txt'. DIRECTORY_SEPARATOR . $id . '.zip'))
                {

                    unlink(FILESDIR . 'download' .DIRECTORY_SEPARATOR . 'txt' . DIRECTORY_SEPARATOR . $id . '.zip');

                }

                /* Обновляем счетчик статей в категории */
                if ($arc['refid'] != 0)
                {

                    $cat = $sql->query("SELECT `count_arc` FROM `mod_lib` WHERE `id` = '" . $arc['refid'] . "' AND `type` = 'cat'")->fetch();
                    $sql->query("UPDATE `mod_lib` SET `count_arc` = '" . ($cat['count_arc'] - 1) . "' WHERE `id` = '" . $arc['refid'] . "'");

                }

                /* Удаляем статью */
                $sql->query("DELETE FROM `mod_lib` WHERE `id` = '" . $id . "'");

                $link_back = isset($link_back) ? $link_back : '?act=category&amp;mod=view&amp;id=' . $arc['refid'];
                $message = 'Статья удалена.&#160;<a href="' . $link_back . '">Продолжить</a>';

            }
            else
            {

                $link_back = isset($link_back) ? $link_back : '?act=articles&amp;mod=view&amp;id=' . $id;

                $message = '<form action="" method="post">Вы действительно хотите удалить статью' .
                          '?<br /><input type="submit" name="submit" value="Удалить" />' .
                           '<a href="' . $link_back . '" style="text-decoration: none">' .
                           '<input type="button" name="back" value="Назад" /></a></form> ';

            }

        }
        else
        {

            $message = 'Статья не найдена';

        }

        if (!empty($message))
        {

            echo '<div class="fmenu">' . 'Библиотека' . ' | Удалить статью</div><div class="post">' . $message .
                 '</div><div class="fmenu"><a href="index.php">В библиотеку</a></div>';
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