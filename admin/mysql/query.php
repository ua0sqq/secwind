<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Сделать запрос';
    include incDir.'head.php';

	if (isset($_POST['query']))
	{
		$sql->multi($_POST['query']); // экранируем символы
		$queries = substr_count($_POST['query'], ';');
		do
		{
			//if (!mysqli_more_results($sql->db))
				//break;

			$result = mysqli_store_result($sql->db);

			if (mysqli_errno($sql->db))
			{
				$error[] = htmlspecialchars(mysqli_error($sql->db));
			}

			$sql->free(true);
		}
		while (mysqli_more_results($sql->db)); 
		
		//$sql->multi($_POST['query']);
		//$sql->free(true);

		if ($queries == 0)
			$error[] = 'Запрос не выполнен';

		if (isset($error))
			Core::msg_show($error);
		else
			echo '<div class="menu_razd">Запросы ('.$queries.') выполнены</div>';
	}
    ?>
	<form method="post">
		Введите запрос:<br />
		<textarea name="query"></textarea><br />
		<input type="submit"/>
	</form>
	<a href='/admin/?act=mysql'><div class="menu_razd">MySQL</div></a>
    <a href='/admin/'><div class="menu_razd">Админка</div></a>
    <?php
    include incDir.'foot.php';