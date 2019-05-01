<?php
	include '../engine/includes/start.php';
	$set['title']='Администрация';
	include incDir . 'head.php';

	$k_post = $sql->query('SELECT * FROM `user` WHERE `group_access` > 1')->num_rows();

	if ($k_post == 0)
	{
		echo 'Список пуст';
	}

	while ($ank = $sql->fetch())
	{
		echo '<div class="p_m">'.Core::user_show($ank).'</div>';
	}

	?>
	<a href="online.php" class="link">Онлайн</a>
	<a href="users.php" class="link">Все пользователи</a>
	<a href="/" class="link">Главная</a>
	<?php
	include incDir . 'foot.php';