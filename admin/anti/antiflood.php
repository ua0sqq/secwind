<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();
    $set['title'] = 'Антифлуд';
    include incDir . 'head.php';

	$config = file_exists(H . 'engine/files/data/flood_config.swi') ? unserialize(file_get_contents(H . 'engine/files/data/flood_config.swi')) : array(2 => 2, 0 => 0, -1 => -1);
	$array = array(1 => 'Первое', 2 => 'Второе', 3 => 'Третье');
	
	if ($act == 'del')
	{
		unlink(H . 'engine/files/data/flood_config.swi');
	}

	if (!file_exists(H . 'engine/files/data/flood_config.swi'))
	{
		echo 'Антифлуд выключен<br />';
	}
	else
	{
		echo 'Антифлуд активен, <a href="?act=del">отключить</a>';
	}

	if (isset($_POST['save']))
	{
		$config = array();

		for ($i = 1; $i < 4; $i++)
		{
			if (isset($_POST[$i . '_request']))
			{
				$config[(int) $_POST[$i . '_request']] = (int) $_POST[$i . '_time'];
			}
		}
		file_put_contents(H . 'engine/files/data/flood_config.swi', serialize($config));
	}
	?>
	<form action="?act=form" method="post">
	Количество запросов - В течении секунд...<br />
	<?php
	$i = 0;
	while (list($key, $val) = each($config))
	{
		if (++$i == 4)
			break;
		echo 
			$array[$i] . ' правило:<br />
			<input type="text" name="'.$i.'_request" value="'.$key.'"/> - <input type="text" name="'.$i.'_time" value="'.$val.'"/><br />';
	}
	?>
	<input type="submit" name="save"/>
	</form>
	<?php
	include incDir . 'foot.php';