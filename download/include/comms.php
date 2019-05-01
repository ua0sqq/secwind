<?php


$sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();
if ($sql->num_rows() == 0 || !is_file($res_down['dir'] . '/' . $res_down['name']) || ($res_down['type'] == 3 && !$admin)) {
include H.'engine/includes/head.php';
    echo 'Файл не найден<br /><a href="index.php">К категориям</a>';
    include H.'engine/includes/foot.php';

}

$title_pages = htmlspecialchars(mb_substr($res_down['rus_name'], 0, 30));
$set['title'] = 'Комментарии: ' . (mb_strlen($res_down['rus_name']) > 30 ? $title_pages . '...' : $title_pages);
include H.'engine/includes/head.php';



	
echo '<div class="fmenu">' . $set['title'] . '</div>';
switch ($do) {
    case "delpost":
        $comm = isset($_GET['comm']) ? abs(intval($_GET['comm'])) : false;
        if ($admin && $comm) {
            if (isset($_GET['yes'])) {
                $sql->query("DELETE FROM `down_comms` WHERE `id`='$comm' LIMIT 1", true);
                $colmes = $sql->query("SELECT COUNT(*) FROM `down_comms` WHERE `refid`='$id'")->result();
                $sql->query("UPDATE `down_files` SET `total` = '$colmes' WHERE `id` = '$id' LIMIT 1");
                Core::stop('index.php?act=comms&id=' . $id);
            }
            else {
                echo '<div class="err">Вы действительно хотите удалить комментарий?<br />';
                echo '<a href="index.php?act=comms&amp;id=' . $id . '&amp;do=delpost&amp;comm=' . $comm . '&amp;yes">Удалить</a> | <a href="index.php?act=comms&amp;id=' . $id . '">Отмена</a></div>';
            }
        }
        break;
    case 'say':

            $msg = trim($_POST['msg']);
            if (empty($msg))
                $error = 'Не заполнено поле.';
            if ($error) {
                $error = '<a href="index.php?act=comms&amp;id=' . $id . '">Повторить</a>';
                echo '<div class="err">'.$error.'</div>';
            }
            else {
                $msg = my_esc(mb_substr($msg, 0, 500));
                $sql->query("INSERT INTO `down_comms` SET `time`='$time', `user_id`='$user[id]', `refid`='$id', `text`='$msg', `browser`='".my_esc($_SERVER['HTTP_USER_AGENT'], true)."'");
                $colmes = $sql->query("SELECT COUNT(*) FROM `down_comms` WHERE `refid`='$id'")->result();
                $sql->query("UPDATE `down_files` SET `total` = '$colmes' WHERE `id` = '$id' LIMIT 1");
                Core::stop('index.php?act=comms&id=' . $id);
            }

        break;
    default:
            echo '<div class="post"><form action="index.php?act=comms&amp;id=' . $id . '&amp;do=say" method="post">';
            echo 'Сообщение(max. 500):<br/><textarea cols="20" rows="2" name="msg"></textarea><br />';
            echo "<input type='submit' title='Нажмите для отправки' name='submit' value='Отправить'/></form></div>";
        
        $colmes = $sql->query("SELECT COUNT(*) FROM `down_comms` WHERE `refid`='$id'")->result();
		$page = new page($colmes, $set['p_str']);
        $i = 0;
		
        if ($colmes) {
            $sql->query("SELECT `down_comms`.*, `down_comms`.`id` AS `cid`, `user`.`group_access`, `user`.`nick`, `user`.`pol`, `user`.`id`
			FROM `down_comms` LEFT JOIN `user` ON `down_comms`.`user_id` = `user`.`id` WHERE `down_comms`.`refid`='$id' ORDER BY `down_comms`.`time` DESC LIMIT ".$page->limit());
            while ($res = $sql->fetch()) {
                $text = '';
                echo $i % 2 ? '<div class="p_m">' : '<div class="p_t">';
                $post = text::output($res['text']);
                $subtext = '<div class="status"><a href="index.php?act=comms&amp;id=' . $id . '&amp;do=delpost&amp;comm=' . $res['cid'] . '">Удалить</a></div>';
                echo '<img src="/style/users/icons/'.$res['pol'].'.png" alt="" /> '. $res['nick'] . '<span class="status">(' . Core::time($res['time']) . ')</span><br />'. $post . ($admin ? $subtext : null) . '</div>';
                ++$i;
            }
        }
        else {
            echo '<div class="err">Данный файл еще никто не комментировал!</div>';
        }
        echo '<div class="foot">Всего: ' . $colmes . '</div>';
       
        $page->display('index.php?act=comms&amp;id=' . $id . '&amp;');

        echo '<div class="aut"><a href="index.php?act=view&amp;id=' . $id . '">К файлу</a></div>';
}