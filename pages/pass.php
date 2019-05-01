<?php
    include '../engine/includes/start.php';
	
	$set['title'] = 'Восстановление пароля';
	
	include H.'engine/includes/head.php';
	
	if ($user_id)
		Core::stop('/?');


	/*
	FoXxs
	*/

	if (isset($_GET['set_new']))
	{
		if ($sql->query("SELECT COUNT(*) FROM `user` WHERE `id` = '".$id."' AND `sess` = '".my_esc($_GET['set_new'], 'true')."'")->result() > '0')
		{
			if (isset($_POST['go2']))
			{
				$error_pass = false;
				
				if (empty($_POST['password']))
				{
					Core::msg_show('Введите старый пароль');
					$error_pass = true;
				}
				
				if (empty($_POST['password_new']))
				{
					Core::msg_show('Введите новый пароль');
					$error_pass = true;
				}
				
				if (empty($_POST['password_new2']))
				{
					Core::msg_show('Введите повтор нового пароля');
					$error_pass = true;
				}
				
				if ($_POST['password_new'] !== $_POST['password_new2'])
				{
					Core::msg_show('Повтор пароля введен не верно');
					$error_pass = true;
				}
				
				if (!isset($error_pass))
				{
					$user2 = $sql->query("SELECT `id` FROM `user` WHERE `id` = '".$id."'")->fetch();
					$password = md5($user2['id'] . $_POST['password_new']);
					
					$sql->query("UPDATE `user` SET `pass` = '$password' WHERE `id` = ".$user2['id']."");
					
					setcookie('user_id', $user2['id'], $time+60*60*24*365);
					setcookie('pass', $password, $time+60*60*24*365);
					
					Core::msg_show('Пароль успешно сменен');
					echo '<a href="/pages/user.php">Моя анкета</a>';
				}
			}
			else
			{
			?>
				<form action="" method="">
				Введите старый пароль:<br />
				<input type="text" name="password" /><br />
				
				Введите новый пароль:<br />
				<input type="password" name="password_new" /><br />
				
				Введите повтор нового пароля:<br />
				<input type="password" name="password_new2" /><br />
				
				<input type="submit" name="go2" value="Завершить">
				</form>
			<?
			}
		}
		else
			Core::msg_show('Ошибка восстановления пароля');
	}
	else
	{
		if (isset($_POST['go']))
		{
			if (empty($_POST['login']))
			{
				Core::msg_show('Введите логин');
				$error_pass = true;
			}
			
			if (empty($_POST['email']))
			{
				Core::msg_show('Введите E-mail');
				$error_pass = true;
			}
			
			if (!isset($error_pass))
			{
				if ($sql->query("SELECT COUNT(*) FROM `user` WHERE `nick` = '".my_esc($_POST['login'],'true')."'")->result() > '0')
				{
					$user = $sql->query("SELECT `id`,`nick`,`ank_mail` FROM `user` WHERE `nick` = '".my_esc($_POST['login'],'true')."'")->fetch();
					
					if (empty($user2['ank_mail']))
						Core::msg_show('Восстановление пароля не возможно<br />На данном логине нет E-mail');
					elseif ($_POST['email'] !== $user2['ank_mail'])
						Core::msg_show('E-mail введен не верно');
					else
					{
						$new_sess = substr(md5(md5(md5(mt_rand()))), 0, 20);
						$subject = 'Восстановление пароля';
						$regmail = "Здравствуйте $user[nick]<br />
						Вы активировали восстановление пароля<br />
						Для установки нового пароля перейдите по ссылке:<br />
						<a href='http://$_SERVER[HTTP_HOST]/pass.php?id=$user[id]&amp;set_new=$new_sess'>http://$_SERVER[HTTP_HOST]/pass.php?id=$user[id]&amp;set_new=$new_sess</a><br />
						Данная ссылка действительна до первой авторизации под своим логином ($user[nick])<br />
						С уважением, администрация сайта<br />";
						$adds = "From: \"password@$_SERVER[HTTP_HOST]\" <password@$_SERVER[HTTP_HOST]>\n";
						$adds .= "Content-Type: text/html; charset=utf-8\n";
						mail($user['ank_mail'],'=?utf-8?B?'.base64_encode($subject).'?=',$regmail,$adds);

						$sql->query("UPDATE `user` SET `sess` = '$new_sess' WHERE `id` = '".$user2['id']."' LIMIT 1");

						Core::msg_show('Письмо с ссылкой для активации пароля отправлена на Ваш E-mail '.$user2['ank_mail'].'');
					}
					
				}
				else
				  Core::msg_show('Пользователь с таким логином не найден');
			}
		}
		?>
		<form action="" method="POST">
		Логин:<br />
		<input type="text" name="login" /><br />

		E-mail:<br />
		<input type="text" name="email" /><br />
		<input type="submit" name="go" value="Отправить" />
		</form>
		На ваш E-mail придет ссылка для установки нового пароля.<br />
		Если у вас в анкете отсутствует запись о вашем e-mail, восстановление пароля невозможно.<br />
		<?
	}
	include H.'engine/includes/foot.php';