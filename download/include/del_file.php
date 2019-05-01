<?php


$set['title'] = 'Удаление файла';
include H.'engine/includes/head.php';

$sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();
if ($sql->num_rows() == 0 || !is_file($res_down['dir'] . '/' . $res_down['name'])) {
    echo 'Файл не найден<br /><a href="index.php">К категориям</a>';
	include H.'engine/includes/foot.php';
}
if ($admin) {
    if (isset($_GET['yes'])) {
        if (is_dir($screenroot . '/' . $id)) {
            $dir_clean = opendir($screenroot . '/' . $id);
            while ($file = readdir($dir_clean)) {
                if ($file != '.' && $file != '..') {
                    @unlink($screenroot . '/' . $id . '/' . $file);
                }
            }
            closedir($dir_clean);
            rmdir($screenroot . '/' . $id);
        }
        $req_file_more = $sql->query("SELECT * FROM `down_more` WHERE `refid` = '$id'");
        if ($sql->num_rows()) {
            while ($res_file_more = $sql->fetch()) {
                if (is_file($res_down['dir'] . '/' . $res_file_more['name']))
                    @unlink($res_down['dir'] . '/' . $res_file_more['name']);
            }
            $sql->query("DELETE FROM `down_more` WHERE `refid` = '$id'");
        }
        if (is_file('about/' . $id . '.txt'))
            @unlink('about/' . $id . '.txt');
        $sql->query("DELETE FROM `down_comms` WHERE `refid`='$id'");
        @unlink($res_down['dir'] . '/' . $res_down['name']);
        $dirid = $res_down['refid'];
        $sql2 = '';
        $i = 0;
        while ($dirid != '0' && $dirid != "") {
            $res = $sql->query("SELECT `refid` FROM `down_files` WHERE `type` = 1 AND `id` = '$dirid' LIMIT 1")->fetch();
            if ($i)
                $sql2 .= ' OR ';
            $sql2 .= '`id` = \'' . $dirid . '\'';
            $dirid = $res['refid'];
            ++$i;
        }
        $sql->multi("UPDATE `down_files` SET `total` = (`total`-1) WHERE $sql2;DELETE FROM `down_files` WHERE `id` = '$id' LIMIT 1;OPTIMIZE TABLE `down_files`");
        if (file_exists(H.'engine/files/tmp/download[dir='.$id.'].swc'))
        unlink(H.'engine/files/tmp/download[dir='.$id.'].swc');
        header('Location: index.php?id=' . $res_down['refid']);
    }
    else {
        echo '<div class="err">Вы действительно хотите удалить файл?<br /> <a href="index.php?act=del_file&amp;id=' . $id . '&amp;yes">Удалить</a> | <a href="index.php?act=view&amp;id=' . $id . '">Отмена</a></div>';
    }
}
else {
    header('Location: ../?err');
}