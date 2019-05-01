<?php

$set['title'] = 'Новые файлы';
$sql_down = '';
$i = 1;
if ($id) {

    $sql->query("SELECT * FROM `down_files` WHERE `type` = 1 AND `id` = '$id' LIMIT 1");
    $res_down_cat = $sql->fetch();

    if (!is_dir($res_down_cat['dir'] . '/' . $res_down_cat['name'])) {
        include H.'engine/includes/head.php';
        echo 'Каталог не существует<br /><a href="index.php">К категориям</a>';
        include H.'engine/includes/foot.php';
    }

    $title_pages = mb_substr($res_down_cat['rus_name'], 0, 30);
    $textl = 'Новые файлы: ' . (mb_strlen($res_down_cat['rus_name']) > 30 ? $title_pages . '...' : $title_pages);
    $sql_down = ' AND `dir` LIKE \'' . ($res_down_cat['dir'] . '/' . $res_down_cat['name']) . '%\' ';
}
include H.'engine/includes/head.php';

$total = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = '2'  AND `time` > $old $sql_down")->result();
$page = new page($total, $set['p_str']);
$cache = new cache(H.'engine/files/tmp/download[new_files='.$cur_page.'].swc');
if (!$cache->life())
{
ob_start();
echo '<div class="fmenu">' . $set['title'] . '</div>';

if ($total) {
    $sql->query("SELECT * FROM `down_files` WHERE `type` = '2'  AND `time` > $old $sql_down ORDER BY `time` DESC LIMIT ".$page->limit());
    while ($res_down = $sql->fetch()) {
        echo ($i % 2) ? '<div class="p_m">' : '<div class="p_t">';
        echo show_file($res_down);
        echo '</div>';
        ++$i;
    }
}
else {
    echo '<div class="err">Новыx файлов нет!</div>';
}
$page->display('index.php?id=' . $id . '&amp;act=new_files&amp;');
$cache->write();
}
echo $cache->read();

echo '<a href="index.php">Загрузки</a>';