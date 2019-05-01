<?php

	include_once H.'engine/functions/censure.php';
	
	if (isset($_COOKIE['i_am_sign_up']))
	{
		$_COOKIE['i_am_sign_up']++;
	}
	else
	{
		setcookie('i_am_sign_up', 0, $time + 60 * 3600, '/');
	}

	if (in_array($_POST['password'], array('qwerty', 'йцукен', 'пароль', 'password')) ||
		$_POST['password'] == $user['nick'] || 
		is_numeric($_POST['password']))
	{
		$sql->query("
			INSERT INTO `suspicious_users` SET 
			`name`  = 'Простой пароль',
			`text` = 'У пользователя <a href=\'/pages/user.php?id=".$user['id']."\'>".$user['nick']."</a> слишком простой пароль'");
	}
	
	if (is_numeric($user['nick']) || 
		$user['nick'] == 'test' ||
		substr_count($user['nick'], 'admin'))
	{
		$sql->query("
			INSERT INTO `suspicious_users` SET 
			`name`  = 'Подозрительный ник',
			`text` = 'У пользователя <a href=\'/pages/user.php?id=".$user['id']."\'>".$user['nick']."</a> подозрительный ник'");
	}

	if ($sql->query('SELECT COUNT(*) FROM `user` WHERE `ip` = '.ip2long($_SERVER['REMOTE_ADDR']))->result() > 1 ||
		$_COOKIE['i_am_sign_up'] > 0)
	{
		$sql->query("
			INSERT INTO `suspicious_users` SET 
			`name`  = 'Множественая регистрация',
			`text` = 'Возможно <a href=\'/pages/user.php?id=".$user['id']."\'>".$user['nick']."</a>  уже был зарегистрирован, <a href=\'/admin/anti/antitwink.php?ip=".$_SERVER['REMOTE_ADDR']."\'>проверить</a>'");
	}

	if ($censure = censure($user['nick']))
    {
       	$sql->query("
			INSERT INTO `suspicious_users` SET 
			`name`  = 'Подозрительный ник',
			`text` = 'Ник пользователя <a href=\'/pages/user.php?id=".$user['id']."\'>".$user['nick']."</a> содержит мат ".$censure."'");
    }
