<?php

$sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();
if (!is_file($res_down['dir'] . '/' . $res_down['name']) || ($res_down['type'] == 3 && !$admin)) {
    $error = true;
}
else {
    $link = $res_down['dir'] . '/' . $res_down['name'];
}
$more = isset($_GET['more']) ? abs(intval($_GET['more'])) : false;
if ($more) {
    $sql->query("SELECT * FROM `down_more` WHERE `refid` = '$id' AND `id` = '$more' LIMIT 1");
    $res_more = $sql->fetch();
    if (!is_file($res_down['dir'] . '/' . $res_more['name'])) {
        $error = true;
    }
    else {
        $link = $res_down['dir'] . '/' . $res_more['name'];
    }
}
if ($error) {
    header('location: ../?err');
}
else {
    if (!isset($_SESSION['down_' . $id]))
        $sql->query("UPDATE `down_files` SET `field`=`field`+1 WHERE `id`='$id'", true);
    $_SESSION['down_' . $id] = 1;
    header('location: ' . $link);
}