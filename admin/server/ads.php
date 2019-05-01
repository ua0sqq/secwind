<?php

	include '../../engine/includes/start.php';

	if (!$creator)
        Core::stop();

	
	$set['title'] = 'Реклама';

	include incDir . 'head.php';

	switch($act)
	{
	
		case 'add':
		
			if (isset($_POST['name'], $_POST['link']))
			{
				$name = Core::form('name');
				$link = Core::form('link');
				$img = Core::form('img');
				
				switch($_POST['time_type'])
				{
					default:
					case 'hour':
						$ads_time = $time + intval($_POST['time']) * 60 * 60;
							break;
				
					case 'sut':
						$ads_time = $time + intval($_POST['time']) * 60 * 60 * 24;
							break;
							
					case 'mes':
						$ads_time = $time + intval($_POST['time']) * 60 * 60 * 24 * 30;
							break;
				}
	
				$pos = Core::form('pos') == 'top' ? 'top' : 'bottom';
				$main = Core::form('main') ? 1 : 0;
				$new_line = Core::form('new_line') ? 1 : 0;
				
				$sql->query('
					INSERT INTO `ads` SET 
					`name` = "'.$name.'",
					`link` = "'.$link.'",
					`img` = "'.$img.'",
					`time` = "'.$ads_time.'",
					`pos` = "'.$pos.'",
					`main` = "'.$main.'",
					`new_line` = "'.$new_line.'"
					');
				Core::msg_show('Реклама добавлена', 'msg');
			}
			
			?>
				<a href="?" class="link">Список рекламных ссылок</a>
				<form method="post">
					Название:<br />
					<input type="text" name="name" value=""/><br />
					Ссылка:<br />
					<input type="text" name="link" value="http://"/><br />
					Изображение:<br />
					<input type="text" name="img" value=""/><br />
					Длительность показа рекламы: <br />
					<input name = "time" value="12" type="text" size='3' />
					<select name = "time_type">
						<option value=""></option>
						<option value="mes">Месяцев</option>
						<option value="sut">Суток</option>
						<option value="hour">Часов</option>
					</select><br />
					Позиция:<br />
					<select name="pos">
						<option value="top">Наверху</option>
						<option value="bottom">Внизу</option>
					</select><br />
					На главной:<br />
					<select name="main">
						<option value="0">Нет</option>
						<option value="1">Да</option>
					</select><br />
					С новой строки:<br />
					<select name="new_line">
						<option value="0">Нет</option>
						<option value="1">Да</option>
					</select><br />
					<input type="submit" value="Добавить"/>
				</form>
			<?php
		
		break;
	
		case 'delete':
		
			$sql->query('SELECT * FROM `ads` WHERE `id` = '.$id);
			
			if ($sql->num_rows() == 0)
			{
				Core::msg_show('Реклама не найдена');
				include incDir . 'foot.php';
			}
		
			if (isset($_GET['confirm'], $_SESSION['ads_confirm_code']) && $_SESSION['ads_confirm_code'] == $_GET['confirm'])
			{
				$sql->query('DELETE FROM `ads` WHERE `id` = '.$id);
				Core::msg_show('Реклама удалена', 'msg');
				echo '<a href="?" class="link">Список рекламных ссылок</a>';
			}
			else
			{
				$_SESSION['ads_confirm_code'] = uniqid();
				echo 'Вы действительно хотите удалить рекламу?<br /><a href="?act=delete&amp;id='.$id.'&amp;confirm='.$_SESSION['ads_confirm_code'].'">Удалить</a>,   <a href="?">Нет. Вернутся в список рекламы</a>';
			}
		
		break;
		
		case 'edit':
		
			$sql->query('SELECT * FROM `ads` WHERE `id` = '.$id);
			
			if ($sql->num_rows() == 0)
			{
				Core::msg_show('Реклама не найдена');
				include incDir . 'foot.php';
			}
		
			$ads = $sql->fetch();
		
			if (isset($_POST['name'], $_POST['link']))
			{
				$ads['name'] = Core::form('name');
				$ads['link'] = Core::form('link');
				$ads['img'] = Core::form('img');
				
				switch($_POST['time_type'])
				{
					default:
					case 'hour':
						$ads['time'] = $time + intval($_POST['time']) * 60 * 60;
							break;
				
					case 'sut':
						$ads['time'] = $time + intval($_POST['time']) * 60 * 60 * 24;
							break;
							
					case 'mes':
						$ads['time'] = $time + intval($_POST['time']) * 60 * 60 * 24 * 30;
							break;
				}
				
				$ads['pos'] = Core::form('pos') == 'top' ? 'top' : 'bottom';
				$ads['main'] = Core::form('main') ? 1 : 0;
				$ads['new_line'] = Core::form('new_line') ? 1 : 0;
				
				$sql->query('
					UPDATE `ads` SET 
					`name` = "'.$ads['name'].'",
					`link` = "'.$ads['link'].'",
					`img` = "'.$ads['img'].'",
					`time` = "'.$ads['time'].'",
					`pos` = "'.$ads['pos'].'",
					`main` = "'.$ads['main'].'",
					`new_line` = "'.$ads['new_line'].'"
					WHERE `id` = '.$id);

				Core::msg_show('Реклама изменена', 'msg');
			}

			$time_type = 'hour';
			$ads['time'] = $ads['time'] - $time;

			if ($ads['time'] >=  2592000)
			{
				$time_type = 'mes';
				$ads['time'] = ceil($ads['time'] / 2592000);
			}
			elseif ($ads['time'] >= 86400)
			{
				$time_type = 'sut';
				$ads['time'] = ceil($ads['time'] / 86400);
			}
			else
			{
				$ads['time'] = ceil($ads['time'] / 3600);
			}
			

			?>
				<a href="?" class="link">Список рекламных ссылок</a>
				<form method="post">
					Название:<br />
					<input type="text" name="name" value="<?=$ads['name']?>"/><br />
					Ссылка:<br />
					<input type="text" name="link" value="<?=$ads['link']?>"/><br />
					Изображение:<br />
					<input type="text" name="img" value="<?=$ads['img']?>"/><br />
					Время действия: <br />
					<input type="text" name="time" value="<?=$ads['time']?>"/><br />
					<select name="time_type">
						<option value="mes" <?=$time_type == 'mes' ? 'selected="selected"' : ''?>>Месяцев</option>
						<option value="sut" <?=$time_type == 'sut' ? 'selected="selected"' : ''?>>Суток</option>
						<option value="sut" <?=$time_type == 'hour' ? 'selected="selected"' : ''?>>Часов</option>
					</select><br />
					Позиция:<br />
					<select name="pos">
						<option value="top">Наверху</option>
						<option value="bottom"<?=$ads['pos'] == 'bottom' ? ' selected="selected"' : ''?>>Внизу</option>
					</select><br />
					На главной:<br />
					<select name="main">
						<option value="0">Нет</option>
						<option value="1"<?=$ads['main'] == 1 ? ' selected="selected"' : ''?>>Да</option>
					</select><br />
					С новой строки:<br />
					<select name="new_line">
						<option value="0">Нет</option>
						<option value="1"<?=$ads['new_line'] == 1 ? ' selected="selected"' : ''?>>Да</option>
					</select><br />
					<input type="submit" value="Изменить"/>
				</form>
			<?php
		
		break;
		
		default:
		
			echo '<a class="link" href="?act=add">Добавить</a>';
			$i = 0;
			$sql->query('SELECT * FROM `ads` WHERE `time` > '. $time);
			while($ads = $sql->fetch())
			{
				echo 
				'<div class="'.($i++ % 2 ? 'p_m' : 'p_t').'">
					<b>Название: </b> '.$ads['name'].'<br />
					<b>Ссылка: </b> '.$ads['link'].'<br />
					<b>Картинка: </b> '.(!empty($ads['img']) ? $ads['img'] : 'нет').'<br />
					<b>Позиция: </b> '.($ads['pos'] == 'top' ? 'Наверху' : 'Внизу').'<br />
					<b>На главной: </b> '.($ads['main'] ? 'да' : 'нет').'<br />
					<b>Время окончания: </b> '.Core::time($ads['time']).'<br />
					<b>С новой строки: </b> '.($ads['new_line'] ? 'да' : 'нет').
				'<br />[  <a href="?act=edit&amp;id='.$ads['id'].'">Изменить</a>  ] | [  <a href="?act=delete&amp;id='.$ads['id'].'">Удалить</a>  ]'.
				'</div>';
			}
		
		break;
	}

	?>
	<div class="menu_razd">См. также</div>
    <a href='/admin/'><div class="link">Админка</div></a>
	<a href='/admin/?act=server'><div class="link">Админка / Сайт</div></a>
	<?php
	include incDir . 'foot.php';