<?php
	include '../../engine/includes/start.php';

	if (!$moder)
        Core::stop();

    $set['title'] = 'Забаненные юзеры';

    include incDir.'head.php';

	?>
		<table width="100%"><tr>

		<td class="<?=$act == 'list' ? 'p_t' : 'p_m'?>"><a href="?act=list" style="display:block">Список</a></td>

		<td class="<?=$act == 'ban' ? 'p_t' : 'p_m'?>"><a href="?act=ban" style="display:block">Забанить</a></td>

		</tr></table>
	<?php

	switch($act)
	{
		case 'unset':
		if ($sql->query('SELECT COUNT(*) FROM `ban` WHERE `user_id` = '.intval($_GET['user_id']).' AND `id` = '.$id)->result())
		{
			$sql->query('UPDATE `ban` SET `time` = '.$time.' WHERE `id` = '.$id.' LIMIT 1');
			Core::msg_show('Время бана обнулено');
		}
		break;

		case 'ban':

		if (isset($_GET['new'], $_SESSION['edit_user_id']))
		{
			unset($_SESSION['edit_user_id']);
		}

		if (isset($_POST['user']) || $id)
		{
			$_SESSION['edit_user_id'] = 
				$sql->query('SELECT `id` FROM `user` WHERE `'.(Core::form('order') == 'id' || $id ? 'id' : 'nick').'` = "'.($id ? $id : Core::form('user')).'" LIMIT 1')->result();
			if ($_SESSION['edit_user_id'] == 0 || $_SESSION['edit_user_id'] == $user_id)
			{
				unset($_SESSION['edit_user_id']);
				Core::msg_show('Поиск не дал результатов', 'error');
				//Include incDir . 'foot.php';
			}
		}

		if (!isset($_SESSION['edit_user_id']))
		{
			?>
			<div class="menu_razd">Забанить пользователя</div>
			<form method="post" action="?act=ban">
			Искать пользователя по:<br />
			<select name="order">
				<option value="id">Id</option>
				<option value="nick">Ник</option>
			</select><br />
			Данные для поиска:<br />
			<input type="text" name="user" value=""/><br />
			<input type="submit" value="Искать"/>
			</form>
			<?php
		}
		else
		{
			$editor = Core::get_user($_SESSION['edit_user_id']);
			echo '<div class="post">Забанить пользователя '.$editor['nick'].', <a href="?act=ban&amp;new">изменить</a></div>';

			if (isset($_POST['ban_pr'], $_POST['time'], $_POST['vremja']))
			{
				$timeban = $time;

				if ($_POST['vremja']=='min')$timeban+=intval($_POST['time'])*60;
				elseif ($_POST['vremja']=='chas')$timeban+=intval($_POST['time'])*60*60;
				elseif ($_POST['vremja']=='sut')$timeban+=intval($_POST['time'])*60*60*24;
				elseif ($_POST['vremja']=='mes')$timeban+=intval($_POST['time'])*60*60*24*30;

				if ($timeban < $time) $err[]='Ошибка времени бана';

				$prich = Core::form('ban_pr');
				$pr_lenght = mb_strlen($prich);
			
				if ($pr_lenght > 1024)
				{
					$err[]='Сообщение слишком длинное';
				}
	
				if ($pr_lenght < 10)
				{
					$err[]='Необходимо подробнее указать причину';
				}

				if (!isset($err))
				{
					$sql->query("INSERT INTO `ban` (`user_id`, `moder_id`, `prich`, `time`) VALUES ('$editor[id]', '$user[id]', '$prich', '$timeban')");
					Core::msg_show('Пользователь успешно забанен', 'msg');
				}
				else
					Core::msg_show($err);
			}

			?>
			<form action="?act=ban" method="post">
				Причина:<br />
				<textarea name="ban_pr"></textarea><br />
				Время бана:<br />
				<input type='text' name='time' title='Время бана' value='10' maxlength='11' size='3' />
				<select class='form' name="vremja">
					<option value='min'>Минуты</option>
					<option value='chas'>Часы</option>
					<option value='sut'>Сутки</option>
					<option value='mes'>Месяцы</option>
				</select><br />
				<input type='submit' value='Забанить' />
			</form>
			<?php
		}
		break;

		default:

		Core::get(array('page.class', 'text.class'));
		$total = $sql->query("SELECT COUNT(*) FROM `ban`  WHERE `time` > " . $time)->result();
		$page = new page($total, $set['p_str']);
		$i = 0;

		if ($total == 0)
		{
			echo '<div class="p_t">Нет нарушений</div>';
		}

		$sql->query('SELECT 
			`ban`.`id` as `ban_id`, `ban`.`time`, `ban`.`prich`,
			`user`.`id`, `user`.`nick`, `user`.`pol` 
			FROM `ban` LEFT JOIN `user`
			ON `ban`.`user_id` = `user`.`id` 
			WHERE `time` > '.$time.'
			ORDER BY `time` DESC LIMIT '.$page->limit());

		while ($post = $sql->fetch())
		{
			echo '<div class="'.($i++ % 2 ? 'p_m' : 'p_t').'"> '. 
				Core::user_show($post, array(
				'status' => 'до ' . Core::time($post['time']), 
				'post' => text::output($post['prich']).'<br /><a href="?act=unset&amp;id='.$post['ban_id'].'&amp;user_id='.$post['id'].'">Снять бан</a>')) . '</div>';
		}

		$page->display('?');
		break;
	}
	
	if ($creator)
	{
		echo '
		<a href="/admin/?act=users"><div class="link">Пользователи</div></a>
		<a href="/admin/"><div class="link">Админка</div></a>';
	}
	include incDir . 'foot.php';