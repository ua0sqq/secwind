<?php

if ($id)
{
    $check = $sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `id` = '" . $id . "' AND `type` = 'arc' AND `mod` = '0'")->result();
    if ($check)
    {
        if (isset($_POST['arc_type']))
        {
            $type = $_POST['arc_type'] == '0' ? 'txt' : 'html';
            $arc = $sql->query(
                "SELECT `name`, `text`, `edit_time`, `down_time`" .
                " FROM `mod_lib` WHERE `id` = '" . $id . "' AND `type` = 'arc' AND `mod` = '0'"
            )->fetch();
            $filename = FILESDIR . 'download' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $id . '.zip';

            if (((($arc['edit_time'] < $arc['down_time']) && $arc['down_time'] !== 0) || $arc['edit_time'] == 0) && file_exists($filename))
            {
               header('Location: /lib/files/download/' . $type . '/' . $id . '.zip');
            }
            else
            {

                switch($type)
                {

                    case 'txt':
                        $text = $arc['name'] . PHP_EOL . preg_replace('#\[img]([\da-z_\-.]+)\[/img]#is', '', $arc['text']);
                        break;
                    case 'html':
                        $text = htmlspecialchars($arc['text']);
                        $text = preg_replace_callback('#\[img]([\da-z_\-.]+)\[/img]#is', 'replaceImage', $text);
                        $text = "<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>" .
                                "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'><html><head>" .
                                '<style>body{max-width: 720px; margin: auto; background: #E1E1E1}' .
                                'a:link{color:blue; text-decoration:none}' .
                                'a:visited{color:#000f5f; text-decoration:none; font-weight:bold}' .
                                'a:hover, a:active{color:blue; text-decoration:underline}' .
                                '.content{padding: 4px 0px 4px 0px; margin: 5px 0px 5px 0px; border: 1px solid #AAA; background:#F8F8F8;}' .
                                '.title{text-align: center; font-size: 22pt; font-weight:bold; padding: 0px; margin:0px; border-bottom:1px solid #CCC;}' .
                                '.text{padding: 5px; margin:0px 2px 0px 2px;}' .
                                '.foot{text-align: center;padding: 0px; margin:0px; border-top:1px solid #CCC; }' .
                                '.phpcode {background-color: #E0E6E9; border: 1px dotted #9FAEBB;margin-top: 4px;padding: 0 2px 0 2px;}' .
                                '.quote {border-left: 4px solid #c0c0c0;color: #878787;font-size: x-small;margin-left: 2px;padding: 2px 0 2px 4px;}' .
                                '.bblist {color: #4A5663;padding: 0px 0px 0px 10px;}</style>' .
                                '<title>' . htmlspecialchars($arc['name']) . '</title></head><body><div class="content">' .
                                '<div class="title">' . htmlentities($arc['name'], ENT_QUOTES, 'UTF-8') . '</div><div class="text">' .
                                $text . '</div><div class="foot">Статья скачана с сайта ' . $_SERVER['SERVER_NAME'] .
                                '</div></div></body></html>';
                        break;
                    default:
                        break;

                }

                if (file_exists(FILESDIR . 'download' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $id . '.zip'))
                {

                    unlink(FILESDIR . 'download' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $id . '.zip');

                }

                $filename = FILESDIR . 'download' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $id . '.' . $type;

                /* Создаем файл с текстом статьи */
                if (file_exists($filename))
                {

                    unlink($filename);

                }

                $file = fopen($filename, 'w');

                if (flock($file, LOCK_EX) === FALSE)
                {

                    $error = 'При создании файла произошла ошибка. Попробуйте еще раз.';

                }
                else
                {

                    if (fwrite($file, $text) === FALSE)
                    {

                        $error = 'При создании файла произошла ошибка. Попробуйте еще раз.';

                    }

                    flock($file, LOCK_UN);
                    fclose($file);

                }

                if (empty($error))
                {

                    /* Создаем архив */
                    require_once H . 'engine/classes' . DIRECTORY_SEPARATOR . 'zip.php';
                    $archive = new PclZip(FILESDIR . 'download' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $id . '.zip');
                    $archive->add($filename, PCLZIP_OPT_REMOVE_ALL_PATH);

                    unlink($filename);
                    $files = $sql->query("SELECT `name` FROM `mod_lib_files` WHERE `aid` = '" . $id . "'");
                    $add = array();
                    $attach = FILESDIR . 'attach' . DIRECTORY_SEPARATOR;
                    while ($file = $sql->fetch())
                    {

                        if (file_exists($attach . $file['name']))
                        {


                            $add[] = $attach . $file['name'];

                        }

                        $preview = explode('.', $file['name']);
                        if (isImage($preview[1]))
                        {

                            if (file_exists($attach . $preview[0] . '_preview.png'))
                            {

                                $add[] = $attach . $preview[0] . '_preview.png';

                            }

                        }


                    }
                    $archive->add(
                        $add,
                        PCLZIP_OPT_REMOVE_PATH, $attach,
                        PCLZIP_OPT_ADD_PATH, 'files'
                    );


                    if (!file_exists(FILESDIR . 'download' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $id . '.zip'))
                    {

                        $error = 'При создании файла произошла ошибка. Попробуйте еще раз.';

                    }
                    else
                    {

                        $sql->query("UPDATE `mod_lib` SET `down_time` = '" . time() . "' WHERE `id` = '" . $id . "'");
                        echo '<div class="fmenu">' . 'Библиотека' . ' | ' . $set['title'] . '</div>' .
                              '<div class="msg"><a href="/lib/files/download/' . $type . '/' . $id . '.zip">Скачать</a></div>' .
                              '<div class="post"><a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

                    }

                }

            }

        }
        else
        {

            echo '<div class="fmenu">' . 'Библиотека' . ' | ' . $set['title'] . '</div>' .
                 '<div class="menu"><form action="?act=articles&amp;mod=download&amp;id=' . $id . '" method="post">
                 Выберите тип файла:<br />' .
                 '<input type="radio" name="arc_type" value="0" checked="checked" /> TXT<br />' .
                 '<input type="radio" name="arc_type" value="1" /> HTML<br />' .
                 '<input type="submit" name="submit" value="Скачать" /></form></div>' .
                 '<div class="fmenu"><a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

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

if (!empty($error))
{

    $error .= '<br /><a href="?act=articles&amp;mod=download&amp;id=' . $id . '">Продолжить</a>';

}