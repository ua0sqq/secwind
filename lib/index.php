<?php

/**
* @author Screamer
*/

include '../engine/includes/start.php';

define('INCDIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR);

define('FILESDIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR);

define('ICONSDIR', '/lib/files/icons/');

require INCDIR . 'lib' . DIRECTORY_SEPARATOR . 'functions.php';

$do = isset($_GET['do']) ? $_GET['do'] : false;
$libSet = unserialize($sql->query("SELECT `val` FROM `mod_lib_set` WHERE `key` = 'set'")->result());

/* Закрытие доступа */
if (($libSet['mod_close'] == 1 && !$user_id) || ($libSet['mod_close'] == 2 && !$admin)) {
	$set['title'] = 'Библиотека';
	require H . 'engine/includes/head.php';
	echo Core::msg_show('Доступ запрещен');
	require H . 'engine/includes/foot.php';
}

/* Список допустимых режимов работы и заголовков страниц для них */
$modes = array(
	/* Статьи */
	'articles'       => array(
		'attach'     => 'Прикрепить &#160; файл',
		'download'   => 'Скачать статью',
		'edit'       => 'Редактировать статью',
		'form'       => ($do == 'add' ? 'Добавить статью' : 'Редактировать статью'),
		'save'       => ($do == 'add' ? 'Добавить статью' : 'Редактировать статью'),
		'remove'     => 'Удалить статью',
		'move'       => 'Переместить статью',
		'upload'     => 'Загрузить статью',
		'vote'       => 'Опрос',
		'view'       => ''
	),
	/* Закладки */
	'bookmarks'      => array(
		'add'        => 'Добавить в закладки',
		'clean'      => 'Удаление закладок',
		'remove'     => 'Удалить из закладок',
		'view'       => 'Мои закладки',
	),
	/* Категории */
	'category'       => array(
		'add'        => 'Добавить категорию',
		'edit'       => 'Редактировать категорию',
		'remove'     => 'Удалить категорию',
		'move'       => 'Переместить категорию',
		'view'       => ''
	),
	/* Комментарии */
	'comments'       => 'Комментарии',
	/* Главная страница */
	'mainpage'       => array(
		'view'       => 'Библиотека'
	),
	/* Панель управления */
	'panel'          => array(
		'garbage'    => 'Сборщик мусора',
		'view'       => 'Панель управления',
		'moderation' => 'Статьи на модерации',
		'settings'   => 'Настройки',
		'movdel'     => (isset($_REQUEST['move']) ? 'Переместить статьи и категории' : 'Удалить статьи и категории')
	),
	/* Поиск */
	'search'         => array(
		'view'       => 'Настройки'
	)
);

/* Если идет обращение к директорию с файлами */
$folders = array('files');
$uri = explode('/', $_SERVER['REQUEST_URI']);

if (!in_array($uri[1], $folders)) {
	/* Устанавливаем режим по умолчанию */
	$act = !empty($act) ? $act : 'mainpage';
	$mod = !empty($_GET['mod']) ? $_GET['mod'] : 'view';
	$loading = array_key_exists($act, $modes);
	if ($loading) {
		if (is_array($modes[$act])) {
			$loading = array_key_exists($mod, $modes[$act]);
		} else {
			$loading = is_string($modes[$act]);
		}
	}
	if ($loading) {
		if (is_string($modes[$act])) {
			$mode = INCDIR . $act . '.php';
			/* Устанавливаем заголовок страницы */
			$set['title'] = $modes[$act];
		} else {
			$mode = INCDIR . $act . DIRECTORY_SEPARATOR . $mod . '.php';
			/* Устанавливаем заголовок страницы */
			$set['title'] = $modes[$act][$mod];
		}
		if (file_exists($mode)) {
			if (!empty($set['title'])) {
				require H . 'engine/includes/head.php';
			}
			require $mode;
			/* Показ ошибок */
			if (!empty($error)) {
				echo '<div class="fmenu">Библиотека</div>'
				     . Core::msg_show($error)
				     . '<div class="fmenu"><a href="index.php">Назад</a></div>';
			}
				require_once H . 'engine/includes/foot.php';

		} else {
			$error = TRUE;
		}
	} else {
		$error = TRUE;
	}
	/* Если запрашиваемый режим не найден, перенаправляем на страницу ошибки */
	if (isset($error) && $error === TRUE) {
		header('Location: /?err');
		exit;
	}
}