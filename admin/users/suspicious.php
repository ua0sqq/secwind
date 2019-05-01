<?php
	include '../../engine/includes/start.php';
	
	if (!$creator)
		Core::stop('/?');

	$set['title'] = 'Подозрительные пользователи';
	include incDir . 'head.php';

	switch($act)
	{
		default:
			$type = 'Подозрительный ник';
				break;

		case 'password':
			$type = 'Простой пароль';
				break;

		case 'other':
			$type = 'Множественая регистрация';
				break;
	}

	Core::get('page.class');

	$total = $sql->query('SELECT count(*) FROM `suspicious_users` WHERE `name` = "'.$type.'"')->result();
	$page = new page($total, $set['p_str']);
	$sql->query('SELECT `text` FROM `suspicious_users` WHERE `name` = "'.$type.'" ORDER BY `id` DESC LIMIT '.$page->limit());
	$i = 0;

	?>
		<style>
		td a{display:block}
		</style>

		<div class="post">Сортировка</div>
		<table width="100%"><tr>
		<td class="<?=$type == 'Подозрительный ник' ? 'p_m' : 'p_t'?>"><a href="?act=nick">Ник</a></td>
		<td class="<?=$type == 'Простой пароль' ? 'p_m' : 'p_t'?>"><a href="?act=password">Пароль</a></td>
		<td class="<?=$type == 'Множественая регистрация' ? 'p_m' : 'p_t'?>"><a href="?act=other">Другое</a></td>
		</tr></table>
	<?php

	$page->display('?act='.$act.'&amp;');

	while($msg = $sql->result())
	{
		echo '<div class="'.($i++ % 2 ? 'p_m' : 'p_t').'">'.$msg.'</div>';
	}

	$page->display('?act='.$act.'&amp;');

	echo '
		<a href="/admin/?act=users"><div class="link">Пользователи</div></a>
		<a href="/admin/"><div class="link">Админка</div></a>';

	include incDir . 'foot.php';
