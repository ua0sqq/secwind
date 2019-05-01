<?php
$set['title'] = 'Добавление файла';
include H.'engine/includes/head.php';

$upload_max_filesize=ini_get('upload_max_filesize');
if (preg_match('#([0-9]*)([a-z]*)#i',$upload_max_filesize,$varrs))
{
if ($varrs[2]=='M')$upload_max_filesize=$varrs[1]*1048576;
elseif ($varrs[2]=='K')$upload_max_filesize=$varrs[1]*1024;
elseif ($varrs[2]=='G')$upload_max_filesize=$varrs[1]*1024*1048576;
}

$res = $sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND `type` = 1 LIMIT 1")->fetch();

if (is_dir($res['dir'] . '/' . $res['name'])) {
    if (($res['field']) || $admin) {
        $al_ext = $res['field'] ? explode(', ', $res['text']) : array('rar', 'zip', 'pdf', 'nth', 'txt', 'tar', 'gz', 'jpg', 'jpeg', 'gif', 'png', 'bmp', '3gp', 'mp3', 'mpg', 'sis', 'thm', 'jar', 'jad', 'cab', 'sis', 'sisx', 'exe', 'msi');
        if (isset($_POST['submit'])) {
            $load_cat = $res['dir'] . '/' . $res['name'];
            $do_file = false;
            if ($_FILES['fail']['size'] > 0) {
                $do_file = true;
                $fname = strtolower($_FILES['fail']['name']);
                $fsize = $_FILES['fail']['size'];
            }
            if ($do_file) {
                $new_file = isset($_POST['new_file']) ? trim($_POST['new_file']) : null;
                $name = isset($_POST['text']) ? trim($_POST['text']) : null;
                $name_link = isset($_POST['name_link']) ? check(mb_substr($_POST['name_link'], 0, 200)) : null;
                $ext = format($_FILES['fail']['name']);
                if (!empty($new_file)) {
                    $fname = $new_file . '.' . $ext;
                }
                if (empty($name))
                    $name = $fname;
                if (empty($name_link))
                    $err = 'Не заполнено поле.';
                if ($fsize > $upload_max_filesize)
                    $err = 'Вес файла превышает ' . text::size_data($upload_max_filesize);
                if (!in_array($ext, $al_ext))
                    $err = 'Запрещенный тип файла "'.$ext.'"! К отправке разрешены только файлы, имеющие следующее расширение: ' . implode(', ', $al_ext);
                if (strlen($fname) > 30)
                    $err = 'Длина названия файла и названия для сохранеия не должна превышать 30 символов';
                //if (!preg_match("#[^a-z0-9.()+_-]#", $fname))
                    //$err = 'В названии файла присутствуют недопустимые символы. Разрешены только латинские символы, цифры и некоторые знаки ( .()+_- ). Запрещены пробелы.';
                if (isset($err)) {
                    echo '<div class="err">'.$err.'<a href="index.php?act=down_file&amp;id=' . $id . '">Повторить</a></div>';
             
                }
                else {
                    if (file_exists("$load_cat/$fname"))
                        $fname = $time . $fname;

                    if ((move_uploaded_file($_FILES["fail"]["tmp_name"], "$load_cat/$fname")) == true) {
                        @chmod("$fname", 0777);
                        @chmod("$load_cat/$fname", 0777);
                        echo '<div class="msg">Файл прикреплен';
                        if ($set_down['mod'] && !$admin) {
                            echo ' и если пройдет модерацию - будет добавлен в загруз центр.';
                            $type = 3;
                        }
                        else
                            $type = 2;
                        echo '</div>';
                        $fname = my_esc($fname);
                        $text = isset($_POST['opis']) ? trim($_POST['opis']) : null;
                        $name = my_esc(mb_substr($name, 0, 200));
                        $sql->query("INSERT INTO `down_files` SET `refid`='$id', `dir`='$load_cat', `time`='$time',`name`='$fname', `text` = '$name_link',`rus_name`='$name', `type` = '$type',`user_id`='$user[id]'");
                        $file_id = mysqli_insert_id($sql->db);
                        if (!empty($text)) {
                            $files = fopen('about/' . $file_id . '.txt', 'w+');
                            flock($files, LOCK_EX);
                            fputs($files, $text);
                            flock($files, LOCK_UN);
                            fclose($files);

                        }
                        require_once H.'engine/classes/class_upload.php';
                        $handle = new upload($_FILES['screen']);
                        if ($handle->uploaded) {
                            $dir = mkdir("$screenroot/$file_id", 0777);
                            if ($dir = true)
                                chmod("$screenroot/$file_id", 0777);
                            $handle->file_new_name_body = $time;
                            $handle->allowed = array('image/jpeg', 'image/gif', 'image/png');
                            $handle->file_max_size = $upload_max_filesize;
                            $handle->file_overwrite = true;
                            $handle->image_resize = true;
                            $handle->image_x = 240;
                            $handle->image_ratio_y = true;
                            $handle->image_convert = 'jpg';
                            $handle->process($filesroot . '/screen/' . $file_id . '/');
                            if ($handle->processed) {
                                echo '<div class="msg">Скриншот прикреплен</div>';
                            }
                            //else
                              //  echo '<div class="err">Скриншот не прикреплен: ' . $handle->error . '</div>';
                        }
                        else
                            echo '<div class="err">Скриншот не прикрeплен</div>';
                        //if ($admin) {
                            echo '<div class="menu"><a href="index.php?act=view&amp;id=' . $file_id . '">К файлу</a></div>';
                            $dirid = $id;
                            $sql2 = '';
                            $i = 0;
                            while ($dirid != '0' && $dirid != "") {
                                $res_down = $sql->query("SELECT `refid` FROM `down_files` WHERE `type` = 1 AND `id` = '$dirid' LIMIT 1")->fetch();
                                if ($i)
                                    $sql2 .= ' OR ';
                                $sql2.= '`id` = \'' . $dirid . '\'';
                                $dirid = $res_down['refid'];
                                ++$i;
                            }
                            //$sql->free(true);
                            $sql->query("UPDATE `down_files` SET `total` = `total`+1 WHERE $sql2;");
                                                     
                            // $sql->free(true);
                            if (file_exists(H.'engine/files/tmp/download[dir='.$id.'].swc'))
                                unlink(H.'engine/files/tmp/download[dir='.$id.'].swc');
                        //}
                        echo '<div class="menu"><a href="index.php?act=down_file&amp;id=' . $id . '">Выгрузить еще</a></div>';
                        echo '<div class="menu"><a href="index.php?id=' . $id . '">Вернуться в категорию</a></div>';
                    }
                    else
                        echo '<div class="err">Ошибка прикрепления файла.<br /><a href="index.php?act=down_file&amp;id=' . $id . '">Повторить</a></div>';
                }
            }
            else
                echo '<div class="err">Не выбран файл.<br /><a href="index.php?act=down_file&amp;id=' . $id . '">Повторить</a></div>';


        }
        else {
            echo '<div class="fmenu">' . text::output($res['rus_name']) . '</div>';
            echo '<div class="post"><form action="index.php?act=down_file&amp;id=' . $id . '" method="post" enctype="multipart/form-data">
            Файл<span class="status">*</span>:<br /><input type="file" name="fail"/><br />
            Сохранить как (max. 30, без расширения):<br /><input type="text" name="new_file"/><br />
            Скриншот:<br /><input type="file" name="screen"/><br />
            Название файла (мах. 200):<br /><input type="text" name="text"/><br />
            Ссылка для скачки файла (мах. 200)<span class="red">*</span>:<br /><input type="text" name="name_link" value="Скачать файл"/><br />
            Описание (max. 500)<br /><textarea name="opis"></textarea>';
            echo '<br /><input type="submit" name="submit" value="Выгрузить"/></form>';
            echo '</div><div class="p_m"><small>Max. вес: ' .text::size_data($upload_max_filesize) . ', расширения: ' . implode(', ', $al_ext) . '<br />Скриншот будет автоматически преоброзаван в картинку, шириной не превышающую 240px (высота будет вычислина автоматически)</small></div>';
            echo '<div class="p_t"><a href="index.php?id=' . $id . '">Назад</a></div>';

        }
    }
    else
        echo 'Доступ закрыт<br /><a href="index.php?id=' . $id . '">К категории</a>';
}
else
    echo 'Каталог не существует<br /><a href="index.php">К категориям</a>';