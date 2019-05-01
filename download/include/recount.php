<?php

if ($admin)
 {
    if(isset($_GET['cat'])) {        $req = mysqli_query($sql->db, "SELECT `dir`, `name` FROM `down_files` WHERE `id` = '$id' AND `type` = 1 LIMIT 1");
        if (mysqli_num_rows($req)) {
            $res = mysqli_fetch_assoc($req);
            $dir_files = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = '2' AND `dir` LIKE '" . ($res['dir'] . '/' . $res['name']) . "%'")->result();
            $sql->query("UPDATE `down_files` SET `total` = '$dir_files' WHERE `id` = '$id'");
        }
    }
    else {        $req_down = mysqli_query($sql->db, "SELECT `dir`, `name`, `id` FROM `down_files` WHERE `type` = 1");
        while ($res_down = mysqli_fetch_assoc($req_down)) {            $dir_files = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = '2' AND `dir` LIKE '" . ($res_down['dir'] . '/' . $res_down['name']) . "%'")->result();
            $sql->query("UPDATE `down_files` SET `total` = '$dir_files' WHERE `id` = '" . $res_down['id']  . "'");
        }
    }
}
header('location: index.php?id=' . $id);