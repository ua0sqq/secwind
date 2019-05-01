<?php


$sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();
if ($sql->num_rows() == 0 || !is_file($res_down['dir'] . '/' . $res_down['name']) || !$admin) {
include H.'engine/includes/head.php';
    echo display_error('<a href="index.php">К категориям</a>');
    include H.'engine/includes/foot.php';

}

$set['title'] = $res_down['rus_name'];
include H.'engine/includes/head.php';

$upload_max_filesize=ini_get('upload_max_filesize');
if (preg_match('#([0-9]*)([a-z]*)#i',$upload_max_filesize,$varrs))
{
if ($varrs[2]=='M')$upload_max_filesize=$varrs[1]*1048576;
elseif ($varrs[2]=='K')$upload_max_filesize=$varrs[1]*1024;
elseif ($varrs[2]=='G')$upload_max_filesize=$varrs[1]*1024*1048576;
}

$al_ext = array('rar', 'zip', 'pdf', 'nth', 'txt', 'tar', 'gz', 'jpg', 'jpeg', 'gif', 'png', 'bmp', '3gp', 'mp3', 'mpg', 'sis', 'thm', 'jar', 'jad', 'cab', 'sis', 'sisx', 'exe', 'msi');
$del = isset($_GET['del']) ? abs(intval($_GET['del'])) : false;
$edit = isset($_GET['edit']) ? abs(intval($_GET['edit'])) : false;
if ($edit) {
    $name_link = isset($_POST['name_link']) ? check(mb_substr($_POST['name_link'], 0, 200)) : null;
    $sql->query("SELECT `rus_name` FROM `down_more` WHERE `id` = '$edit' LIMIT 1");
    if ($name_link && $sql->num_rows() && isset($_POST['submit'])) {
        $sql->query("UPDATE `down_more` SET `rus_name`='$name_link' WHERE `id` = '$edit' LIMIT 1", true);
        header('Location: index.php?act=file_more&id=' . $id);
    }
    else {
        $res_file_more = $sql->fetch();
        echo '<div class="post">' . text::output($res_down['rus_name']) . '</div><div class="p_m">Редактирвание доп. файла</div>';
        echo '<div class="p_m"><form action="index.php?act=file_more&amp;id=' . $id . '&amp;edit=' . $edit . '"  method="post">
        Имя для ссылки (мах. 200)<span style="color:red">*</span>:<br /><input type="text" name="name_link" value="' . $res_file_more['rus_name'] . '"/><br /><input type="submit" name="submit" value="Сохранить"/>';
        echo '</form></div><div class="menu"><a href="index.php?act=file_more&amp;id=' . $id . '">Отмена</a></div>';
    }
}
else
    if ($del) {
        $sql->query("SELECT `name` FROM `down_more` WHERE `id` = '$del' LIMIT 1");
        if ($sql->num_rows() && isset($_GET['yes'])) {
            $res_file_more = $sql->fetch();
            if (is_file($res_down['dir'] . '/' . $res_file_more['name']))
                unlink($res_down['dir'] . '/' . $res_file_more['name']);
            $sql->query("DELETE FROM `down_more` WHERE `id` = '$del' LIMIT 1", true);
            if (file_exists(H.'engine/files/tmp/download[file='.$id.';page=1].swc'))
                unlink(H.'engine/files/tmp/download[file='.$id.';page=1].swc');
            header('Location: index.php?act=file_more&id=' . $id);
        }
        else {
            echo '<div class="err">Вы действительно хотите удалить файл?<br /> <a href="index.php?act=file_more&amp;id=' . $id . '&amp;del=' . $del . '&amp;yes">Удалить</a> | <a href="index.php?act=file_more&amp;id=' . $id . '">Отмена</a></div>';
        }
    }
    else
        if (isset($_POST['submit'])) {
            if ($_FILES['fail']['size'] > 0) {
                $do_file = true;
                $fname = strtolower($_FILES['fail']['name']);
                $fsize = $_FILES['fail']['size'];
            }
            if ($do_file) {
                $error = false;
                $new_file = isset($_POST['new_file']) ? trim($_POST['new_file']) : null;
                $name_link = isset($_POST['name_link']) ? check(mb_substr($_POST['name_link'], 0, 200)) : null;
                $ext = explode(".", $fname);
                if (!empty($new_file)) {
                    $fname = strtolower($new_file . '.' . $ext[1]);
                    $ext = explode(".", $fname);
                }
                if (empty($name_link))
                    $error = 'Не заполнено поле.';
                if ($fsize > $upload_max_filesize)
                    $error = 'Вес файла превышает ' . size_file($upload_max_filesize);
                if (count($ext) != 2)
                    $error = 'Неправильное имя файла! К отправке разрешены только файлы имеющие имя и одно расширение (<b>name.ext</b>)';
                if (!in_array($ext[1], $al_ext))
                    $error = 'Запрещенный тип файла! К отправке разрешены только файлы, имеющие следующее расширение: ' . implode(', ', $al_ext);
                if (strlen($fname) > 30)
                    $error = 'Длина названия файла и названия для сохранеия не должна превышать 30 символов';
                if (preg_match("#[^a-z0-9.()+_-]#", $fname))
                    $error = 'В названии файла присутствуют недопустимые символы. Разрешены только латинские символы, цифры и некоторые знаки ( .()+_- ). Запрещены пробелы.';
                if ($error) {
                    $error = '<a href="index.php?act=file_more&amp;id=' . $id . '">Повторить</a>';
                    echo '<div class="err">'.$error.'</div>';
                }
                else {
                    $fname = file_exists($res_down['dir'].'/'.$fname) ? 'file' . $id . '_' . $time . $fname : 'file' . $id . '_' . $fname;
                    if ((move_uploaded_file($_FILES["fail"]["tmp_name"], "$res_down[dir]/$fname")) == true) {
                        @chmod("$fname", 0777);
                        @chmod("$load_cat/$fname", 0777);
                        echo '<div class="msg">Файл прикреплен<br /><a href="index.php?act=file_more&amp;id=' . $id . '">Продолжить</a><br /><a href="index.php?act=view&amp;id=' . $id . '">К файлу</a></div>';
                        $fname = my_esc($fname);
                        $sql->query("INSERT INTO `down_more` SET `refid`='$id', `time`='$time',`name`='$fname', `rus_name` = '$name_link',`size`='" . intval($fsize) . "'");
                        if (file_exists(H.'engine/files/tmp/download[file='.$id.';page=1].swc'))
                            unlink(H.'engine/files/tmp/download[file='.$id.';page=1].swc');
                    }
                    else
                        echo '<div class="err">Ошибка прикрепления файла.<br /><br /><a href="index.php?act=file_more&amp;id=' . $id . '">Повторить</a><br /><a href="index.php?act=view&amp;id=' . $id . '">К файлу</a></div>';
                }
            }
            else
                echo '<div class="err">Ошибка прикрепления файла.<br /><a href="index.php?act=file_more&amp;id=' . $id . '">Повторить</a><br /><a href="index.php?act=view&amp;id=' . $id . '">К файлу</a></div>';
        }
        else {
            echo '<div class="post"><b>' . text::output($res_down['rus_name']) . '</b></div><div class="p_m"><b>Дополнительные файлы</b></div>';
            echo '<div class="post"><form action="index.php?act=file_more&amp;id=' . $id . '"  method="post" enctype="multipart/form-data">Файл<span class="red">*</span>::<br /><input type="file" name="fail"/><br />
        Сохранить как (max. 30, без расширения):<br /><input type="text" name="new_file"/><br />
        Имя для ссылки (мах. 200)<span style="color:red">*</span>:<br /><input type="text" name="name_link" value="Скачать дополнительный файл файл"/><br /><input type="submit" name="submit" value="Выгрузить"/>';
            echo '</form></div><div class="menu"><small>Max. вес: ' . text::size_data($upload_max_filesize). ', расширения: ' . implode(', ', $al_ext) . '</small></div>';
            $sql->query("SELECT * FROM `down_more` WHERE `refid` = '$id'");
            $total_file = $sql->num_rows();
            $i = 1;
            if ($total_file) {
                while ($res_file_more = $sql->fetch()) {
                    echo ($i % 2) ? '<div class="p_m">' : '<div class="post">';
                    $format = explode('.', $res_file_more['name']);
                    $format_file = strtolower($format[count($format) - 1]);
                    echo '<table  width="100%"><tr><td width="16" valign="top"><img src="' . $filesroot . '/images/' . (file_exists($filesroot . '/images/' . $format_file . '.png') ? $format_file . '.png' : 'file.gif') . '" alt="file" />
               </td><td> ' . $res_file_more['rus_name'] . ' <br /><a href="index.php?act=file_more&amp;id=' . $id . '&amp;edit=' . $res_file_more['id'] . '">Изменить</a> <span class="red"><a href="index.php?act=file_more&amp;id=' . $id . '&amp;del=' .
                        $res_file_more['id'] . '"> [ X ] </a></span><div class="status">' . $res_file_more['name'] . ' (' . text::size_data($res_file_more['size']) . '), ' .  Core::time($res_file_more['time']) . '</div></td></tr></table></div>';
                    ++$i;
                }
                echo '<div class="menu">Всего: ' . $total_file . '</div>';
            }
            echo '<div class="menu"><a href="index.php?act=view&amp;id=' . $id . '">Назад</a></div>';
        }