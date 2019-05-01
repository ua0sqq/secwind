<?php


if ($admin) {
	echo '<div class="fmenu">' . 'Библиотека' . ' | Панель управления</div>';
	if ($id) {
		$cat = $sql->query("SELECT `refid`, `name`, `mod` FROM `mod_lib` WHERE `id` = " . $id . " AND `type` = 'cat'")->fetch();
		if ($cat === FALSE) {
			/* Категория не найдена */
			$err = 'Категория не найдена</div>';
		}
	} else {
		$id = 0;
		$arcsMod = $sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `type` = 'arc' AND `mod` = '1'")->result();
		echo '<div class="msg"><p><img src="' . ICONSDIR .'moderation.png" alt="" style="float:left" />&#160;'
		     . '<a href="?act=panel&amp;mod=moderation">Статьи на модерации</a> [' . $arcsMod . ']</p>'
		     . ( $admin
		         ? '<p><img src="' . ICONSDIR . 'settings.png" alt="" style="float:left" />&#160;'
		           . '<a href="?act=panel&amp;mod=settings">Настройки</a></p>'
		           . '<p><img src="' . ICONSDIR . 'garbage.png" alt="" style="float: left" />&#160;'
		           . '<a href="?act=panel&amp;mod=garbage">Сборщик мусора</a></p>'
		         : '')
		     . '</div>';
	}
	if (!isset($err)) {
		if (isset($cat)) {
			/* Ссылка для перехода на категорию выше */
			$ref = $sql->query("SELECT `name` FROM `mod_lib` WHERE `id` = " . $cat['refid'] . " AND `type` = 'cat'")->result();
			if ($ref === FALSE)  {
				$ref['name'] = 'Панель управления';
			}
			echo '<div class="msg"><a href="?act=panel&amp;mod=view&amp;id=' . $cat['refid'] . '">' . $ref['name'] . '</a></div>';
			unset($ref);
		}
        Core::get('page.class', 'classes');
		$total_cat = intval($sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `refid` = " . $id . " AND `type` = 'cat'")->result());
		$total_arc = intval($sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `refid` = " . $id . " AND `type` = 'arc'  AND `mod` = '0'")->result());
		$total = $total_arc + $total_cat;
        $page = new page($total, $set['p_str']);
		/* Список категорий и статей */
		if ($total > 0) {
			echo '<form action="?act=panel&amp;mod=movdel&amp;id=' . $id . '" method="post">';
			$query = $sql->query(
				"SELECT `id`, `refid`, `name`, `announce`, `counter`, `count_arc`, `type`, `tags`, `time`, `author_id`, `author_name`, " .
				"(`rate_plus` - `rate_minus`) as `rate_all`, `comm_count`, `views`, `uni_views`, `mod` " .
				"FROM `mod_lib` WHERE `refid` = " . $id . " " .
				"AND (`type` = 'cat' OR (`type` = 'arc' AND `mod` = '0')) ORDER BY `type` DESC, `name` ASC LIMIT " . $page->limit()
			);
			$i = 0;
			while($content = $sql->fetch()) {
				echo '<div class="' . ($i % 2 ? 'p_m' : 'p_t') . '">'
				. '<input type="checkbox" name="data[]" value="' . $content['id'] . '" />&#160;'
				. ($content['type'] == 'arc'
				   ? display_article($content)
				   : display_category($content, '?act=panel&amp;mod=view&amp;id=')
				  )
				. '</div>';
				$i++;
			}
			echo '<div class="menu"><input type="submit" name="move" value="Переместить" /> <input type="submit" name="delete" value="Удалить" /></div></form>';
		} else {
			echo '<div class="menu">Список пуст</div>';
		}
	} else {
		echo '<div class="err">' . $err . '</div>';
	}
	echo '<div class="fmenu"><a href="index.php">В библиотеку</a></div>';
	$page->display('?act=panel&amp;mod=view&amp;id=' . $id . '&amp;');

} else {
	$error = 'Доступ запрещен';
}