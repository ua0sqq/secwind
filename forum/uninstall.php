<?php
    include '../engine/includes/start.php';
	if (!$user_id)
	    Core::stop();
	
	$set['title']='Удаление модуля - форум';
	
	include H.'engine/includes/head.php';

	if (!$creator)Core::stop();

	if (isset($_GET['yes']))
	{
		$sql->multi("DROP TABLE `forum_favourites`, `forum_files`, `forum_forums`, `forum_journal`, `forum_polled`, `forum_polls`, `forum_posts`, `forum_posts_del`, `forum_posts_rating`, `forum_readed`, `forum_topics`;
		DELETE FROM `modules` where `name` = 'forum';DELETE FROM `module_services` where `belongs` = 'forum';");
		unlink(H . 'engine/services/forum_service.php');
		Core::get('delete_dir', 'functions');
		delete_dir(H . 'forum');
		echo 'Модуль "Форум" удален';
	}
	else
		echo 'Вы действительно хотите удалить модуль "Форум"?<br /><a href="?yes">[  Да  ]</a> | <a href=".">[  Нет  ]</a>';

	include H.'engine/includes/foot.php';