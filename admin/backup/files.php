<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();
    $set['title']='Резервное копирование файлов';
    require incDir.'head.php';
    Core::get('text.class', 'classes');
    
    //@set_time_limit(20);

    echo '<div class="menu_razd">Выбор файлов</div>';
    echo '<div class="post"><img src="/style/users/icons/1.png"/> <a href="/pages/user.php?id=2">Система</a><br />Резервное копирование долгий и ресурсоемкий процесс, если бекап не создается, раскоментируйте 9-строку <b>set_time_limit(20);</b>. На бесплатных хостингах, резервное копирование является платным, на платных нет смысла делать =)</div>';
    $arr=array();
    if (isset($_POST['file_zip']))
    {
        foreach ( $_POST['file_zip'] as $file )
	    {
            $arr[] = H . $file;
	    }
	
	    $name = empty($_POST['name']) ? 'Backup_'.mt_rand(12,1222) : $_POST['name'];
	
	    if (is_file(H.'engine/files/backup/files/'.$name.'.zip') and empty($_POST['delcopy']) )
	    {
		    echo 'Архив '. $name .'.zip уже есть!<br/><a href="backup.php">Повторить</a>';	
            require incDir.'foot.php';
	    } 
        elseif (is_file(H.'engine/files/backup/files/'.$name.'.zip'))
	    {
		    echo 'Архив '. $name .'.zip заменен<br/>';
	    }

        Core::get('zip', 'classes');
	    $create = new PclZip(H.'engine/files/backup/files/'.$name.'.zip');//var_dump($arr);
	    //$create -> ($arr, PCLZIP_OPT_REMOVE_PATH, '\\');
        Core::msg_show($create -> create($arr,  PCLZIP_OPT_REMOVE_PATH, H) == 0 ? $create -> errorInfo(true) : 'Архив создан!');
	    unset($create, $file);
    }
    

	echo '<form method="post"><input type="text" name="name" size="40" value="Backup__'.date("d_m_y").'__'.(mt_rand(0,999)).'"/><br/>';

	$dir = opendir('../../');

	echo isset($_GET['get']) ? '<a href="?">Снять все</a>':'<a href="?get">Отметить все</a>';

	while ($file = readdir($dir))
	{
		if ($file != '.' && $file != '..')
		{
			echo  '
				<div class="link"><label>
				<input type="checkbox" '.(isset($_GET['get'])?'checked="checked"':'').' name="file_zip[]" value="'. $file .'"/> '.$file .' ('. (is_file(H . $file) ? text::size_data(filesize(H.$file)) : 'папка' ) .')
				</label></div>';
		}
	}
    closedir($dir);

	?>
    <div class="post"><input type="checkbox" checked="checked" name="delcopy" value="1"/> Заменить при совпадении имен</div>
    <input type="submit" name="create" value="Архивировать" /></form>
    <div class="menu_razd">См. также</div>
	<a href='/admin/backup/list.php'><div class="link">Список Backup</div></a>
	<a href='mysql.php'><div class="link">Резервное копирование базы данных</div></a>
    <a href='/admin/'><div class="link">Админка</div></a>
    <?
    require incDir.'foot.php';