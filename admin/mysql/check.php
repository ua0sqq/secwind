<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Проверка таблиц';
    include incDir.'head.php';

	if (isset($_GET['check']))
	{
		$i = 0;
		$timer = microtime(1);
		$analyze = isset($_GET['analyze']);
		$sql->query('SHOW TABLES');

		while ($table = $sql->fetch())
		{
			$data = mysqli_fetch_assoc(mysqli_query($sql->db, 'check table `'.$table['Tables_in_'.$set['mysql_db_name']] . '` EXTENDED'));

			if ($data['Msg_text'] != 'OK')
			{
				mysqli_query($sql->db, 'repair table `'.$table['Tables_in_'.$set['mysql_db_name']] . '` EXTENDED');
				$i++;
			}
			
			if ($analyze)
			{
				mysqli_query($sql->db, 'analyze table `'.$table['Tables_in_'.$set['mysql_db_name']] . '`');
			}
		}
		Core::msg_show('Отремонтировано таблиц: '.$i.' в течении '. round(microtime(1) - $timer, 4) .' сек.');
	}
	else
	{
		?>
		<div class="post">
			Утилита проверяет ваши таблицы, и если требуется ремонтирует таблицу, которая, возможно, повреждена.
		</div>
		<form>
			<label>
				<input type="checkbox" name="analyze" value="1"/> Также анализировать таблицы
			</label>
			<br />
			<input type="submit" name="check" value="Начать"/>
		</form>
		<?php
	}

	echo '<a href="/admin/?act=mysql"><div class="link">MySQL</div></a><a href="/admin/"><div class="link">Админка</div></a>';

    include incDir.'foot.php';