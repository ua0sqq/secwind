<?php

$set['title'] = 'Загрузки / Создание категории';
	
include H.'engine/includes/head.php';

if ($admin) 
{
    $al_ext = array('rar', 'zip', 'pdf', 'nth', 'txt', 'tar', 'gz', 'jpg', 'jpeg', 'gif', 'png', 'bmp', '3gp', 'mp3', 'mpg', 'sis', 'thm', 'jar', 'jad', 'cab', 'sis', 'sisx', 'exe', 'msi');

    if (!$id) {
        $load_cat = $loadroot;
    }
    else {
        $sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND `type` = 1 LIMIT 1");
        $res_down = $sql->fetch();
        if ($sql->num_rows() == 0 || !is_dir($res_down['dir'] . '/' . $res_down['name'])) {
            echo 'Каталог не существует';
            echo '<div class="err"><a href="index.php">К категориям</a></div>';
            include H.'engine/includes/foot.php';
        }
        $load_cat = $res_down['dir'] . '/' . $res_down['name'];
    }
    if (isset($_POST['submit'])) {
        $name = trim($_POST['name']);
        $rus_name = trim($_POST['rus_name']);
        if (empty($name) || empty($rus_name))
            $error = 'Не заполнено поле';
        if (preg_match("/[^0-9a-zA-Z]+/", $name))
            $error = 'Недопустимые символы в название папки<br/>';

        $error_format = false;
        if ($admin && isset($_POST['user_down'])) {
            $format = trim($_POST['format']);
            $format_array = explode(', ', $format);
            foreach ($format_array as $value) {
                if (!in_array($value, $al_ext))
                    $error_format .= 1;
            }
            $user_down = 1;
            $format_files = my_esc($_POST['format']);
        }
        else {
            $user_down = 0;
            $format_files = '';
        }
        if ($error_format)
            $error = 'Можно писать только следующие расширения: ' . implode(', ', $al_ext);
        if ($error) {
            echo $error;
            echo '<div class="err"><a href="index.php?act=add_cat&amp;id=' . $id . '">Повторить</a></div>';
            include H.'engine/includes/foot.php';
        }
        $name = my_esc($name);
        $rus_name = my_esc($rus_name);
        if(!is_dir("$load_cat/$name"))
            $dir = mkdir("$load_cat/$name", 0777);
        if ($dir == true) {
            chmod("$load_cat/$name", 0777);
            $sql->query("INSERT INTO `down_files` SET
            `refid`='$id',
            `dir`='$load_cat',
            `time`='$time',
            `name`='$name',
            `type` = '1',
            `field`='$user_down',
            `text` = '$format_files',
            `rus_name`='$rus_name'");
            $cat_id = mysqli_insert_id($sql->db);
            echo '<div class="msg">Папка создана</div>';
            echo '<a href="index.php?id=' . $cat_id . '">В папку</a><br/>';
            if (file_exists(H.'engine/files/tmp/download[dir='.$id.';page=1].swc'))
                unlink(H.'engine/files/tmp/download[dir='.$id.';page=1].swc');
        }
        else {
            echo 'Ошибка при создание категории';
            echo '<div class="err"><a href="index.php?act=add_cat&amp;id=' . $id . '">Повторить</a></div>';
            include H.'engine/includes/foot.php';
        }
    }
    else {
        echo '<div class="fmenu">Создание категории</div><div class="menu"><form action="index.php?act=add_cat&amp;id=' . $id . '" method="post">
        Название [A-Za-z0-9]:<br/><input type="text" name="name"/><br/>Название для отображения:<br/><input type="text" name="rus_name"/><br/>';

            echo '<div class="status"><input type="checkbox" name="user_down" value="1" /> Выгрузка файлов юзерами<br/>
                 Разрешенные расширение (zip, jar и тд.):<br/><input type="text" name="format"/></div>
                 <div class="status">Можно писать только следующие расширения:<br /> ' . implode(', ', $al_ext) . '<br />Другие Расширения в целях безопасности к выгрузке допускаться не будут</div>';
        
        echo ' <input type="submit" name="submit" value="Создать"/><br /></form></div>';
    }
    echo '<div class="menu">';
    if ($id)
        echo '<a href="index.php?id=' . $id . '">Назад</a><br />';
    echo '<a href="index.php">К категориям</a></div>';
}
else {
    header('Location: ' . $home . '/?err');
}
