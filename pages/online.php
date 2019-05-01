<?php
	include '../engine/includes/start.php';
	
	$set['title'] = 'Список пользователей в онлайне';
	
	include H.'engine/includes/head.php';
	
	Core::get('page.class');

	$k_post = $sql->query('SELECT COUNT(*) FROM `user` WHERE `date_last` > '.(time()-600))->result();
    $page = new page($k_post, $set['p_str']);

	if ($k_post > 0)
	{
		$sql->query("SELECT * FROM `user` WHERE `date_last` > '".(time()-300)."' LIMIT ".$page->limit());
		while($users = $sql->fetch())
		{
			echo '<div class="link">'.Core::user_show($users, array('status' => 'Был '.(($sec = time() - $users['date_last']) < 1 ? 1 : $sec) . ' сек. назад'));
			if ($admin)
			{
				if (!empty($users['ip']))
					echo 'IP: '. long2ip($users['ip']) .'<br />';
					
				if (!empty($users['ua']))
					echo 'UA:' . $users['ua'] .'<br />';
			}
			echo '</div>';
		}
		$page->display('?');
	}
	else
		Core::msg_show('Пользователей нет в онлайне');

	unset($k_post, $page, $users, $sec);
	$sql->free();

	echo '<a href="administration.php" class="link">Администрация</a><a href="users.php" class="link">Все пользователи</a><a href="/" class="link">Главная</a>';
	
	include incDir . 'foot.php';