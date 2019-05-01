<?php
	include '../engine/includes/start.php';
	
	$set['title'] = 'Регистрация';
	
	include H.'engine/includes/head.php';

	if ($user_id)
		Core::stop('/pages/user.php');
	
	if (isset($_GET['id'], $_GET['activation']))
	{
		if ($sql->query("SELECT COUNT(*) FROM `user` WHERE `id` = '".my_esc($_GET['id'] ,'true')."' AND `activation` = '".my_esc($_GET['activation'],'true')."'")->result() !== '0')
		{
			$sql->query("UPDATE `user` SET `activation` = 'null' WHERE `id` = '$id'");
			$user['password'] = $sql->query("SELECT `password` FROM `user` WHERE `id` = '$id'")->result();
			Core::msg_show('Ваш аккаунт успешно активирован');
			
			setcookie('user_id', $id, $time+60*60*24*365, '/');
		    setcookie('pass', $user['password'], $time+60*60*24*365, '/');

			echo '<a href="/pages/user.php">Перейти в свой кабинет</a>';

			include H.'engine/includes/foot.php';
		}
		else
			Core::msg_show('Ошибка активации аккаунта');
	}
	
	if (isset($_POST['auth']))
	{
		$log_len = mb_strlen($_POST['login']);

		if (empty($_POST['login']))
			$error[] = 'Введите логин';
		
		if (!isset($_POST['sex']))
			$error[] = 'Выберите пол';

		if (empty($_POST['password']))
			$error[] = 'Введите пароль';
		
		if (empty($_POST['password2']))
			$error[] = 'Введите повтор пароля';
		
		if (!empty($_POST['login']) && $log_len < 3 || $log_len > 16)
			$error[] = 'Логин должен быть не меньше 3х и не больше 16 символов';
		
		if (!empty($_POST['password']) && mb_strlen($_POST['password']) < 6)
			$error[] = 'Пароль должен быть не меньше 6';
		
		if ($_POST['password'] !== $_POST['password2'])
			$error[] = 'Пароль не соответствует повтору пароля';
			
		if (empty($_SESSION['captcha']) || trim(strtolower($_POST['captcha'])) != $_SESSION['captcha'])
		{
			$error[] = 'Неверный проверочный код';
		}
		
		if (isset($set['reg_select']) &&  $set['reg_select'] == 'open_mail')
		{
			if (empty($_POST['email']))
				$error[] = 'Введите E-mail';
			
			if (!empty($_POST['email']) && !preg_match('#^[A-z0-9-\._]+@[A-z0-9]{2,}\.[A-z]{2,4}$#ui',$_POST['email']))
				$error[] = 'Не верный формат E-mail';
		}
		
		if (!isset($error))
		{
			if ($sql->query("SELECT COUNT(*) FROM `user` WHERE `nick` = '".my_esc($_POST['login'], 'true')."'")->result() == 0)
			{
				$status = $sql->query('SHOW TABLE STATUS LIKE "user"')->fetch(); 
				$password = md5($status['Auto_increment'] . $_POST['password']); 
				$login = my_esc($_POST['login'], 'true');
				$sex = intval($_POST['sex']);
				
				if (!empty($set['activation_acc']))
				{
					$key = md5(mt_rand());
					$email = my_esc($_POST['email'], 'true');
					
					$sql->query("INSERT INTO `user` (`nick`, `pass`, `date_reg`, `date_last`, `activation`, `pol`, `ank_mail`) VALUES ('$login', '$password', '$time', '$time', '$key', '$sex', '$email')");
					$user['id'] = $sql->query("SELECT * FROM `user` WHERE `nick` = '".my_esc($_POST['login'])."' LIMIT 1")->result();
					$user = Core::get_user($user['id'], true);
					
					$subject = "Активация аккаунта";
					$regmail = "Здравствуйте $login<br />
					Для активации Вашего аккаунта перейдите по ссылке:<br />
					<a href='http://$_SERVER[HTTP_HOST]/pages/registration.php?id=$user[id]&amp;activation=$key'>http://$_SERVER[HTTP_HOST]/pages/reg.php?id=$user[id]&amp;activation=$key</a><br />
					Если аккаунт не будет активирован в течении 24 часов, он будет удален<br />
					С уважением, администрация сайта<br />";
					$adds = "From: \"password@$_SERVER[HTTP_HOST]\" <password@$_SERVER[HTTP_HOST]>\n";
					$adds .= "Content-Type: text/html; charset=utf-8\n";
					mail($_POST['email'],'=?utf-8?B?'.base64_encode($subject).'?=',$regmail,$adds);
					
					Core::msg_show('Вам необходимо активировать Ваш аккаунт по ссылке, высланной на E-mail');
					
					include H.'engine/includes/foot.php';
				}
				else
				{
					$sql->query("INSERT INTO `user` (`nick`, `pass`, `date_reg`, `date_last`, `pol`, `ip`, `ua`) 
					VALUES ('$login', '$password', '$time', '$time', '$sex', '".ip2long($_SERVER['REMOTE_ADDR'])."', '".my_esc($_SERVER['HTTP_USER_AGENT'], true)."')");
					$user = $sql->query("SELECT * FROM `user` WHERE `nick` = '".my_esc($_POST['login'])."' LIMIT 1")->result();
					$user = Core::get_user($user, true);
					setcookie('user_id', $user['id'], $time+60*60*24*365, '/');
					setcookie('pass', $password, $time+60*60*24*365, '/');
					$res = mysqli_query($sql->db, 'select `file` from `module_services` where `use_in` ="reg"');
					while($file = $sql->result($res))
					{
						include_once H . $file;
					}
					?>
					Вы успешно зарегистрировались<br />
					 <a href="/pages/user.php"><div class="link">Перейти в свой кабинет</div></a>
					<?
					include H.'engine/includes/foot.php';
				}
			}
			else
				Core::msg_show('Пользователь с логином '.Core::form('login').' уже зарегистрирован');
		}
		else
			Core::msg_show($error);
	}
	
	?>
	<form action="" method="POST">
	Логин [A-z А-я 0-9]<br />
	<input type="text" name="login" value="<?=Core::form('login')?>" /><br />
	
	<?
	if (!empty($set['activation_acc']))
	{
	?>
			E-mail:<br />
			<input type="text" name="email" value="<?=Core::form('email')?>"/><br />
	<?
	}
	?>
	
	Пол:<br />
	<select name="sex">
		<option value="1">Мужской</option>
		<option value="0">Женский</option>
	</select><br />
	
	Пароль:<br />
	<input type="password" name="password" value="<?=Core::form('password')?>"/><br />
	
	Повтор пароля:<br />
	<input type="password" name="password2" value="<?=Core::form('password2')?>"/><br />
	
	<img src = "/pages/captcha.php"/><br />
	
	Проверочный код:<br />
	<input type = "text" name = "captcha"/><br />
	
	<input type="submit" name="auth" value="Зарегистрироваться" />
	
	</form>
	<?
	include H.'engine/includes/foot.php';