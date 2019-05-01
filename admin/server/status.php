<?php

	include '../../engine/includes/start.php';

	if (!$creator)
        Core::stop();

	
	$set['title'] = 'Информация о SecWind';

	include incDir . 'head.php';

	Core::get(array('text.class', 'files.class', 'cache.class'));

	switch($act)
	{
		case 'db':
			$sql->query('truncate table `errors`');
			$sql->query('truncate table `suspicious_users`');
				break;

		case 'cache':
			Cache::multi_delete('swc', tmpDir);
			Cache::multi_delete('db', H . 'engine/files/users/cache/');
				break;

		case 'backup':
			Cache::multi_delete('zip', H . 'engine/files/backup/files/');
			Cache::multi_delete('sql', H . 'engine/files/backup/mysql/');
				break;
	}

	$user_dir = new files(H . 'engine/files/users/cache/', 'get_data');
	$tmp_dir = new files(H . 'engine/files/tmp/', 'get_data');
	$fbu = new files(H . 'engine/files/backup/files/', 'get_data');
	$sqlbu = new files(H . 'engine/files/backup/mysql/', 'get_data');
	$mod_arch = new files(H . 'engine/files/modules/archives/', 'get_data');
	$mod_conf = new files(H . 'engine/files/modules/configs/', 'get_data');
	$mod_icons = new files(H . 'style/icons/modules/', 'get_data');
	$themes = new files(H . 'style/themes/', 'get_data');
	$avatars = new files(H . 'style/themes/', 'get_data');

	$dbsize = $dbrows = $tbl_error = $suspic_user = 0;

	$sql->query('SHOW TABLE STATUS'); 
	while($row = $sql->fetch())
	{
		$dbsize += $row['Data_length'] + $row['Index_length'];
		$dbrows += $row['Rows'];
	}

	$row = $sql->query('SHOW TABLE STATUS LIKE "errors"')->fetch(); 
	$tbl_error = $row['Data_length'] + $row['Index_length'];
	$tbl_error_rows = $row['Rows'];

	$row = $sql->query('SHOW TABLE STATUS LIKE "suspicious_users"')->fetch(); 
	$suspic_user = $row['Data_length'] + $row['Index_length'];
	$suspic_user_rows = $row['Rows'];
	
	echo '
		<div class="menu_razd">База данных</div>
		<div class="news">
		Общий размер: <b>' . text::size_data($dbsize) . '</b> (' . $dbrows . ' строк)<br />
		Таблица ошибок: <b>' . text::size_data($tbl_error) . '</b> (' . $tbl_error_rows . ' строк)<br />
		Таблица подозрительных юзеров: <b>' . text::size_data($suspic_user) . '</b> (' . $suspic_user_rows . ' строк)<br />
		&rarr; <a href="?act=db">Очистить мусорные таблицы</a>
		</div>
		<div class="menu_razd">Кеш</div>
		<div class="news">
		Общий размер: <b>' . text::size_data($user_dir->size + $tmp_dir->size) . '</b><br />
		Кеш пользователей: <b>' . text::size_data($user_dir->size) . '</b> (' . $user_dir->files . ' файл.) <br />
		Кеш остального: <b>' . text::size_data($tmp_dir->size) . '</b> (' . $tmp_dir->files . ' файл.)<br />
		&rarr; <a href="?act=cache">Очистить кеш</a>
		</div>
		<div class="menu_razd">Бекапы</div>
		<div class="news">
		Общий размер: <b>' . text::size_data($fbu->size + $sqlbu->size) . '</b><br />
		Бекап файлов: <b>' . text::size_data($fbu->size) . '</b> (' . $fbu->files . ' файл.) <br />
		Бекап базы данных: <b>' . text::size_data($sqlbu->size) . '</b> (' . $sqlbu->files . ' файл.)<br />
		&rarr; <a href="?act=backup">Удалить все бекапы</a></div>
		<div class="menu_razd">Неустановленные модули</div>
		<div class="news">
		Общий размер: <b>' . text::size_data($mod_arch->size + $mod_conf->size + $mod_icons->size) . '</b><br />
		Архивы: <b>' . text::size_data($mod_arch->size) . '</b> (' . $mod_arch->files . ' файл.)  <br />
		Конфиг-файлы: <b>' . text::size_data($mod_conf->size) . '</b> (' . $mod_conf->files . ' файл.) <br />
		Иконки: <b> ' . text::size_data($mod_icons->size) . '</b> (' . $mod_icons->files . ' файл.) </div>
		<div class="menu_razd">Прочее</div>
		<div class="news">
		Темы: <b>' . text::size_data($themes->size) . '</b><br />
		Аватары: <b>' . text::size_data($avatars->size) . '</b></div>';
	
	echo '
		<div class="menu_razd">См. также</div>
		<div class="link"><a href="..?act=server">Сервер</a></div>
		<div class="link"><a href="..">Админка</a></div>';

	unset($user_dir, $tmp_dir, $fbu, $sqlbu, $mod_arch, $mod_conf, $mod_icons, $themes, $avatars, $dbsize, $dbrows, $tbl_error, $suspic_user, $suspic_user_rows, $tbl_error_rows, $row);

	include incDir . 'foot.php';