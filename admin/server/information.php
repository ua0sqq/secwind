<?php

	include '../../engine/includes/start.php';

	if (!$creator)
        Core::stop();
	
	$set['title'] = 'Информация о сервере';

	include incDir . 'head.php';

	/*
	* Часть кода от Casper, часть от venom 
	*/

	function inival($var)
	{
		static $arr;
		if (!isset($arr[$var]))
		{
			$arr[$var] = ini_get($var);
		}
		
		if (empty($arr[$var]))
		{
			return 'Выключен';
		}
		else
		{
			if (2 > $arr[$var])
			{
				return 'Включен';
			}
			else
			{
				return $arr[$var];
			}
		}
	}

	$gd = gd_info();

	$dis_funcs = (strlen(ini_get('disable_functions')) > 1) ? str_replace (',', ', ', ini_get('disable_functions')) : 'Нет';

	if(function_exists('apache_get_modules'))
	{
		if (array_search('mod_rewrite', apache_get_modules()))
			$mod_rewrite = '<font color="#009900">ON</font>';
		else
			$mod_rewrite = '<font color="#990000">OFF</font>';
	}
	else
		$mod_rewrite = ' n/a ';

	echo 
		'<div class="menu_razd">Информация о сервере</div>'.
		'<div class="link">Время сервера: '.date('H:i, j.m.Y').'</div>'.
		'<div class="link">PHP VERSION: '.PHP_VERSION.' ('.PHP_OS.') </div>'.
		'<div class="link">ZEND ENGINE: '.Zend_Version().' </div>' .
		'<div class="link">Mod Rewrite: '.$mod_rewrite.'</div>';

	if (isset($_GET['exts']))
	{
		$exts = get_loaded_extensions();
		$extensions = NULL;
		foreach ($exts as $ext)
		{
			$extensions .= ' '.$ext.', ';
		}
		$extensions = substr($extensions, 0, -1);

		echo '<a href="?"><div class="link">Расширения: '. $extensions . '</div></a>';
	}
	else
	{
		echo '<a href="?exts"><div class="link">Расширения: развернуть</div></a>';
	}

	echo
		'<div class="menu_razd">Дополнительные сведения</div>'.
		'<div class="link">Safe Mode: '. inival('safe_mode') . '</div>'.
		'<div class="link">Allow url fopen: '. inival('allow_url_fopen') .'</div>'.
		'<div class="link">Register globals: '. inival('register_globals') .'</div>'.
		'<div class="link">Allow url include: '. inival('allow_url_include') . '</div>' . 
		'<div class="link">Выключенные функции: '. $dis_funcs . '</div>' . 
		'<div class="menu_razd">Конфигурация</div>' . 
		'<div class="link">Версия GD: '.$gd['GD Version'].'</div>'.
		'<div class="link">Версия PHP: '.phpversion().'</div>'.
		'<div class="link">Bepcия Zend engine: '.zend_version().'</div>'.
		'<div class="link">Вывод ошибок сайта: '.inival('display_errors') . '</div>'.
		'<div class="menu_razd">Конфигурация квоты</div>'.
		'<div class="link">Лимит вpeмени выпoлнeния: '. inival('max_execution_time') . ' ceк.</div>'.
		'<div class="link">Лимит оперативной памяти: '. inival('memory_limit') . '</div>'.
		'<div class="link">Возможность зaгpузки фaйлoв: '. inival('file_uploads') . '</div>'.
		'<div class="link">Лимит paзмepа зaгpужaeмoгo фaйлa: '. inival('upload_max_filesize'). '</div>';
	
	echo '
		<div class="menu_razd">См. также</div>
		<div class="link"><a href="..?act=server">Сервер</a></div>
		<div class="link"><a href="..">Админка</a></div>';

	include incDir . 'foot.php';