<?php

$set['title'] = 'Обзор комментариев';
include H.'engine/includes/head.php';
echo '<div class="fmenu">Обзор комментариев</div>';
$i = 1;
$colmes = $sql->query("SELECT COUNT(*) FROM `down_comms`")->result();
$page = new page($colmes, $set['p_str']);

if ($colmes) {
    $req = $sql->query("SELECT `down_comms`.*, `down_comms`.`id` AS `cid`, `user`.`group_access`, `user`.`nick`, `user`.`date_last`, `user`.`pol`,  `user`.`id`, `down_files`.`rus_name`
FROM `down_comms` LEFT JOIN `user` ON `down_comms`.`user_id` = `user`.`id` LEFT JOIN `down_files` ON `down_comms`.`refid` = `down_files`.`id` ORDER BY `down_comms`.`time` DESC LIMIT ".$page->limit());
    while ($res = $sql->fetch()) {
        $text = '';
        echo $i % 2 ? '<div class="p_m">' : '<div class="p_t">';
        $post['status'] = $res['time'];
        $post['post'] = text::output($res['text']) . '<br /><a href="index.php?act=view&amp;id=' . $res['refid'] . '">' . text::output($res['rus_name']) . '</a> | <a href="index.php?act=comms&amp;id=' . $res['refid'] . '">Все комменты</a>';
        echo Core::user_show($res, $post).'</div>';
        ++$i;
    }
}
else
    echo '<div class="msg">Список пуст!</div>';

echo '<div class="fmenu">Всего: ' . $colmes . '</div>';

$page->display('index.php?act=comm_all&amp;');

echo '<a href="index.php">Загрузки</a>';