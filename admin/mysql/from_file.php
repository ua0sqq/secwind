<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Сделать запрос из файла';
    include incDir.'head.php';

	if (isset($_POST['exec']))
	{
		$sql->from_file($_FILES['file']['tmp_name']);
		Core::msg_show('Запросы выполнены', 'menu_razd');
	}
	elseif (isset($_GET['file']) && file_exists(H . 'engine/files/backup/mysql/'.$_GET['file']))
	{
		if (isset($_POST['query']))
		{
			$sql->from_file(H . 'engine/files/backup/mysql/'.$_GET['file']);
			if (Core::form('delete') == 1)
				unlink(H . 'engine/files/backup/mysql/'.$_GET['file']);
			Core::msg_show('Запросы выполнены', 'menu_razd');
		}
		else
		{
			echo
				'Вы действительно хотите выполнить запрос из файла '.$_GET['file'].'?'.
				'<form method="post"><label><input type="checkbox" name="delete" checked="checked" value="1"/> Удалить файл</label><br /><input type="submit" name="query" value="Выполнить"/></form>';
		}
	}

    ?>
	Сделать запрос из sql файла
	<form method="post" enctype="multipart/form-data">
        <input type="file" name="file"/><br />
        <input value = "Выполнить" name="exec" type="submit" />
    </form>
	<a href="/admin/?act=mysql"><div class="menu_razd" style="width:45%;display:inline-block">MySQL</div></a>
    <a href="/admin/"><div class="menu_razd" style="width:48%;display:inline-block">Админка</div></a>
	<?
    
    include incDir.'foot.php';