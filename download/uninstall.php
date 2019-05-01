<?php
    include '../engine/includes/start.php';
	if (!$user_id)
	    Core::stop();
	
	$set['title']='Удаление модуля ЗЦ';
	
	include H.'engine/includes/head.php';

	if (!$creator)Core::stop();

	if (isset($_GET['yes']))
	{
		$sql->multi("DROP TABLE `down_comms`, `down_files`, `down_more`;DELETE FROM `modules` where `name` = 'download';DELETE FROM `module_services` where `belongs` = 'download';");
		unlink(H . 'engine/services/download_service.php');
		Core::get('delete_dir', 'functions');
		delete_dir(H . 'download');
		
		echo 'Модуль "Загруз-центр" удален';
	}
	else
		echo 'Вы действительно хотите удалить модуль "Загруз-центр"?<br /><a href="?yes">[  Да  ]</a> | <a href=".">[  Нет  ]</a>';

	include H.'engine/includes/foot.php';