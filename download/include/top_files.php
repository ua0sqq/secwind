<?php

$set['title'] = $id ? 'Самые комментируемые файлы' : 'Популярные файлы';
include H.'engine/includes/head.php';

$limit = $set_down['top'];
$i = 1;
echo '<div class="p_m"><b>' . $set['title']. '</b> (' . $limit . ')</div>';
$sql2 = $id ? '`total`' : '`field`';
$sql->query("SELECT * FROM `down_files` WHERE $sql2 > '0' ORDER BY $sql2 DESC LIMIT $limit");
echo '<div class="menu_razd">' . ($id ? '<a href="index.php?act=top_files&amp;id=0">Популярные</a>' : '<a href="index.php?act=top_files&amp;id=1">Самые комментируемые</a>') . '</div>';
while ($res_down = $sql->fetch()) {
    echo ($i % 2) ? '<div class="p_m">' : '<div class="p_t">';
    echo show_file($res_down);
    echo '</div>';
    ++$i;
}

echo '<div class="p_m"><br /></div><a href="index.php">Загрузки</a>';