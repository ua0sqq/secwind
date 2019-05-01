<?php
	
  /**
	* @author Dionis
	* denis.rassvetnit@gmail.com
	* ICQ: 465868235
	*/

    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Дефрагментация MySQL';
    include incDir.'head.php';

	switch($act)
	{
		case 'table':
			$sql->query('OPTIMIZE TABLE `' . $_GET['defrag'] . '`;');
			echo '<div class="menu_razd">Таблица ' . $_GET['defrag'] . ' ортимизирована</div><a href="/admin/mysql/defrag.php"><div class="link">Дефрагментация таблиц</div></a>';
		break;

		case 'all':
			$q = $sql->query('SHOW TABLE STATUS');
			$size = 0;

			while ($data_info = $sql->fetch())
			{
				if (!empty($data_info['Data_free']) && $data_info['Data_free'] > 0)
				{
					$size += $data_info['Data_free'];
					$sql->query('OPTIMIZE TABLE `' . $data_info['Name'] . '`;');
				}
			}

			Core::get('text.class');
			echo '<div class="menu_razd">Все таблицы оптимизированы. Перераспределено '.text::size_data($size).'</div><a href="?"><div class="link">Дефрагментация таблиц</div></a>';
		break;

		default:
			$sql->query('SHOW TABLE STATUS');
			$i = 0;
			echo 'Оптимизировать таблицы';
			while ($data_info = $sql->fetch())
			{
				if ($data_info['Data_free'] > 0)
				{
					echo '<div class="'.($i % 2 ? 'p_m' : 'p_t').'"><a href="?defrag=' . $data_info['Name'] . '&amp;act=table"> ' . $data_info['Name'] . '</a></div>';
					$i++;
				}
			}

			if ($i)
			{
				echo '
				<div class="menu_razd">Всего нуждаются в оптимизации ' . $i . ' таблиц</div>
					<a href="defrag.php?act=all"><div class="link">Оптимизировать все таблицы</div></a>';
			}
			else
				echo '<div class="menu_razd">Нет таблиц нуждающихся в оптимизации</div>';
		break;
	}

	echo '
	<a href="/admin/?act=mysql"><div class="menu_razd" style="width:45%;display:inline-block">MySQL</div></a>
    <a href="/admin/"><div class="menu_razd" style="width:48%;display:inline-block">Админка</div></a>';

    include incDir.'foot.php';