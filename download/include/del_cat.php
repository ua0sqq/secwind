<?php

include H.'engine/includes/head.php';
	
	
	
if ($admin) {
    if ($sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = 1 AND`refid` = '$id'")->result() || !$sql->query("SELECT * FROM `down_files` WHERE `type` = 1 AND `id` = '$id' LIMIT 1")->num_rows()) 
    {
        echo 'Системная ошибка<br /><a href="index.php">К категориям</a>';
        include H.'engine/includes/foot.php'; 
    }
    $res = $sql->fetch();
    if (isset($_GET['yes'])) {    	$sql->query("SELECT * FROM `down_files` WHERE `refid` = '$id'");
        while ($res_down = $sql->fetch()) {        	if (is_dir($screenroot . '/' . $res_down['id'])) {            	$dir_clean = opendir($screenroot . '/' . $res_down['id']);
            	while ($file = readdir($dir_clean)) {
                	if ($file != '.' && $file != '..') {
                    	@unlink($screenroot . '/' . $res_down['id'] . '/' . $file);
                	}
            	}
            	closedir($dir_clean);
            	rmdir($screenroot . '/' . $res_down['id']);
			}
            $sql->query("SELECT * FROM `down_more` WHERE `refid` = '" . $res_down['id'] . "'");
             while ($res_file_more = $sql->fetch()) {
                @unlink($res_down['dir'] . '/' . $res_file_more['name']);
            }
            @unlink('about/' . $res_down['id']. '.txt');
            @unlink($res_down['dir'] . '/' . $res_down['name']);
			$sql->multi("DELETE FROM `down_more` WHERE `refid`='" . $res_down['id'] . "';DELETE FROM `down_comms` WHERE `refid`='" . $res_down['id'] . "'");
        }
        $sql->query("DELETE FROM `down_files` WHERE `refid` = '$id' OR `id` = '$id'");
        rmdir($res['down'] . '/' . $res['name']);
        if (file_exists(H.'engine/files/tmp/download[dir='.$id.'].swc'))
        unlink(H.'engine/files/tmp/download[dir='.$id.'].swc');
        header('location: index.php?id=' . $res['refid']);
	}
    else {    	echo '<div class="err">Вы действительно хотите удалить каталог?<br /> <a href="index.php?act=del_cat&amp;id=' . $id . '&amp;yes">Удалить</a> | <a href="index.php?id=' . $id . '">Отмена</a></div>';
	}
}
else {
    header('Location: /?err');
}