<?php

if ($admin)
 {
    if(isset($_GET['cat'])) {
        if (mysqli_num_rows($req)) {
            $res = mysqli_fetch_assoc($req);
            $dir_files = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = '2' AND `dir` LIKE '" . ($res['dir'] . '/' . $res['name']) . "%'")->result();
            $sql->query("UPDATE `down_files` SET `total` = '$dir_files' WHERE `id` = '$id'");
        }
    }
    else {
        while ($res_down = mysqli_fetch_assoc($req_down)) {
            $sql->query("UPDATE `down_files` SET `total` = '$dir_files' WHERE `id` = '" . $res_down['id']  . "'");
        }
    }
}
header('location: index.php?id=' . $id);