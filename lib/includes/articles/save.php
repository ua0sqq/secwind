<?php

$access = FALSE;
$do = $do == 'edit' ? 'edit' : 'add';
$max_size = include H . 'engine/includes/max_file_size.php';
Core::get('text.class', 'classes');

if ($id === FALSE)
{

    $id = 0;

}

if ($admin)
{

    /* Открыть доступ для модераторов и администраторов */
    $access = TRUE;

}

if ($id !== 0 && $do == 'add' && $user_id)
{

    $cat = $sql->query("SELECT `mod`, `count_arc` FROM `mod_lib` WHERE `id` = " . $id . " AND `type` = 'cat'")->fetch();

    if ($cat !== FALSE)
    {

        if ($cat['mod'] == 1)
        {

            /* Открыть доступ для пользователей (для добавления статьи) */
            $access = TRUE;

        }

    }
    else
    {

        $error = 'Категория не найдена<br /><a href="index.php">В библиотеку</a>';

    }

}

if ($access === TRUE && $user_id && !isset($error))
{

    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $announce = isset($_POST['announce']) ? $_POST['announce'] : '';
    $text = isset($_POST['text']) ? $_POST['text'] : '';
    $tags = isset($_POST['tags']) ? $_POST['tags'] : '';
    $delfile = searchWord(array_keys($_POST));
    $form = '<form action="?act=articles&amp;mod=form&amp;do=' . ($do == 'add' ? 'add' : 'edit') . '&amp;id=' . $id . '" method="post">' .
        '<input type="hidden" name="title" value="' . htmlentities($title, ENT_QUOTES, 'UTF-8') . '" />' .
        '<input type="hidden" name="announce" value="' . htmlentities($announce, ENT_QUOTES, 'UTF-8') . '" />' .
        '<input type="hidden" name="text" value="' . htmlentities($text, ENT_QUOTES, 'UTF-8') . '" />' .
        '<input type="hidden" name="tags" value="' . htmlentities($tags, ENT_QUOTES, 'UTF-8') . '" />' .
        '<input type="submit" name="continue" value="Продолжить" /></form>';
    $error = array();

    if ($do == 'edit')
    {

        /* Получить данные статьи для редактировоания */
        $arc = $sql->query("SELECT `name` FROM `mod_lib` WHERE `id` = '" . $id . "' AND `type` = 'arc'")->result();
        if ($arc === FALSE)
        {

            $error[] = 'Статья не найдена<br />' . $form;

        }
        else
        {

            $title_old = my_esc($arc['name']);

        }

    }

    if (empty($error))
    {

        if (!empty($_POST))
        {

            /* Удаляем файл */
            if ($delfile)
            {

                $delfile = explode('_', $delfile);
                $ext = array_pop($delfile);
                $preview = FILESDIR . 'attach' . DIRECTORY_SEPARATOR . implode('_', $delfile) . '_preview.png';
                $delfile = implode('_', $delfile) . '.' . $ext;
                $filename = FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $delfile;

                if (!empty($_SESSION['files'][$delfile]))
                {

                    unset($_SESSION['files'][$delfile]);
                    $_SESSION['libFiles'] -= 1;

                }
                elseif($sql->query("SELECT COUNT(*) FROM `mod_lib_files` WHERE `aid` = '" . $id . "' AND `name` = '" . my_esc($delfile) . "'")->result() > 0)
                {

                    $sql->query("DELETE FROM `mod_lib_files` WHERE `aid` = '" . $id . "' AND `name` = '" . my_esc($delfile) . "'");

                }

                if (file_exists($preview))
                {

                    unlink($preview);

                }

                if (file_exists($filename))
                {

                    unlink($filename);
                    echo '<div class="err">Файл удален.<br />' . $form . '</div>';

                }
                else
                {

                    $error[] = 'Файл не существует';
                    $error[] = $form;

                }

            }
            /* Добавляем файл */
            elseif(isset($_POST['addfile']))
            {

                /* Проверка на максимальное кол-во файлов */
                if (!isset($_SESSION['libFiles']))
                {

                    $_SESSION['libFiles'] = 0;

                }
                else
                {

                    if ($_SESSION['libFiles'] == $libSet['files']['max_number'])
                    {

                        $error[] = 'Превышено максимально допустимое количество файлов для статьи.';

                    }

                }

                if (empty($error))
                {

                    /* Проверка размера файла */
                    if ($_FILES['file']['size'] > $max_size)
                    {

                        $error[] = 'Размер файла не должен превышать &#160;' . text::size_data($max_size);

                    }

                    $fname = $_FILES['file']['name'];
                    $ext = pathinfo($fname, PATHINFO_EXTENSION);


                    if (!in_array($ext, $libSet['files']['extensions']))
                    {

                        $error[] = 'К отправке разрешены файлы имеющие одно из следущих расширений &#160' . implode(', ', $libSet['files']['extensions']);

                    }

                    /* Проверка имени файла */
                    if (mb_strlen($fname) > 30)
                    {

                        $error[] = 'Длина имени файла не должна превышать 30 символов';

                    }
					/*
                    if (preg_match("/[^\da-z_\-.]+/", $fname))
                    {

                        $error[] = 'В имени файла присутствуют недопустимые символы';

                    }
					*/
                    $fname = FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $fname_ext[0] . '.' . $ext;

                    /* Проверка файла на существование */
                    if (file_exists($fname))
                    {

                        $fname_ext[0] .= '_' . time();
                        $fname = FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $fname_ext[0] . '.' . $ext;

                    }

                }

                if (empty($error))
                {

                    /* Загружаем файл */
                    if (copy($_FILES['file']['tmp_name'], $fname) === TRUE)
                    {

                        /* Создаем превью для изображения */
                        if (isImage($_FILES['file']['type']) && extension_loaded('gd'))
                        {

                            $imageSize = getimagesize($fname);
                            $types = array(1 => 'gif', 2=> 'jpg', 3 => 'png');
                            $imageType = array_key_exists($imageSize[2], $types) ? $types[$imageSize[2]] : FALSE;
                            if ($imageType !== FALSE)
                            {

                                $width = $imageSize[0];
                                $height = $imageSize[1];
                                if ($width > 220 || $height > 176)
                                {

                                    switch ($imageType)
                                    {

                                        case 'gif':
                                            $image = imagecreatefromgif($fname);
                                            break;
                                        case 'jpg':
                                            $image = imagecreatefromjpeg($fname);
                                            break;
                                        case 'png':
                                            $image = imagecreatefrompng($fname);
                                            break;
                                        default:

                                    }

                                    $max = $width > $height ? 'w' : 'h';
                                    $new_width = 176;
                                    $new_height = 220;

                                    if ($max == 'w' && $width > $new_width)
                                    {

                                        $new_height = intval(($new_width * $height) / $width);

                                    }

                                    if ($max == 'h' && $height > $new_height)
                                    {

                                        $new_width = intval(($new_height * $width) / $height);

                                    }

                                    $preview = imagecreate($new_width, $new_height);
                                    imagecopyresized($preview, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                                    imagepng($preview, FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $fname_ext[0] . '_preview.png');

                                }

                            }

                        }

                        $_SESSION['libFiles'] += 1;
                        $_SESSION['files'][$fname_ext[0] . '.' . $ext] = $_FILES['file']['type'];
                        echo '<div class="msg">Файл загружен ' . $form . '</div>';

                    }
                    else
                    {

                        $error[] = 'При загрузке файла произошла ошибка';
                        $error[] = $form;

                    }

                }
                else
                {

                    $error[] = $form;

                }

            }
            /* Сохраняем статью */
            elseif(isset($_POST['submit']))
            {

                /* Проверка заголовка */
                if (!empty($title))
                {

                    $title = my_esc($title);

                    if (mb_strlen($title) > 255)
                    {

                        $error[] = 'Превышена допустимая длина заголовка';

                    }

                    $total_title = intval($sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `refid` = '" . $id . "' AND `name` = '" . $title . "' AND `type` = 'arc'")->result());

                    if (($total_title > 0 && $do == 'add') || ($do == 'edit' && $title != $title_old && $total_title))
                    {

                        $error[] = 'Статья с таким названием уже есть';

                    }

                }
                else
                {

                    $error[] = 'Вы не ввели название';
                }

                /* Проверка анонса */
                if (!empty($announce))
                {

                    $announce = my_esc($announce);
                    if (mb_strlen($announce) > 255)
                    {

                        $error[] = 'Недопустимая длина анонса';

                    }

                }
                else
                {

                    $error[] = 'Вы не ввели анонс';

                }

                /* Проверка текста статьи */
                if (!empty($text))
                {

                    $text = my_esc($text);

                    if (mb_strlen($text) > 16777215)
                    {

                        $error[] = 'Превышена допустимая длина текста';

                    }

                }
                else
                {

                    $error[] = 'Вы не ввели текст';

                }

                /* Проверка меток */
                if (!empty($tags))
                {

                    $tags = explode(',', $tags);
					$tags = array_unique($tags);
                    $tags = array_map('trim', $tags);
                    $tags = my_esc(implode(',', $tags));

                    if (mb_strlen($tags) > 255)
                    {

                        $error = 'Превышена допустимая длина меток';

                    }

                }

                /* Сохраняем статью */
                if (empty($error))
                {

                    if ($do == 'add')
                    {
                        /* Добавление */
                        $sql->query(
                            "INSERT INTO `mod_lib` SET " .
                            "`refid` = '" . $id . "'," .
                            "`name` = '" . $title . "'," .
                            "`text` = '" . $text . "'," .
                            "`announce` = '" . $announce . "'," .
                            "`tags` = '" . $tags . "', " .
                            "`type` = 'arc'," .
                            "`author_id` = '" . $user_id . "'," .
                            "`author_name` = '" . $user['nick'] . "',".
                            "`mod` = '" . ($moder > 0 ? '0' : '1') . "'," .
                            "`time` = '" . time() . "';"
                        );

                        $aid = mysqli_insert_id($sql->db);

						if (file_exists(tmpDir . 'index_page.swc'))
							unlink(tmpDir . 'index_page.swc');
						Core::get('cache.class');
						Cache::multi_delete('lib[cat='.$id, tmpDir);
						
                        /* Обновляем счетчик записей */
                        if (isset($cat))
                        {
                            $sql->query("UPDATE `mod_lib` SET `count_arc` = '" . ($cat['count_arc'] + 1) .  "' WHERE `id` = '" . $id . "'");
                        }

                    }
                    else
                    {
                        /* Редактирование */
                       $sql->query(
                           "UPDATE `mod_lib` SET " .
                           "`name` = '" . $title . "', " .
                           "`text` = '" . $text . "', " .
                           "`announce` = '" . $announce . "', " .
                           "`tags` = '" . $tags . "', " .
                           "`edit_name` = '" . $user['nick'] . "', " .
                           "`edit_time` = '" . time() . "', " .
                           "`edit_id` = '" . $user_id . "' " .
                           "WHERE `id` = '" . $id . "'"
                       );

                    }

                    /* Добавляем файлы в базу */
                    if (!empty($_SESSION['files']))
                    {

                        $sql2 = "INSERT INTO `mod_lib_files` (`aid`, `name`) VALUES ";
                        $total = count($_SESSION['files']);
                        $i = 0;

                        foreach ($_SESSION['files'] as $name => $type)
                        {

                            if (file_exists(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $name))
                            {

                                $sql2 .= "('" . ($do == 'add' ? $aid : $id) . "', '" . my_esc($name) . "')" . ( ($i == ($total - 1)) ? ";" : "," );
                                $i++;

                            }

                        }

                        $sql->query($sql2);

                    }

                    unset($_SESSION['libFiles'], $_SESSION['files']);

                    /* Показываем сообщение о успешном сохранении */
                    if ($do == 'add')
                    {

                        if ($moder)
                        {

                            $message = 'Статья добавлена.&#160;<a href="?act=articles&amp;mod=view&amp;id=' . $aid . '">Продолжить</a>';

                        }
                        else
                        {

                            $message = 'Статья добавлена.&#160; Она будет доступна после проверки модератором. <a href="?act=category&amp;mod=view&amp;id=' . $id . '">Продолжить</a>';

                        }

                    }
                    else
                    {

                        $message = 'Статья сохранена &#160;<a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Продолжить</a>';

                    }

                    echo '<div class="fmenu">' . 'Библиотека' . ' | ' . ($do == 'add' ? 'Добавить статью' : 'Редактировать статью') . '</div>' .
                         '<div class="msg">' . $message . '</div>' .
                         '<div class="post"><a href="?act=' .
                         ($do == 'add' ? 'category' : 'articles') .
                         '&amp;mod=view&amp;id=' . $id . '">Назад</a></div>';

                }

            }
            else
            {

                $error[] = 'Ошибка входящих данных<br /><a href="?act=articles&amp;mod=form&amp;id=' . $id .'">Продолжить</a>';

            }

        }
        else
        {

            $error[] = 'Ошибка входящих данных<br /><a href="?act=articles&amp;mod=form&amp;id=' . $id .'">Продолжить</a>';

        }

    }

}
else
{

    $error = 'Доступ запрещен';

}