<?php

$set['title'] = 'Управление скриншотами';
include H.'engine/includes/head.php';


$sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();
if ($sql->num_rows() == 0 || !is_file($res_down['dir'] . '/' . $res_down['name']) || !$moder) {
    echo '<a href="index.php">К категориям</a>';
    include H.'engine/includes/foot.php';

}

$upload_max_filesize=ini_get('upload_max_filesize');
if (preg_match('#([0-9]*)([a-z]*)#i',$upload_max_filesize,$varrs))
{
if ($varrs[2]=='M')$upload_max_filesize=$varrs[1]*1048576;
elseif ($varrs[2]=='K')$upload_max_filesize=$varrs[1]*1024;
elseif ($varrs[2]=='G')$upload_max_filesize=$varrs[1]*1024*1048576;
}

$screen = array();
if ($do && is_file($screenroot . '/' . $id . '/' . $do)) {
    unlink($screenroot . '/' . $id . '/' . $do);
    header('Location: index.php?act=edit_screen&id=' . $id);
    exit;
}
else
    if (isset($_POST['submit'])) {
        require_once H.'engine/classes/class_upload.php';
        $handle = new upload($_FILES['screen']);
        if ($handle->uploaded) {
            $handle->file_new_name_body = $time;
            $handle->allowed = array('image/jpeg', 'image/gif', 'image/png');
            $handle->file_max_size = $upload_max_filesize;
            $handle->file_overwrite = true;
            $handle->image_resize = true;
            $handle->image_x = 240;
            $handle->image_ratio_y = true;
            $handle->image_convert = 'jpg';
            $handle->process($filesroot . '/screen/' . $id . '/');
            if ($handle->processed) {
                echo '<div class="msg"><b>Скриншот прикреплен</b>';
                if (file_exists(H.'engine/files/tmp/download[file='.$id.';page=1].swc'))
                    unlink(H.'engine/files/tmp/download[file='.$id.';page=1].swc');
            }
            else
                echo '<div class="err"><b>Скриншот не прикреплен: ' . $handle->error . '</b>';

        }
        else
            echo '<div class="err"><b>Не выбран файл</b>';
        echo '<br /><a href="index.php?act=edit_screen&amp;id=' . $id . '">Вернуться</a><br /><a href="index.php?act=view&amp;id=' . $id . '">К файлу</a></div>';
    }
    else {
        echo '<div class="p_m"><b>' . text::output($res_down['rus_name']) . '</b></div><div class="p_t"><b>Cкриншот</b></div>';
        if ($screen)
            echo '<div class="news"><img src="' . $screen . '" alt="screen"/></div>';
        echo '<div class="post"><form action="index.php?act=edit_screen&amp;id=' . $id . '"  method="post" enctype="multipart/form-data"><input type="file" name="screen"/><br /><input type="submit" name="submit" value="Выгрузить"/>';
        if ($screen)
            echo '&nbsp;<input type="submit" name="delscreen" value="Удалить"/>';
        echo '</form></div><div class="p_m"><small>Max. вес: ' . text::size_data($upload_max_filesize) . '<br />Скриншот будет автоматически преоброзаван в картинку, шириной не превышающую 240px (высота будет вычислина автоматически)<br />Новый файл заменить старый</small></div>';
        if (is_dir($screenroot . '/' . $id)) {
            $screen = glob($screenroot . '/' . $id . '/*.gif');
            $screen = array_merge($screen, glob($screenroot . '/' . $id . '/*.jpg'));
            $screen = array_merge($screen, glob($screenroot . '/' . $id . '/*.png'));
        }
        else {
            $dir = mkdir("$screenroot/$id", 0777);
            if ($dir = true)
                chmod("$screenroot/$id", 0777);
        }
        if ($screen) {
            $total = count($screen);
			$page = new page($total, $set['p_str']);
	
            for ($i = $page->start(); $i < $total; $i++) {
                $screen_name = htmlentities($screen[$i], ENT_QUOTES, 'utf-8');
                $file = preg_replace('#^' . $screenroot . '/' . $id . '/(.*?)$#isU', '$1', $screen_name, 1);
                echo (($i % 2) ? '<div class="p_m">' : '<div class="p_t">') . '
                  <table  width="100%"><tr><td width="40" valign="top"><a href="' . $screen_name . '"><img src="preview.php?type=1&amp;img=' . urlencode($screen_name) . '" alt="screen_' . $i . '" /></a></td><td>' . $file . '<div class="status"><a href="index.php?act=edit_screen&amp;id=' .
                    $id . '&amp;do=' . $file . '">Удалить</a></div></td></tr></table></div>';
            }
        }
        echo '<div class="p_m"><a href="index.php?act=view&amp;id=' . $id . '">Назад</a></div>';
    }