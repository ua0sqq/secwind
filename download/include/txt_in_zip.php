<?php

$dir_clean = opendir('time_files/created_zip');
while ($file = readdir($dir_clean)) {
    if ($file != 'index.php' && $file != '.htaccess' && $file != '.' && $file != '..') {
        $time_file = filemtime('time_files/created_zip/' . $file);
        if ($time_file < ($time - 300))
            unlink('time_files/created_zip/' . $file);
    }
}
closedir($dir_clean);
$sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();

if ($sql->num_rows() == 0 || !is_file($res_down['dir'] . '/' . $res_down['name']) || format($res_down['name']) != 'txt' || ($res_down['type'] == 3 && !$admin)) {
    include H.'engine/includes/head.php';   echo display_error('Файл не найден<br /><a href="index.php">К категориям</a>');
    include H.'engine/includes/foot.php';
}

if (isset($_GET['more'])) {
    $more = abs(intval($_GET['more']));
    $req_more = $sql->query("SELECT * FROM `down_more` WHERE `id` = '$more' LIMIT 1");
    $res_more = $sql->fetch();
    if (!$sql->num_rows() || !is_file($res_down['dir'] . '/' . $res_more['name']) || format($res_more['name']) != 'txt') {
        include H.'engine/includes/head.php';     
        echo Core::msg_show('Файл не найден<br /><a href="index.php">К категориям</a>');
        include H.'engine/includes/foot.php';
        exit;
    }
    $down_file = $res_down['dir'] . '/' . $res_more['name'];
    $title_pages = $res_more['rus_name'];
    $txt_file = $res_more['name'];
}
else {
    $down_file = $res_down['dir'] . '/' . $res_down['name'];
    $title_pages = $res_down['rus_name'];
    $txt_file = $res_down['name'];
}

if (!isset($_SESSION['down_' . $id])) {
    $sql->query("UPDATE `down_files` SET `field`=`field`+1 WHERE `id`='$id'");
    $_SESSION['down_' . $id] = 1;
}
$file = 'time_files/created_zip/' . $txt_file . '.zip';
if (!file_exists($file)) {
Core::get('zip');
    $zip = new PclZip($file);
    function w($event, &$header)
    {
        $header['stored_filename'] = basename($header['filename']);
        return 1;
    }
    $zip->create($down_file, PCLZIP_CB_PRE_ADD, 'w');
    chmod($file, 0644);
}

include H.'engine/includes/head.php';

echo '<div class="p_m"><b>' . text::output($title_pages) . '</b></div>';
echo '<div class="menu"><a href="' . text::output($file) . '">Скачать в ZIP</a></div>';
echo '<div class="menu"><input type="text" value="http://'.$_SERVER['HTTP_HOST'].'/' . $dir_load . '/' . text::output($file) . '"/><b></b></div>';
echo '<div class="menu">Файл будет доступен для скачивания в течение 5 минут</div>';
echo '<div class="p_t"><a href="index.php?act=view&amp;id=' . $id . '">К файлу</a></div>';

?>