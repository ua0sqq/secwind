<?php
	include '../engine/includes/start.php';
	$set['title']='Пользователи'; // заголовок страницы
	include incDir . 'head.php';

	Core::get('page.class');
	$total = $sql->query('SELECT COUNT(*) FROM `user`')->result();
	$page = new page($total, $set['p_str']);
	if (!$total)
	{
		echo 'Нет результатов';
	}

	$sql->query('SELECT `id`, `nick`, `date_reg`, `date_last`, `pol` FROM `user` LIMIT '.$page->limit());
	while ($ank = $sql->fetch())
	{
		echo '<div class="link">'.Core::user_show($ank, array('post' => '<span class="status">Регистрация:</span> '.Core::time($ank['date_reg']).'<br />
			<span class="status">Посл. посещение:</span> '.Core::time($ank['date_last']))).'</div>';
	}

	$page->display('?');
	unset($total, $page, $ank);
	$sql->free();

	?>
	<a href="administration.php" class="link">Администрация</a>
	<a href="online.php"><div class="link">Онлайн</div></a>
	<a href="/"><div class="link">Главная</div></a>
	<?php
	include incDir . 'foot.php';