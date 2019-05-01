<?php

$set['title'] = 'Поиск файлов';
include H.'engine/includes/head.php';


$search = isset ($_REQUEST['search']) ? rawurldecode(trim($_REQUEST['search'])) : false;
echo '<div class="post"><b>Поиск файла</b></div>';
echo '<form action="index.php?act=search" method="post"><div class="post"><p>';
echo 'Имя файла:<br /><input type="text" name="search" value="' . check($search) . '" />';
echo '<input type="submit" value="Поиск" name="submit" /><br />';
echo '</p></div></form>';
$error = false;
if (!empty ($search) && mb_strlen($search) < 3 || mb_strlen($search) > 64)
		$error = 'Недопустимая длина имени файла. Разрешено минимум 3 и максимум 64 символа';
if ($search && !$error) {	$search_db = strtr($search, array('_' => '\\_', '%' => '\\%', '*' => '%'));
	$search_db = '%' . $search_db . '%';
	$total = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = '2'  AND `rus_name` LIKE '" . my_esc($search_db) . "'")->result();
	$page = new page($total, $set['p_str']);
   $i=1;
	if ($total) {
    $req_down = $sql->query("SELECT * FROM `down_files` WHERE `type` = '2'  AND `rus_name` LIKE '" . my_esc($search_db) . "' ORDER BY `rus_name` ASC LIMIT ".$page->limit());
    	while ($res_down = $sql->fetch()) {
     		echo ($i % 2) ? '<div class="p_m">' : '<div class="post">';
        	echo show_file($res_down);
        	echo '</div>';
        	++$i;
    	}
	}
    else {
        echo '<div class="menu"><p>По Вашему запросу ничего не найдено</p></div>';
    }
	echo '<div class="fmenu">Всего найдено:  ' . $total . '</div>';
        $check_search = check(rawurlencode($search));
        $page->display('index.php?act=search&amp;search=' . $check_search . '&amp;');
    
	echo '<div class="menu"><a href="index.php?act=search">Новый поиск</a></div>';
}
else {
    if ($error)
        echo '<div class="err"><p>ОШИБКА!<br />' . $error . '</p></div>';
    echo '<div class="post">Поиск идет по Имени файла и нечувствителен к регистру букв. То есть, <b>FiLe</b> и <b>file</b> для поиска равноценны. <br />Длина запроса: 4мин., 64макс.</div>';
}
echo '<div class="post"><a href="index.php?">Загрузки</a></div>';