<?php

$set['title'] = 'Файлы на модерации';
include H.'engine/includes/head.php';


if ($admin) {
    echo '<div class="fmenu">' . $set['title'] . '</div>';
    if ($id) {
        $sql->query("UPDATE `down_files` SET `type` = 2 WHERE `id` = '$id' LIMIT 1");
        echo '<div class="msg">Файл принят</div>';
    }
    else
        if (isset($_POST['all_mod'])) {
            $sql->query("UPDATE `down_files` SET `type` = 2 WHERE `type` = '3'");
            echo '<div class="msg">Все файлы приняты</div>';
        }
    $total = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = '3'")->result();
	$page = new page($total, $set['p_str']);
	
    if ($total) {
        $req_down = $sql->query("SELECT * FROM `down_files` WHERE `type` = '3' ORDER BY `time` DESC LIMIT ".$page->limit());
        while ($res_down = $sql->fetch()) {
            echo ($i % 2) ? '<div class="p_m">' : '<div class="p_t">';
            echo show_file($res_down);
            echo '<div class="status"><a href="index.php?act=mod_files&amp;id=' . $res_down['id'] . '">Принять</a> | <span class="red"><a href="index.php?act=del_file&amp;id=' . $res_down['id'] . '">Удалить</a></span></div></div>';
            ++$i;
        }
        echo '<div class="menu_razd"><form name="" action="index.php?id=' . $id . '&amp;act=mod_files" method="post"><input type="submit" name="all_mod" value="Принять все"/></form></div>';
    }
    else {
        echo '<div class="menu">Список пуст!</div>';
    }
    echo '<div class="fmenu">Всего: ' . $total . '</div>';
    $page->display('index.php?id=' . $id . '&amp;act=mod_files&amp;');
        echo '<p><form action="index.php" method="get"><input type="hidden" value="mod_files" name="act" /><input type="hidden" name="id" value="' . $id . '"/><input type="text" name="page" size="2"/><input type="submit" value="К странице &gt;&gt;"/></form></p>';
    echo '<a href="index.php">Загрузки</a>';
}
else
    header('Location: ' . $home . '/?err');