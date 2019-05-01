<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();
    $set['title']='Резервное копирование базы данных';
    require incDir.'head.php';
    
    echo '<div class="menu_razd">Выберите таблицы</div>';
    if (!empty($_POST['table']))
    {
		$file = H . 'engine/files/backup/mysql/backup__' . (count($_POST['table']) === 1 ? current($_POST['table']) . '__' : null) . date('d_m_y_').mt_rand(99,999)  .  '__.sql';

        $backup = NULL;

        foreach ($_POST['table'] as $table)
        {
            $backup .= 'DROP TABLE IF EXISTS `'.$table.'`;'. PHP_EOL;
            $row = mysqli_fetch_row(mysqli_query($sql->db, 'SHOW CREATE TABLE `'.$table.'`'));
            $backup .=$row[1].';'.PHP_EOL . PHP_EOL . PHP_EOL;
            $res = mysqli_query($sql->db, 'SELECT * FROM `'.$table.'`');
            if (count($res) > 0)
            {
                while (($row = mysqli_fetch_assoc($res)))
                {
                    $keys = implode("`, `", array_keys($row));
                    $values = array_values($row);
                    foreach ($values as $k=>$v)
                    {
                        $values[$k] = my_esc($v);
                        $values[$k]=preg_replace("#(\n|\r){1,}#", '\n', $values[$k]);
                    }
                    $values2 = implode("', '", $values);
                    $values2 = "'".$values2."'";
                    $backup .= "INSERT INTO `$table` (`$keys`) VALUES ($values2);\r\n";
                }
                $backup .= PHP_EOL . PHP_EOL;
            }
        }

        file_put_contents($file, $backup);
        Core::msg_show('Backup успешно создан!', 'menu_razd');
    }

    echo '<div class="link">'. (isset($_GET['get']) ? '<a href="?">Снять все</a>' : '<a href="?get">Отметить все</a>') .'</div>';
    $tab = mysqli_query($sql->db, 'SHOW TABLES');
    echo '<form action="?backup" method="post">';
    while($table = mysqli_fetch_assoc($tab))
    {
        echo '<label><input type="checkbox" '.(isset($_GET['get'])?'checked="checked"':'').' name="table[]" value="'.$table['Tables_in_'.$set['mysql_db_name']].'"/> '.$table['Tables_in_'.$set['mysql_db_name']].'</label><br />';
    }

    ?>
    <input type="submit" name="create" value="Начать" /></form>
	<div class="menu_razd">См. также</div>
	<a href='/admin/backup/files.php'><div class="link">Резервное копирование файлов</div></a>
	<a href='/admin/backup/list.php'><div class="link">Список Backup</div></a>
    <a href='/admin/'><div class="link">Админка</div></a>
    <?
    require incDir.'foot.php';