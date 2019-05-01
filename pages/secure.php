<?php
	include '../engine/includes/start.php';

	if (!$user_id)
		Core::stop();

	$set['title']='Безопасность';
	include incDir . 'head.php';

	if (isset($_POST['old_pass']))
	{
		if ($sql->query("SELECT COUNT(*) FROM `user` WHERE `id` = $user[id] AND `pass` = '".md5($user_id . $_POST['old_pass'])."' LIMIT 1")->result() == 1)
		{
			if ($_POST['new_pass'] == $_POST['confirm_pass'])
			{
				if (mb_strlen($_POST['new_pass']) < 6)
					$err = 'По соображениям безопасности новый пароль не может быть короче 6-ти символов';
			}
			else
				$err = 'Новый пароль не совпадает с подтверждением';
		}
		else
			$err = 'Старый пароль неверен';

		if (isset($err))
		{
			Core::msg_show($err);
		}
		else
		{
			$user['pass'] = md5($user_id . $_POST['new_pass']);
			$sql->query("UPDATE `user` SET `pass` = '".$user['pass']."' WHERE `id` = '$user[id]' LIMIT 1");
			setcookie('pass', $user['pass'], time()+60*60*24*365);
			Core::msg_show('Пароль успешно изменен', 'menu_razd');
			echo 'Ссылка на автологин: <br /><input type="text" value="http://'.$_SERVER['HTTP_HOST'].'/login.php?id='.$user_id.'&amp;pass='.htmlspecialchars($_POST['new_pass']).'"/><br />';
		}
	}

	?>
	<form method='post'>
		Старый пароль:<br />
		<input type='text' name='old_pass' value='' /><br />
		Новый пароль:<br />
		<input type='password' name='new_pass' value='' /><br />
		Подтверждение:<br />
		<input type='password' name='confirm_pass' value='' /><br />
		<input type='submit' name='save' value='Изменить' />
	</form>

	<div class='link'>
	&laquo;<a href='menu.php'>Кабинет</a><br />
	</div>
	<?php
	include incDir . 'foot.php';