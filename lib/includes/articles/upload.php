<?php

if ($id)
{

    $cat = $sql->query("SELECT `name`, `mod` FROM `mod_lib` WHERE `type` = 'cat' AND `id` = " . $id )->fetch();
    if ($cat === FALSE)
    {

        unset($cat);

    }

}
else
{

    $id = 0;

}

$access = FALSE;
if ($user_id && isset($cat) && $cat['mod'] == 1)
{

    $access = TRUE;

}
elseif ($admin)
{

    $access = TRUE;

}

if ($access === TRUE)
{
	Core::get('text.class', 'classes');
	$max_size = include H . 'engine/includes/max_file_size.php';
    if (!empty($_POST))
    {

        if (!empty($_FILES))
        {

            $error = array();

            /* Проверка размера файла */
            if ($_FILES['file']['size'] > $max_size)
            {

                $error[] =  'Размер файла не должен превышать &#160;' . text::size_data($max_size);

            }

            /* Проверка имени файла */
            $fname = $_FILES['file']['name'];

            if (preg_match("/[^\A-z0-9_\-.]+/", $fname))
            {

                $error[] = 'В имени файла присутствуют недопустимые символы';

            }

            $ext = pathinfo($fname, PATHINFO_EXTENSION);

            if (($ext == 'txt' || $ext == 'zip') && empty($error))
            {

                $path = FILESDIR . 'upload' . DIRECTORY_SEPARATOR  . '.' . $ext;

                if (file_exists($path))
                {

                    $path = FILESDIR . 'upload' . DIRECTORY_SEPARATOR . 'copy_' . '_' . time() . '.' . $ext;

                }

                if (copy($_FILES['file']['tmp_name'], $path) === TRUE)
                {

                    switch ($ext)
                    {

                        case 'txt':
                            $file = fopen($path,'r');
                            $text = fread($file, filesize($path));
                            if (isset($_POST['winencode']))
                            {
                            
                                $text = @iconv('windows-1251', 'UTF-8', $text);
                            
                            }
                            fclose($file);
                            unlink($path);
                            echo '<div class="fmenu">' . 'Библиотека' . ' | Загрузка статей</div>' .
                                 '<div class="menu">Файл загружен' .
                                 '<br /><form action="?act=articles&amp;mod=form&amp;id=' . $id . '&amp;do=add" method="post">' .
                                 '<input type="hidden" name="text" value="' . htmlspecialchars(iconv('UTF-8', 'UTF-8//IGNORE', $text)) . '" />' . // вырезаем беспонтовые символы
                                 '<input type="submit" name="addarc" value="Продолжить" /></form></div>' .
                                 '<div class="fmenu"><a href="?act=category&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';
                            break;
                        case 'zip': // Не ставить break;
                        default:
                            Core::get('zip', 'classes');
                            $zip = new PclZip($path);
                            if (($contents = $zip->listcontent()) == FALSE)
                            {

                            	$error = 'Ошибка принятых данных';

                            }
                            else
                            {

                                if (count($contents) > $libSet['zip_deal'])
                                {

                                    $error = 'Кол-во файлов в архиве не должно превышать &#160;' . $libSet['zip_deal'];

                                }
                                else
                                {

                                    echo '<div class="fmenu">' . 'Библиотека' . ' | Загрузка статей</div>' .
                                         '<div class="msg">Обязательные поля помечены звездочкой. <span class="red">*</span></div>' .
                                         '<form action="?act=articles&amp;mod=upload&amp;id=' . $id . '" method="post" name="form">';

                                    $i = 0;
                                    foreach ($contents as $files)
                                    {

                                        echo '<div class="' . ($i % 2 ? 'p_m' : 'p_t') . '"><b>Имя файла</b>: ' . htmlentities($files['filename'], ENT_QUOTES, 'UTF-8') . '<br />';

                                        if ( $files['folder'] == FALSE && mb_substr_count($files['filename'], '/') == 0 )
                                        {

                                            $file = $zip->extract(PCLZIP_OPT_BY_INDEX, $files['index'],PCLZIP_OPT_EXTRACT_AS_STRING);
                                            $file = $file[0];
                                            if ($file['size'] > 16777215)
                                            {

                                                echo 'Ошибка: Превышена допустимая длина заголовка<br />';

                                            }
                                            elseif(mb_substr_count($file['filename'], '.txt') == 1)
                                            {

                                                if (isset($_POST['winencode']))
                                                {
                                                
                                                    $file['content'] = @iconv('windows-1251', 'UTF-8', $file['content']);
                                                
                                                }
                                                echo '<span class="red">*</span> Заголовок'.
                                                     ':<br /><input type="text" name="arc[' . $i . '][title]" /><br />' .
                                                     '<span class="red">*</span> Анонс' . 
                                                     ':<br /><input type="text" name="arc[' . $i . '][announce]" /><br />' .
                                                     '<span class="red">*</span> Текст' .
                                                     ':<br /><textarea rows="10" name="arc[' . $i . '][text]">' .
                                                     htmlentities($file['content'], ENT_QUOTES, 'UTF-8') .
                                                     '</textarea><br />Метки:<br />' .
                                                     '<input type="text" name="arc[' . $i . '][tags]" />' .
                                                     '<input type="hidden" name="arc[' . $i . '][filename]" value="' .
                                                     htmlentities($files['filename'], ENT_QUOTES, 'UTF-8') . '" />';

                                            }
                                            else
                                            {

                                                echo 'Ошибка: Недопустимое расширение файла. Файл дожен иметь расширение txt<br />';

                                            }

                                        }

                                        echo '</div>';
                                        $i++;

                                    }

                                    echo '<div class="msg"><input type="submit" name="submit" value="Добавить" /></div></form>' .
                                         '<div class="fmenu"><a href="?act=category&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

                                }

                            }
                            unset($zip);
                            unlink($path);
                            break;

                    }

                }
                else
                {

                    $error = 'При загрузке файла произошла ошибка';

                }

            }
            else
            {

                $error[] = 'К отправке разрешены файлы имеющие одно из следущих расширений. &#160;zip, txt.';

            }




        }
        elseif(isset($_POST['submit']))
        {

            if (!empty($_POST['arc']))
            {

                $sql2 = "INSERT INTO `mod_lib` (`refid`, `name`, `announce`, `text`, `tags`, `type`, `author_id`, `author_name`, `mod`, `time`) VALUES ";
                $i = 0;
                $total = count($_POST['arc']);
                $mod = $moder > 0 ? 0 : 1;
                $err = array();

                foreach($_POST['arc'] as $arc)
                {

                    $filename = !empty($arc['filename']) ? htmlentities($arc['filename'], ENT_QUOTES, 'UTF-8') : $i;

                    if (empty($arc['title']))
                    {

                        $err[$filename][] = 'Вы не ввели название';

                    }
                    else
                    {

                        $title = my_esc($arc['title']);

                        if (mb_strlen($title) > 255)
                        {

                            $err[$filename][] = 'Превышена допустимая длина заголовка.';

                        }

                        $total_title = intval($sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `refid` = '" . $id . "' AND `name` = '" . $title . "' AND `type` = 'arc'")->result());

                        if ($total_title > 0)
                        {

                            $err[$filename][] = 'Статья с таким названием уже есть';

                        }

                    }

                    if (!empty($arc['announce']))
                    {

                        $announce = my_esc($arc['announce']);
                        if (mb_strlen($announce) > 255)
                        {

                            $err[$filename][] = 'Недопустимая длина анонса';

                        }

                    }
                    else
                    {

                        $err[$filename][] = 'Вы не ввели анонс';

                    }

                    if (!empty($arc['text']))
                    {

                        $text = my_esc($arc['text']);
                        if (mb_strlen($text) > 65534)
                        {

                            $err[$filename][] = 'Превышена допустимая длина текста';

                        }

                    }
                    else
                    {

                        $err[$filename][] = 'Вы не ввели текст';

                    }

                    if (!empty($arc['tags']))
                    {

                        $tags = explode(',', $arc['tags']);
                        $tags = array_map('trim', $tags);
                        $tags = my_esc(implode(',', $tags));
                        if (strlen($tags) > 255)
                        {

                            $err[$filename][] = 'Превышена допустимая длина меток';

                        }

                    }
                    else
                    {

                        $tags = '';

                    }

                    if (empty($err))
                    {

                        $sql_do = TRUE;
                        $sql2 .= "('" . $id . "', '" . $title . "', '" . $announce .
                                "', '" . $text . "', '" . $tags . "', 'arc', '" . $user_id .
                                "', '" . $user['nick'] . "', '" . $mod . "', '" . time() .
                                "')" . ( ($i == ($total - 1)) ? ";" : "," ) . PHP_EOL;
                        unset($_POST['arc'][$i]);

                    }

                    $i++;

                }

                $message = '';

                if (isset($sql_do))
                {

                    $sql->query($sql2);
                    $message = 'Статьи добавлены &#160;<a href="?act=category&amp;mod=view&amp;id=' . $id . '">Продолжить</a>';

                }

                if (!empty($err))
                {

                    $message .= '<br />' . lng('err_zip_arcs_add') . ':';

                    foreach ($err as $aid => $mess)
                    {

                        $message .= '<br />' . $aid . ': ' . implode(' ', $mess);

                    }

                    $message .= '<br />Попробуйте еще раз.';

                }

                echo '<div class="fmenu">' . 'Библиотека' . ' | Загрузка статей</div>' .
                     '<div class="msg">' . $message . '<br /><a href="?act=articles&amp;mod=upload&amp;id=' . $id . '">Повторить' .
                     '</a> | <a href="index.php">В библиотеку</a></div>' .
                     '<div class="fmenu"><a href="?act=category&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

            }
            else
            {

                $error = 'Ошибка принятых данных';

            }

        }
        else
        {

            $error = 'Вы не выбрали файл';

        }

    }
    else
    {

        echo '<div class="fmenu">' . 'Библиотека' . ' | Загрузка статей </div><div class="msg">' .
             'Для загрузки разрешены файлы с расширениями zip или txt. <br />Zip файл должен содержать только текстовые файлы с расширением txt в количестве не более &#160;' . $libSet['zip_deal'] . '.</div><div class="post">' .
             '<form action="?act=articles&amp;mod=upload&amp;id=' . $id . '" method="post" enctype="multipart/form-data">' .
              'Выберите файл:<br /><input type="file" name="file" /><br />' .
             '<input type="checkbox" checked="checked" name="winencode" value="1" /> Win-1251<br />' .
             '<input type="submit" name="submit" value="Загрузить" /></form></div>' .
             '<div class="fmenu"><a href="?act=category&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

    }

}
else
{

    $error = 'Доступ запрещен';

}

if (!empty($error))
{

    $error = is_array($error) ? implode('<br />', $error) : $error;
    $error .= '<br /><a href="?act=articles&amp;mod=upload&amp;id=' . $id . '">Продолжить' .
              '</a> | <a href="index.php">В библиотеку</a>';

}