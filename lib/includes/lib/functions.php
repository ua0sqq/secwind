<?php

/**
* @author Screamer
*/



/**
 * Отображение статьи в списках
 * @param  (array)  $arc Массив с данными статьи
 * @return (string)
 */
function display_article($arc) {
	return '<img src="' . ICONSDIR . 'arc.png" alt="" />&#160;'
	         . '<a href="?act=articles&amp;mod=view&amp;id=' . $arc['id'] . '">'
	         . htmlentities($arc['name'], ENT_QUOTES, 'UTF-8') . '</a><br />'
	         . htmlentities($arc['announce'], ENT_QUOTES, 'UTF-8')
	         // Кто добавил (со ссылкой на профиль)
	         . '<div class="sub">Добавил '
	         . ':&#160;<a href="/pages/user.php?id=' . $arc['author_id'] . '">'
	         . htmlentities($arc['author_name'], ENT_QUOTES, 'UTF-8') . '</a>&#160;'
	         // Дата добавления
	         . '(' . Core::time($arc['time']) . ')<br />'
	         // Метки
	         . (!empty($arc['tags']) ? 'Метки:&#160;' . tagsToLinks($arc['tags']) . '<br />' : '')
	         // Комментарии
	         . '<img src="' . ICONSDIR . 'comm.png" alt="" />&#160;<a href="?act=comments&amp;id=' . $arc['id'] . '">' . $arc['comm_count'] . '</a>&#160;'
	         // Рейтинг
	         . '<img src="' . ICONSDIR . 'rate.png" alt="" />&#160;' . $arc['rate_all'] . '&#160;'
	         // Счетчики просмотров (Уникальных / Всего)
	         . '<img src="' . ICONSDIR . 'view.png" alt="" />&#160;' . ($arc['uni_views'] + $arc['views'])
			 . ' (' . $arc['uni_views'] . '/' . $arc['views'] . ')<br />'
	         . '</div>';
}

/**
 * Отображение категории в списках
 * @param  (array)  $cat   Массив с данными категории
 * @param  (string) $link  Ссылка на категорию (Пример: ?act=category&amp;mod=view&amp;id=)
 * @return (string)
 */
function display_category($cat, $link) {
	$message = '<img src="' . ICONSDIR . ($cat['mod'] == 1 ? 'u' : '') . 'cat.png" alt="" />'
	         . '&#160;<a href="' . $link . $cat['id'] . '">'
			 . htmlentities($cat['name'], ENT_QUOTES, 'UTF-8')
			 . '</a> [' . $cat['counter'] . '/' . $cat['count_arc'] . ']'
			 . (!empty($cat['announce']) ? '<div class="sub">' . htmlentities($cat['announce'], ENT_QUOTES, 'UTF-8') . '</div>' : '');
	return $message;
}

/**
* Добавляет ссылки к меткам
* @param (string) $tags метки
* @return (string)
*/
function tagsToLinks($tags = '')
{
	if (empty($tags)) {
		return '';
	}
	$tags = explode(',', $tags);
	$tags = array_map('trim', $tags);
	$new = '';
	foreach ($tags as $tag) {
		$new .= '<a href="?act=search&amp;mod=view&amp;where=m&amp;query=' . urlencode($tag) . '&amp;search">' .
		     htmlentities($tag, ENT_QUOTES, 'UTF-8') . '</a>, ';
	}
	return trim($new, ', ');
}

/**
* Проверяет является ли файл изображением
* @param (string) $type Расширение или MIME-тип файла
* @return (boolean)
*/
function isImage($type = '')
{
	$array = array('png', 'jpg', 'jpeg', 'gif', 'image/gif', 'image/jpeg', 'image/pjpeg', 'image/png');
	return in_array($type, $array);
}

/**
* Поиск имени файла для удаления (используется при добавлении/редактировании статьи)
* return (boolean)
*/
function searchWordHelper($name)
{
	preg_match('/delfile_(.*)/', $name, $matches);
	return isset($matches[1]) ? $matches[1] : FALSE;
}

/**
* Поиск имени файла для удаления (используется при добавлении/редактировании статьи)
* return (mixed)
*/
function searchWord($array)
{
	$return = array_map('searchWordHelper', $array);
	foreach ($return as $value) {
		if ($value !== FALSE) {
			return $value;
		}
	}
	return FALSE;
}

/**
* Обработка BB-кода изображений при просмотре статьи
* @param (string) $name Имя файла
* @return (string)
*/
function findImage($name)
{
	$name = explode('.', $name[1]);
	if (file_exists(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $name[0] . '.' . $name[1])) {
		$url = 'files/attach/' . $name[0] . '.' . $name[1];
		if (file_exists(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $name[0] . '_preview.png')) {
			$preview = 'files/attach/' . $name[0] . '_preview.png';
		} else {
			$preview = $url;
		}
		return '<p><a href="' . $url . '"><img src="' . $preview . '" alt="image" style="float:center" /></a></p>';
	}
	return '[изображение не найдено]';
}

/**
* Обработка BB-кода изображений при скачивании статьи
* @param (string) $name Имя файла
* @return (string)
*/
function replaceImage($name)
{
	$name = explode('.', $name[1]);
	if (file_exists(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $name[0] . '.' . $name[1])) {
		$url = 'files/' . $name[0] . '.' . $name[1];
		if (file_exists(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $name[0] . '_preview.png')) {
			$preview = 'files/' . $name[0] . '_preview.png';
		} else {
			$preview = $url;
		}
		return '<p><a href="' . $url . '"><img src="' . $preview . '" alt="image" style="float:center" /></a></p>';
	}
	return '[изображение не найдено]';
}

/**
* Генерирует список меток для главной страницы модуля
* @param (int) $storageTime Время хранения кэша списка
* @return (string)
*/
function topTags($storageTime)
{
	global $sql;
	$storageTime = abs(intval($storageTime));
	$createCache = FALSE;
	$file = FILESDIR . 'cache' . DIRECTORY_SEPARATOR . 'tags.dat';
	if (file_exists($file)) {
		$data = unserialize(file_get_contents($file));
		$createTime = array_pop($data);
		if (time() > $createTime + $storageTime) {
			// Время хранения кэша истекло
			$createCache = TRUE;
		} else {
			$tags = $data[0];
		}
	} else {
		// Кэш не существует
		$createCache = TRUE;
	}
	// Создаем кэш
	if($createCache === TRUE) {
		$query = $sql->query("SELECT `tags` FROM `mod_lib` WHERE `tags` != '' ORDER BY `time` DESC LIMIT 20");
		$tags = '';
		while ($get = $sql->fetch()) {
			$tags .= $get['tags'] . ',';
		}
		$tags = tagsToLinks(rtrim($tags, ','));
		file_put_contents($file, serialize(array($tags, time())));
	}
	return $tags;
}