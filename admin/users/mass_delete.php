<?php
	include '../../engine/includes/start.php';
	
	if (!$creator)
		Core::stop('/?');

	$set['title'] = 'Удаление пользователей';
	include incDir . 'head.php';

	if (isset($_POST['write'], $_POST['write2']))
	{
		$timeclear1 = $del_th = 0;

		if ($_POST['write2']=='sut')$timeclear1=$time-intval($_POST['write'])*60*60*24;
		elseif ($_POST['write2']=='mes')$timeclear1=$time-intval($_POST['write'])*60*60*24*30;
		else $err[]='Не выбран период';
		
		$q = mysqli_query($sql->db, "SELECT * FROM `user` WHERE `date_last` < '$timeclear1'");
		$service = mysqli_query($sql->db, 'select `file` from `module_services` where `use_in`="delete_user"');

		while ($editor = $sql->fetch($q))
		{
			$sql->query("DELETE FROM `user` WHERE `id` = '$editor[id]' LIMIT 1");
			while($file = $sql->result($service))
			{
				include_once H . $file;
			}
			$del_th++;
		}
	
		Core::msg_show('Удалено '.$del_th.' пользователей');
	}

	?>
	<form method="post" class='foot' action="?">
		Будут удалены пользователи, не посещавшие сайт<br />
		<input name="write" value="6" type="text" size='3' />
		<select name="write2">
			<option value="">       </option>
			<option value="mes">Месяцев</option>
			<option value="sut">Суток</option>
		</select><br />
		<input value="Удалить" type="submit" /><br />
	</form>
	<div class="link">Удаленные данные невозможно будет восстановить</div>
	<a href="/admin/?act=users"><div class="link">Пользователи</div></a>
	<a href="/admin/"><div class="link">Админка</div></a>
	<?php
	include incDir . 'foot.php';