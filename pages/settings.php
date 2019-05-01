<?php
	include '../engine/includes/start.php';

	if (!$user_id)
		Core::stop();

	$set['title'] = 'Мои настройки';
	
	Core::get('cache.class');
	$cache = new cache(tmpDir . 'themes.swc');
	if (!$cache->life())
	{
		$opendir = opendir(H.'style/themes/');
		while ($theme = readdir($opendir))
		{
			if ($theme == '.' || $theme == '..' || !is_dir(H.'style/themes/'.$theme))
				continue;
			$config = parse_ini_file(H.'style/themes/'.$theme.'/theme.ini');
			$themes[$theme] = $config['name'];
		}
		$cache->write(serialize($themes));
	}
	
	$themes = unserialize($cache->read());


	if (isset($_POST['save']))
	{
		if (isset($_POST['theme']) && is_dir(H.'style/themes/'.$_POST['theme']))
		{
			$_COOKIE['set_theme'] = $user['set_them'] = Core::form('theme');
			$sql->query("UPDATE `user` SET `set_them` = '".$user['set_them']."' WHERE `id` = ".$user_id." LIMIT 1");
            setcookie('set_theme', $user['set_them'] , 0, '/');
		}
		$user = Core::get_user($user_id, true);
	}

	include incDir . 'head.php';

	?>
	<form method='post'>
	Тема:<br />
	<select name='theme'>
	<?php
	foreach($themes as $theme => $name)
	{
		echo '<option value="'.$theme.'">'.$name.'</option>';
	}
	?>
	</select><br />
	<input type='submit' name='save' value='Сохранить'/>
	</form>
	<div class='link'>
	&laquo;<a href='menu.php'>Кабинет</a><br />
	</div>
	<?php
	include incDir . 'foot.php';