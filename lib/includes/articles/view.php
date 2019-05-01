<?php

$error = array();
if (!$id) {
	$error[] = 'Ошибка входящих данных'; // =)
} else {
    Core::get(array('page.class', 'text.class', 'cache.class'), 'classes');

	$strs = !empty($_REQUEST['strings']) ? abs(intval($_REQUEST['strings'])) : 500;
    $total = mb_strlen($sql->query("SELECT `text` FROM `mod_lib` WHERE `id` = " . $id ." AND `type` = 'arc'")->result());
	if (isset($_GET['page']))
    {
        if ($_GET['page'] == 'end')
            $_GET['page'] = ceil($total / $strs);
        if ($_GET['page']  > 1)
		    $start = $_GET['page'] * $strs - $strs;
	}
	$start = isset($start) == 0 ? 1 : $start;

	$arc = $sql->query(
			"SELECT `refid`, `name`, SUBSTRING(`text` FROM " . $start . " FOR " . $strs
			. ") as `content`, `announce`, `tags`, `time`, " .
			"`author_id`, `author_name`, `comm_count`, " .
			"`views`, `uni_views`, `rate_plus`, `rate_minus`, " .
			"`edit_name`, `edit_id`, `edit_time`" .
			" FROM `mod_lib` WHERE `id` = " . $id .
			" AND `type` = 'arc'" . ($moder ? "" : " AND `mod` = '0'")
		)->fetch();
	if ($arc !== FALSE) {
      

		/* Получаем имя родительского каталога */

		if ($arc['refid'] != 0) {
			$cat = $sql->query("SELECT `name` FROM `mod_lib` WHERE `id` = " . $arc['refid'])->fetch();
			if ($cat != FALSE) {
				$cat = htmlentities($cat['name'], ENT_QUOTES, 'UTF-8');
			}
		}
        $page = new page($total, $strs);
		/* Счетчик просмотров */
		$libView = isset($_SESSION['lib_view']) ? $_SESSION['lib_view'] : 0;
		if ($libView != $id) {
			$sql->query("UPDATE `mod_lib` SET `views` = '" . ($arc['views'] + 1) . "' WHERE `id` = " . $id);
			$_SESSION['lib_view'] = $id;
		}
		/* Счетчик уникальных просмотров */
		//$user_id = $user_id ? $user_id : md5(core::$ip . core::$ip_via_proxy . core::$user_agent);
		$unic = $sql->query("SELECT COUNT(*) FROM `mod_lib_counters` WHERE `uid` = '" . $user_id ."' AND `aid` = '" . $id . "' AND `type` = 0")->result();
		if (!$unic) {
			$sql->query("UPDATE `mod_lib` SET `uni_views` = '" . ($arc['uni_views'] + 1) . "' WHERE `id` = '" . $id . "'");
			$sql->query("INSERT INTO `mod_lib_counters` SET `aid` = '" . $id . "', `uid` = '" . $user_id . "'");
		}

		$text = text::output($arc['content']);

		$set['title'] = htmlspecialchars($arc['name']);
		/* Устанавливаем мета теги */
		$set['meta_keywords'] = htmlspecialchars($arc['tags']);
		$set['meta_description'] = htmlspecialchars($arc['announce']);

		require H . 'engine/includes/head.php';
		
		$cache = new cache(H . 'engine/files/tmp/lib[arc='.$id.';strs='.$strs.';start='.$start.'].swc');
		if (!$cache->life())
		{
		ob_start();
		echo '<div class="fmenu">' . 'Библиотека' . ' | ' . (!empty($cat) ? $cat . ' | ' : '') . $set['title'] . '</div>';
        if ($total > $start)
            $page->display('?act=articles&amp;mod=view&amp;id=' . $id . '&amp;strings=' . $strs . '&amp;', $strs);
		echo '<div class="post"><b>' . htmlentities($arc['name'], ENT_QUOTES, 'UTF-8');
       
		echo '</b><br />'. $text . '</div>';
        if ($total > $start)
            $page->display('?act=articles&amp;mod=view&amp;id=' . $id . '&amp;strings=' . $strs . '&amp;', $strs);
        echo '<div class="post">' .
		     (!empty($arc['tags']) ? '<b>Метки:</b>&#160;' . tagsToLinks($arc['tags']) . '<br />' : '') .
		     '<b>Автор:</b>&#160;'.Core::user_show(Core::get_user($arc['author_id']), array('status' => $arc['time'], 'is_time' => '1')) .
             (!empty($arc['edit_time']) ? 
             '<b>Изменено:</b>&#160;'.Core::user_show(Core::get_user($arc['author_id']), array('status' => $arc['edit_time'], 'is_time' => '1')): '' ) .
		     '<b>Просмотры:</b>&#160;' . $arc['views'] . '<br />' .
		     '<b>Уникальных просмотров:</b>&#160;' . $arc['uni_views'] . '<br />' .
		     '<b>Комментарии:</b>&#160;<a href="?act=comments&amp;id=' . $id . '">' . $sql->query('SELECT count(*) from `mod_lib_comments` where `sub_id` = '.$id)->result() . '</a><br />' .
		     '<b>Рейтинг</b>:&#160;' .
		     '<a href="?act=articles&amp;mod=vote&amp;id=' . $id . '&amp;do=1"><img src="' . ICONSDIR . 'minus.png" alt="-" /></a>&#160;' .
		     ($arc['rate_plus'] - $arc['rate_minus']) .
		     '&#160;<a href="?act=articles&amp;mod=vote&amp;id=' . $id . '&amp;do=2"><img src="' . ICONSDIR . 'plus.png" alt="+" /></a>&#160;' .
		     '(За: ' . $arc['rate_plus'] . ' | Против: ' . $arc['rate_minus'] . ')</div>';
		/* Прикрепленные файлы */
		$ftotal = $sql->query("SELECT COUNT(*) FROM `mod_lib_files` WHERE `aid` = '" . $id . "'")->result();
		if ($ftotal != 0) {
			echo '<div class="msg"><b>Прикрепленные&#160; файлы:</b><br />';
			$sql->query("SELECT `name` FROM `mod_lib_files` WHERE `aid` = " . $id);
			while($fdata = $sql->fetch()) {
				$fname = htmlentities($fdata['name'], ENT_QUOTES, 'UTF-8');
				echo '<a href="/lib/files/attach/' . $fname . '">' . $fname . '</a><br />';
			}
			echo '</div>';
		}
		echo '<div class="menu"><b>Символов на страницу:</b><br />' .
			 '<form action="?act=articles&amp;mod=view&amp;id=' . $id . '" method="post">' .
			 '<select name="strings">';
		$strings = range(500, 3000, 500);
		foreach ($strings as $val) {
			echo '<option value="' . $val . '"' . ($val == $strs ? ' selected="selected"' : '') . '>' . $val . '</option>';
		}
		echo '</select><input type="submit" name="submit" value="Сохранить" /></form></div><div class="menu">';
        $cache->write();
        }
        echo $cache->read();

		/* Добавление статьи в закладки / Удаление статьи из закладок */
		$bookmark = $sql->query(
			"SELECT COUNT(*) FROM `mod_lib_counters` " .
			"WHERE `aid` = '" . $id .
			"' AND `uid` = '" . $user_id .
			"' AND `type` = '2'"
		)->result();
		$bmLink = $bookmark
		        ? '<a href="?act=bookmarks&amp;mod=remove&amp;id=' . $id . '">Удалить из закладок</a>'
		        : '<a href="?act=bookmarks&amp;mod=add&amp;id=' . $id . '">Добавить в закладки</a>';
		echo '<a href="?act=' .
		     (isset($_GET['panel']) ? 'panel&amp;mod=view&amp;id=' . $arc['refid'] : 'category&amp;mod=view&amp;id=' . $arc['refid'])
		     . '">Назад</a><br /><a href="index.php">В библиотеку</a><br />' .
		     '<a href="?act=articles&amp;mod=download&amp;id=' . $id . '">Загрузить</a><br />' . $bmLink;
		if ($admin) {
			echo '<br /><a href="?act=articles&amp;mod=move&amp;id=' . $id . '">Переместить</a>' .
			     '<br /><a href="?act=articles&amp;mod=form&amp;id=' . $id . '&amp;do=edit">Редактировать</a>' .
			     '<br /><a href="?act=articles&amp;mod=remove&amp;id=' . $id . '">Удалить</a>';
		}
		echo '</div>';
	} else {
		$error[] = 'Статья не найдена';
	}
}
if (!empty($error)) {
	$set['title'] = 'Библиотека';
	require H . 'engine/includes/head.php';
}