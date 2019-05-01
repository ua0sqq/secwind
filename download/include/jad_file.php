<?php

$sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();

if ($sql->num_rows() == 0 || !is_file($res_down['dir'] . '/' . $res_down['name']) || format($res_down['name']) != 'jar' || ($res_down['type'] == 3 && !$admin)) {
    include H.'engine/includes/head.php';   echo display_error('Файл не найден<br /><a href="index.php">К категориям</a>');
    include H.'engine/includes/foot.php';
}

if (isset($_GET['more'])) {
    $more = abs(intval($_GET['more']));
    $rsql->query("SELECT * FROM `down_more` WHERE `id` = '$more' LIMIT 1");
    $res_more = $sql->fetch();
    if (!$sql->num_rows() || !is_file($res_down['dir'] . '/' . $res_more['name']) || format($res_more['name']) != 'jar') {
        include H.'engine/includes/head.php';       echo display_error('Файл не найден<br /><a href="index.php">К категориям</a>');
        include H.'engine/includes/foot.php';
    }
    $down_file = $res_down['dir'] . '/' . $res_more['name'];
    $jar_file = $res_more['name'];
}
else {
    $down_file = $res_down['dir'] . '/' . $res_down['name'];
    $jar_file = $res_down['name'];
}

if (!isset($_SESSION['down_' . $id])) {
    $sql->query("UPDATE `down_files` SET `field`=`field`+1 WHERE `id`='$id'");
    $_SESSION['down_' . $id] = 1;
}

$size = filesize($down_file);
require_once H.'engine/classes/zip.php';
$zip = new PclZip($down_file);
$content = $zip->extract(PCLZIP_OPT_BY_NAME, 'META-INF/MANIFEST.MF', PCLZIP_OPT_EXTRACT_AS_STRING);
header('Content-type: text/vnd.sun.j2me.app-descriptor');
header('Content-Disposition: attachment; filename="' . basename($down_file) . '.jad";');
echo $content[0]['content'] . "\n" . 'MIDlet-Jar-Size: ' . $size . "\n" . 'MIDlet-Jar-URL: ' . $home . '/' . str_replace($filesroot, $dir_load, $res_down['dir']) . '/' . $jar_file;
exit;
