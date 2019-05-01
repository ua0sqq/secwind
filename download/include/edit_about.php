<?php

include H.'engine/includes/head.php';

$sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();
if ($sql->num_rows() == 0 || !is_file($res_down['dir'] . '/' . $res_down['name']) || !$admin) {
    echo '<a href="index.php">К категориям</a>';
    include H.'engine/includes/foot.php';

}
if (isset($_POST['submit'])) {
    $about = trim($_POST['opis']);
    if (!empty($about)) {
        $files = fopen('about/' . $id . '.txt', 'w+');
        flock($files, LOCK_EX);
        fputs($files, $about);
        flock($files, LOCK_UN);
        fclose($files);
    } elseif (is_file('about/' . $id . '.txt')) {
        unlink('about/' . $id . '.txt');
    }
    if (file_exists(H.'engine/files/tmp/download[file='.$id.';page=1].swc'))
        unlink(H.'engine/files/tmp/download[file='.$id.';page=1].swc');
    exit(header('Location: index.php?act=view&id=' . $id));
}
else {
    if (is_file('about/' . $id . '.txt'))
        $about = htmlspecialchars(file_get_contents('about/' . $id . '.txt'));
	else
	    $about = null;
    echo 
	    '<div class="p_m">' . text::output($res_down['rus_name']) . '</div><div class="p_m">Описание</div>'.
        '<div class="post"><form action="index.php?act=edit_about&amp;id=' . $id . '" method="post">'.
		'<small>Максимум 500 символов</small><br /><textarea name="opis">' . $about . '</textarea>'.
        '<br /><input type="submit" name="submit" value="Отправить"/></form></div>'.
        '<div class="p_t"><a href="index.php?act=view&amp;id=' . $id . '">Назад</a></div>';
}