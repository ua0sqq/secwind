<?php

$wheres = array('n' => 'name', 'a' => 'announce', 't' => 'text', 'm' => 'tags');
$where = !empty($_REQUEST['where']) && array_key_exists($_REQUEST['where'], $wheres) ? $_REQUEST['where'] : 'n';
$query = !empty($_GET['query']) ? urldecode($_GET['query']) : (!empty($_POST['query']) ? $_POST['query'] : '');
$query = trim($query);
echo '<div class="fmenu"><strong>' . 'Библиотека' . ' | Поиск</strong></div>'
     . '<div class="msg"><strong>Введите запрос:</strong> (4 - 64)<br />'
     . '<form action="?act=search&amp;mod=view&amp;search" method="post">'
	 . '<input type="text" name="query" value="' . htmlentities($query, ENT_QUOTES, 'UTF-8') . '" /><br />'
     . 'Где искать:<br />'
     . '<input type="radio" name="where" value="n"' . ($where == 'n' ? ' checked="checked"' : '') . ' />В заголовках<br />'
     . '<input type="radio" name="where" value="a"' . ($where == 'a' ? ' checked="checked"' : '') . ' />В анонсах<br />'
     . '<input type="radio" name="where" value="t"' . ($where == 't' ? ' checked="checked"' : '') . ' />В тексте<br />'
     . '<input type="radio" name="where" value="m"' . ($where == 'm' ? ' checked="checked"' : '') . ' />В метках<br />'
     . '<input type="submit" name="submit" value="Искать" /></form></div>';
if (!empty($query)) {
	echo '<div class="msg"><strong>Результат поиска:</strong></div>';
}
if ((!empty($query)) && (mb_strlen($query) < 4 || mb_strlen($query) > 64)) {
	echo '<div class="err">Длина запроса должна содержать от 4-х до 64-х символов</div>';
} else {
    Core::get('page.class', 'classes');
	$word = my_esc($query);
	$word = mb_strlen($word) < 4 ? ' ' . $word . ' ' : $word;
	$field = $wheres[$where];
	$total = $sql->query(
			"SELECT COUNT(*) FROM `mod_lib` " .
			"WHERE MATCH `" . $field . "` AGAINST ('" . $word . "' IN BOOLEAN MODE) > 0 " .
			"AND `type` = 'arc' AND `mod` = '0'")->result();
    $page = new page($total, $set['p_str']);
	if ($total) {
		$qArc = $sql->query(
			"SELECT `id`, `name`, `announce`, `tags`, `author_id`, `author_name`, `time`, `comm_count`, `views`, `uni_views`, "
			. "(`rate_plus` - `rate_minus`) as `rate_all`,  MATCH `" . $field . "` AGAINST ('" . $word . "') as `rel` "
			. "FROM `mod_lib` WHERE MATCH `" . $field . "` AGAINST ('" . $word . "' IN BOOLEAN MODE) > 0 AND `type` = 'arc' AND `mod` = '0' "
			. "ORDER BY `rel` LIMIT " . $page->limit());
		$i = 0;
		while ($arc = $sql->fetch()) {
			echo '<div class="' . ($i % 2 ? 'p_m' : 'p_t') . '">' . display_article($arc) . '</div>';
			$i++;
		}
        $page->display('?act=search&amp;mod=view&amp;where=' . $where . '&amp;query=' . urlencode($query) . '&amp;search&amp;');
	} elseif(isset($_GET['search'])) {
		echo '<div class="post">Список пуст</div>';
	}
}

echo '<p><a href="index.php">В библиотеку</a></p>';