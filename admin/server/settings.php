<?php

	include '../../engine/includes/start.php';
	
	if (!$creator)
        Core::stop();

	
	$title = $set['title'];
	$set['title']='Настройки системы';
	include incDir . 'head.php';

	if (isset($_POST['save']))
	{
		$set['title'] = $title = Core::form('title') ? Core::form('title') : $_SERVER['SERVER_NAME'];
		$set['antimat'] = isset($_POST['antimat']);
		$set['meta_keywords'] = Core::form('meta_keywords');
		$set['meta_description'] = Core::form('meta_description');
		$set['cache_time'] = (int) $_POST['cache_time'];
		$set['activation_acc'] = (int) $_POST['activation_account'];

		if ($_POST['cache_time2'] == 'hour')
			$set['cache_time'] = 60 * 60 * $_POST['cache_time'];
		elseif ($_POST['cache_time2'] == 'min')
			$set['cache_time'] = 60 * $_POST['cache_time'];

		if (is_dir(H.'style/themes/'.$_POST['theme']))
		{
			$set['theme'] = Core::form('theme');
			$sql->query('ALTER TABLE `user` CHANGE `set_them` `set_them` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT "'.$set['theme'].'"');
		}
        
        Core::save_settings(array('e-mail' => $_POST['dx_e-mail'], 'password' => $_POST['dx_password']), 'engine/files/data/dropbox.ini');
        Core::save_settings(array('id' => intval($_POST['sw_id']), 'password' => Core::form('sw_password')), 'engine/files/data/sw_login.ini');

		Core::save_settings($set);
		Core::msg_show('Настройки успешно приняты', 'msg');
	}
    
    $dropbox = is_file(H . 'engine/files/data/dropbox.ini') ? parse_ini_file(H . 'engine/files/data/dropbox.ini') : array('e-mail' => '', 'password' => '');
    $sw_login = is_file(H . 'engine/files/data/sw_login.ini') ? parse_ini_file(H . 'engine/files/data/sw_login.ini') : array('id' => '', 'password' => '');
    
	Core::get('cache.class');
	$cache = new cache(tmpDir . 'themes.swc');
	if (!$cache->life())
	{
		$opendir = opendir(H.'style/themes/');
		while ($theme = readdir($opendir))
		{
			if ($theme == '.' || $theme == '..' || !is_dir(H.'style/themes/'.$theme))
				continue;
			$conf= parse_ini_file(H.'style/themes/'.$theme.'/theme.ini');
			$themes[$theme] = $conf['name'];
		}
		$cache->write(serialize($themes));
	}
	
	$themes = unserialize($cache->read());
	$activ = isset($set['activation_acc']) ? $set['activation_acc'] : 0;
	$c_time2 = 'sec';
	$c_time = isset($set['cache_time']) ? $set['cache_time'] : 3600;

	if ($set['cache_time'] >= 3600)
	{
		$c_time2 = 'hour';
		$c_time = $set['cache_time'] / 3600;
	}
	elseif ($set['cache_time'] >= 60)
	{
		$c_time2 = 'min';
		$c_time = $set['cache_time'] / 60;
	}

	?>
	<form method="post">
		Название сайта:<br />
		<input name="title" value="<?=$title?>" type="text" /><br />
		Тема :<br />
		<select name='theme'>
		<?php
		foreach($themes as $theme => $name)
		{
			echo '<option value="'.$theme.'">'.$name.'</option>';
		}
		?>
		</select><br />
		Ключевые слова (META):<br />
		<textarea name='meta_keywords'><?=$set['meta_keywords']?></textarea><br />
		Описание (META):<br />
		<textarea name='meta_description'><?=$set['meta_description']?></textarea><br />
		<label><input type='checkbox' <?=$set['antimat'] ? "checked='checked'":null?> name='antimat' value='1' /> Анти-Мат</label><br />
		(beta) Кеш является не актуальным через:<br />
		<input type="text" name="cache_time" value="<?=$c_time?>"/><br />
		<select name="cache_time2">
			<option value="sec" <?=$c_time2 == 'sec' ? 'selected="selected"' : ''?>>Секунд</option>
			<option value="min" <?=$c_time2 == 'min' ? 'selected="selected"' : ''?>>Минут</option>
			<option value="hour" <?=$c_time2 == 'hour' ? 'selected="selected"' : ''?>>Час</option>
		</select><br />
		Активация пользователей через e-mail:<br />
		<select name="activation_account">
			<option value="1">Да</option>
			<option value="0"<?=!$activ ? ' selected="selected"' : ''?>>Нет</option>
		</select><br />
        <hr />
        Dropbox e-mail:<br />
        <input type="text" name="dx_e-mail" value="<?=$dropbox['e-mail']?>"/><br />
        Dropbox пароль:<br />
        <input type="text" name="dx_password" value="<?=$dropbox['password']?>"/><br />
		<input value="Изменить" name='save' type="submit" />
        <hr />
        ID на secwind.ru:<br />
        <input type="text" name="sw_id" value="<?=$sw_login['id']?>"/><br />
        Пароль на secwind.ru:<br />
        <input type="text" name="sw_password" value="<?=$sw_login['password']?>"/><br />
		<input value="Изменить" name='save' type="submit" />
	</form>
	<div class="menu_razd">См. также</div>
	<div class="link"><a href="..?act=server">Сервер</a></div>
	<div class="link"><a href="..">Админка</a></div>
	<?php
	include incDir . 'foot.php';