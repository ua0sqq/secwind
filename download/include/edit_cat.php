<?php

include H.'engine/includes/head.php';


if ($admin) {    $sql->query("SELECT * FROM `down_files` WHERE `type` = 1 AND `id` = '$id' LIMIT 1");
    $res = $sql->fetch();
    if (!$sql->num_rows() || !is_dir($res['dir'] . '/' . $res['name'])) {
        echo 'Каталог не существует<br /><a href="index.php">К категориям</a>';
        include H.'engine/includes/foot.php';
    }
    $al_ext = array('rar', 'zip', 'pdf', 'nth', 'txt', 'tar', 'gz', 'jpg', 'jpeg', 'gif', 'png', 'bmp', '3gp', 'mp3', 'mpg', 'sis', 'thm', 'jar', 'jad', 'cab', 'sis', 'sisx', 'exe', 'msi');
    if (isset($_POST['submit'])) {
        $rus_name = trim($_POST['rus_name']);
        if (empty($rus_name))
            $error = 'Не заполнено поле';
        $error_format = false;
        if ($admin == 9 && isset($_POST['user_down'])) {
            $format = trim($_POST['format']);
            $format_array = explode(', ', $format);
            foreach ($format_array as $value) {
                if (!in_array($value, $al_ext))
                    $error_format .= 1;
            }
            $user_down = 1;
            $format_files = my_esc($_POST['format'], true);
        }
        else {
            $user_down = 0;
            $format_files = '';
        }
        if ($error_format)
            $error = 'Можно писать только следующие расширения: ' . implode(', ', $al_ext);
        if ($error) {
            echo $error;
            echo '<div class="err"><a href="index.php?act=edit_cat&amp;id=' . $id . '">Повторить</a></div>';
           include H.'engine/includes/foot.php';
        }
        $rus_name = my_esc($rus_name, true);
        mysql_query("UPDATE `down_files` SET `field`='$user_down', `text` = '$format_files', `rus_name`='$rus_name' WHERE `id` = '$id' LIMIT 1");
        if (file_exists(H.'engine/files/tmp/download[dir='.$id.'].swc'))
        unlink(H.'engine/files/tmp/download[dir='.$id.'].swc');
        header('location: index.php?id=' . $id);
    }
    else {
        $name = text::output($res['rus_name']);
        echo '<div class="p_m">Изменение каталога: ' . $name . '</div><div class="post"><form action="index.php?act=edit_cat&amp;id=' . $id . '" method="post">
        Название для отображения:<br/><input type="text" name="rus_name" value="' . $name . '"/><br/>';
            echo '<div class="sub"><input type="checkbox" name="user_down" value="1"' . ($res['field'] ? ' checked="checked"' : '') . '/> Выгрузка файлов юзерами<br/>
                 Разрешенные расширение (zip, jar и тд.):<br/><input type="text" name="format" value="' . $res['text'] . '"/></div>
                 <div class="sub">Можно писать только следующие расширения:<br /> ' . implode(', ', $al_ext) . '<br />Другие Расширения в целях безопасности к выгрузке допускаться не будут</div>';
        echo ' <input type="submit" name="submit" value="Изменить"/><br/></form></div>';
    }
    echo '<div class="p_t">';
    if ($id)
        echo '<a href="index.php?id=' . $id . '">Назад</a><br />';
    echo '<a href="index.php">К категориям</a></div>';
}
else {
    header('Location: ' . $home . '/?err');
}