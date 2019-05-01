<?php

Core::get('cache.class', 'classes');

$cache = new cache(H . 'engine/files/tmp/lib[main=' . (isset($_GET['page']) ? intval($_GET['page']) : 1) . ';access=' . ($moder ? 1 : 0) . '].swc');

if (!$cache->life())
{
ob_start();

$total_cats = $sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `type` = 'cat'")->result();
$total_arcs = $sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `type` = 'arc'")->result();

/* Сортировка статей */
$sort = !empty($_GET['sort']) ? $_GET['sort'] : 'new';
$sortMenu = array(
	'views' => '<a href="?sort=views">Просмотры</a>',
	'rate'  => '<a href="?sort=rate">Рейтинг</a>',
	'comm'  => '<a href="?sort=comm">Комментарии</a>',
	'new'   => '<a href="?sort=new">Время</a>'
);
$sql2 = array();

switch ($sort) {
	/* Просмотры */
	case 'views':
		$sortName = 'Популярные статьи';
		$sortMenu['views'] = 'Просмотры';
		$sql2[] = "AND (`uni_views` > 0 OR `views` > 0)";
		$sql2[] = "`uni_views` DESC";
	break;

	/* Рейтинг */
	case 'rate':
		$sortName = 'Лучшие статьи';
		$sortMenu['rate'] = 'Рейтинг';
		$sql2[] = "";
		$sql2[] = "`rate_all` DESC";
	break;

	/* Комментарии */
	case 'comm':
		$sortName = 'Обсуждаемые статьи';
		$sortMenu['comm'] = 'Комментарии';
		$sql2[] = "AND `comm_count` > 0";
		$sql2[] = "`comm_count` DESC";
	break;

	/* По дате добавления */
	case 'new': // Не ставить break!
	default:
		$sortName = 'Новые статьи';
		$sortMenu['new'] = 'Время';
		$sql2[] = "AND `time` > " . (time() - 86400);
		$sql2[] = "`time` DESC";
	break;

}

$bm_arcs = $sql->query("SELECT COUNT(*) FROM `mod_lib_counters` WHERE `uid` = '" . $user_id . "' AND `type` = '2'")->result();

echo '<div class="fmenu">'; 
foreach($sortMenu as $menu)
echo $menu . ' &nbsp; &nbsp;  '; 
echo'</div>'
     // Категории
     . '<div class="msg"><p style="padding: 2px;"><img src="' . ICONSDIR . 'cat.png" alt="" style="float:left" />&#160;'
     . '<a href="?act=category&amp;mod=view">Категории</a>&#160; (' . $total_cats . '/' . $total_arcs . ')</p>'
     // Закладки
     . ($bm_arcs
	   ? '<p style="padding: 2px;"><img src="' . ICONSDIR . 'bookmark.png" alt="" style="float:left" />&#160;'
	     . '<a href="?act=bookmarks&amp;mod=view">Мои закладки</a> (' . $bm_arcs . ')</p>'
       : '')
     . '<p style="padding: 2px;"><img src="' . ICONSDIR . 'search.png" alt="" style="float:left" />&#160;'
	 . '<a href="?act=search&amp;mod=view">Поиск</a></p>'
     // Панель управления
     . ($moder
        ? '<p style="padding: 2px;"><img src="' . ICONSDIR . 'panel.png" alt="" style="float:left" />&#160;'
		  . '<a href="?act=panel&amp;mod=view">Панель управления</a></p>'
        : '')
     . '</div>';

/* Список статей */
$sql->query(
	"SELECT `id`, `name`, `announce`, `tags`, `time`, "
	. "`author_id`, `author_name`, `comm_count`, `views`, `uni_views`, "
	. "(`rate_plus` - `rate_minus`) as `rate_all` "
	. "FROM `mod_lib` WHERE `type` = 'arc' AND `mod` = '0' " . $sql2[0]
	. " ORDER BY " . $sql2[1] . " LIMIT " . $libSet['main_deal']
);

$total = $sql->num_rows();

if ($total) {
	echo '<div class="post"><strong>' . $sortName . '</strong>:</div>';
	$i = 0;
	while($arc = $sql->fetch()) {
		echo '<div class="'. ($i % 2 ? 'p_m' : 'p_t') . '">' . display_article($arc) . '</div>';
		$i++;
	}

} else {
	/* Список пуст */
	echo '<div class="menu" style="padding: 10px;"><strong>' . $sortName . '</strong>:<br />Список пуст</div>';
}
$tags = topTags($libSet['tags_max_cache_time']);
echo (! empty($tags)
      ? '<div class="aut"><b>Метки</b>: ' . $tags . '</div>'
	  : '')
	  . '<div class="fmenu">Всего:&#160;' . $total . '</div>';

$cache->write();
}
echo $cache->read();

