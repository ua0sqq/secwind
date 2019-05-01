<?php
	include '../../engine/includes/start.php';
	
	if (!$admin)
		Core::stop('/?');

	$set['title'] = 'Удаление пользователя';
	include incDir . 'head.php';

	if ($act == 'new' && isset($_SESSION['edit_user_id']))
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
		<div class="menu_razd">Удаление пользователя</div>
		<form method="post" action="?">
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
		echo '<div class="post">Удаление пользователя '.$editor['nick'].', <a href="?act=new">изменить</a></div>';

		if (isset($_POST['delete']))
		{
			if (function_exists('set_time_limit'))
				@set_time_limit(600);

			$service = mysqli_query($sql->db, 'select `file` from `module_services` where `use_in`="delete_user"');
			while($file = $sql->result($service))
			{
				include_once H . $file;
			}

			$sql->query("DELETE FROM `user` WHERE `id` = '$editor[id]' LIMIT 1");

			Core::msg_show('Все данные о пользователе '.$editor['nick'].' удалены');
			unset($_SESSION['edit_user_id'], $editor);
		}
		else
		{
			$service = mysqli_query($sql->db, 'select `file` from `module_services` where `use_in`="pre_delete_user"');
			while($file = $sql->result($service))
			{
				include_once H . $file;
			}
			?>
			Подтвердите факт удаления<br />
			<form method="post">
			<input type="submit" name="delete" value="Удалить"/>
			</form>
			Удаленные данные невозможно будет восстановить
			<?php
		}
	}

	if ($creator)
	{
		echo '
		<a href="mass_delete.php"><div class="link">Массовое удаление пользователей</div></a>
		<a href="/admin/?act=users"><div class="link">Пользователи</div></a>
		<a href="/admin/"><div class="link">Админка</div></a>';
	}
	include incDir . 'foot.php';
