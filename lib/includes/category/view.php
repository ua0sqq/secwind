<?php

Core::get('cache.class', 'classes');

$cache = new cache(H . 'engine/files/tmp/lib[cat='.$id.';page='.(isset($_GET['page']) ? intval($_GET['page']) : 1).'].swc');

if ($id) {
	$cat = $sql->query("SELECT `refid`, `name`, `mod` FROM `mod_lib` WHERE `id` = " . $id . " AND `type` = 'cat'")->fetch();
	if ($cat === FALSE) {
		/* Категория не найдена */
		$set['title'] = 'Библиотека';
		require H . 'engine/includes/head.php';
		$error = 'Категория не найдена <a href="?act=category&amp;mod=view">Продолжить</a>';
	}
} else {
	$id = 0;
}
if (empty($error)) {

$set['title'] = isset($cat) ? htmlspecialchars($cat['name']) : 'Категории';
	require H . 'engine/includes/head.php';
    if (!$cache->life())
    {
    ob_start();

	echo '<div class="fmenu"><b>' . 'Библиотека' . ' | '
	     . ( isset($cat) ? htmlentities($cat['name'], ENT_QUOTES, 'UTF-8') : 'Категории' )
		 . '</b></div>';

    Core::get('page.class', 'classes');
	/* Счетчики */
	$total_cat = intval($sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `refid` = " . $id . " AND `type` = 'cat'")->result());
	$total_arc = intval($sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `refid` = " . $id . " AND `type` = 'arc'  AND `mod` = '0'")->result());
	$total = $total_arc + $total_cat;
    $page = new page($total, $set['p_str']);
	/* Сортировка */
	$sortMenu = array(
		'name' => '<a href="?act=category&amp;mod=view&amp;id=' . $id . '&amp;sort=name">Название</a>',
		'time' => '<a href="?act=category&amp;mod=view&amp;id=' . $id . '&amp;sort=time">Время</a>',
		'comm' => '<a href="?act=category&amp;mod=view&amp;id=' . $id . '&amp;sort=comm">Комментарии</a>',
		'rate' => '<a href="?act=category&amp;mod=view&amp;id=' . $id . '&amp;sort=rate">Рейтинг</a>'
	);
	$sort = !empty($_REQUEST['sort']) ? (array_key_exists($_REQUEST['sort'], $sortMenu) ? $_REQUEST['sort'] : 'name') : 'name';
	switch ($sort) {
		case 'time':
			$sortMenu['time'] = 'Время';
			$order = "`time` DESC";
		break;
		case 'rate':
			$sortMenu['rate'] = 'Рейтинг';
			$order = "`rate_all` DESC";
		break;
		case 'comm':
			$sortMenu['comm'] = 'Комментарии';
			$order = "`comm_count` DESC";
		break;
		case 'name': // Не ставить break;
		default:
			$sortMenu['name'] = 'Название';
			$order = "`name` ASC";
		break;
	}
	echo '<div class="fmenu">Сортировка: '; 
        foreach($sortMenu as $menu)
            echo $menu . ' &nbsp; &nbsp;  '; 
    echo '</div>';
	if (isset($cat)) {
		/* Ссылка для перехода на категорию выше */
		$ref = $sql->query("SELECT `name` FROM `mod_lib` WHERE `id` = " . $cat['refid'] . " AND `type` = 'cat'")->fetch();
		if ($ref == FALSE) $ref['name'] = 'Категории';
		echo '<div class="msg"><img src="/lib/files/icons/up.png"/> <a href="?act=category&amp;mod=view&amp;id=' . $cat['refid'] . '">' .
		htmlentities($ref['name'], ENT_QUOTES, 'UTF-8') . '</a></div>';
		unset($ref);
	}
	/* Список категорий и статей */
	if ($total > 0) {
		$query = $sql->query(
			"SELECT `id`, `refid`, `name`, `announce`, `counter`, `count_arc`, `type`, `tags`, `time`, `author_id`, `author_name`, " .
			"(`rate_plus` - `rate_minus`) as `rate_all`, `comm_count`, `views`, `uni_views`, `mod` " .
			"FROM `mod_lib` WHERE `refid` = " . $id . " " .
			"AND (`type` = 'cat' OR (`type` = 'arc' AND `mod` = '0')) ORDER BY `type` DESC, " . $order . " LIMIT " . $page->limit()
		);
		$i = 0;
		while($content = $sql->fetch()) {
			echo '<div class="'. ($i % 2 ? 'p_m' : 'p_t') . '">'
			     . ($content['type'] == 'arc'
				    ? display_article($content)
					: display_category($content, '?act=category&amp;mod=view&amp;id=')
			       )
		         . '</div>';
		$i++;
		}
	} else {
		echo '<div class="menu">Список пуст</div>';
	}
	echo '<div class="fmenu">Всего: ' . $total . '</div>';
	/* Постраничная навигация */
	$page->display('?act=category&amp;mod=view&amp;id=' . $id . '&amp;sort=' . $sort . '&amp;');
    $cache->write();
    }
    echo $cache->read();


	/* Различные функции */
	echo '<div class="menu"><a href="index.php">В библиотеку</a>';
	if ( $id > 0 && ((isset($cat['mod']) && $cat['mod'] == 1 && $user_id) || $moder)) {
		echo '<br /><a href="?act=articles&amp;mod=form&amp;do=add&amp;id=' . $id . '">Добавить статью</a>'
		     . '<br /><a href="?act=articles&amp;mod=upload&amp;id=' . $id . '">Загрузить статью</a>';
	}
	if ($admin) {
		echo '<br /><a href="?act=category&amp;mod=add&amp;id=' . $id . '">Добавить категорию</a>' .
		     ($id !== 0
		      ? '<br /><a href="?act=category&amp;mod=edit&amp;id=' . $id . '">Редактировать категорию</a>'
		        . '<br /><a href="?act=category&amp;mod=remove&amp;id=' . $id . '">Удалить категорию</a>'
		        . '<br /><a href="?act=category&amp;mod=move&amp;id=' . $id . '">Переместить категорию</a>'
		      : ''
		);
	}
	echo '</div>';
}