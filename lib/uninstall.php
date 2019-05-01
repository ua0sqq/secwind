<?php
    include '../engine/includes/start.php';
	if (!$user_id)
	    Core::stop();
	
	$set['title']='Удаление модуля - Библиотека';
	
	include H.'engine/includes/head.php';

	if (!$creator)Core::stop();

	if (isset($_GET['yes']))
	{
		$sql->multi("DROP TABLE `mod_lib`, `mod_lib_comments`, `mod_lib_counters`, `mod_lib_files`, `mod_lib_set`;DELETE FROM `modules` where `name` = 'lib';DELETE FROM `module_services` where `belongs` = 'lib';");
		unlink(H . 'engine/services/lib_service.php');
		Core::get('delete_dir', 'functions');
		delete_dir(H . 'lib');
		echo 'Модуль "Библиотека" удален';
	}
	else
		echo 'Вы действительно хотите удалить модуль "Библиотека"?<br /><a href="?yes">[  Да  ]</a> | <a href=".">[  Нет  ]</a>';

	include H.'engine/includes/foot.php';