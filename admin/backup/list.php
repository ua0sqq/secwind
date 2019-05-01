<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();
    $set['title'] = 'Список Backup';
    require incDir.'head.php';
	
	$act = $act == 'files' ? 'files' : 'mysql';
    
    $dropbox = is_file(H . 'engine/files/data/dropbox.ini') ? parse_ini_file(H . 'engine/files/data/dropbox.ini') : array('e-mail' => '', 'password' => '');

	echo '<table width="100%"><tr>';
	
	if ($act == 'files')
	{
		echo '<td class="menu_razd" style="width:50%;">Файлы</td><td class="link"><a href="?act=mysql" style="display:block;">MySQL</a></td>';
	}
	else
	{
		echo '<td class="link"><a href="?act=files" style="display:block;">Файлы</a></td><td class="menu_razd" width="50%">MySQL</td>';
	}
	echo '</tr></table>';
	
	if (isset($_GET['restore'], $_GET['file']) && is_file(H . 'engine/files/backup/'.$act.'/'.$_GET['file']))
	{
		Core::get('zip');
		$zip = new Pclzip(H . 'engine/files/backup/'.$act.'/'.$_GET['file']);
		$zip->extract(PCLZIP_OPT_SUBSTITUE_FILE, H);
		Core::msg_show('Восстановление по бекапу прошло успешно');
	}

	if (is_file(H . 'engine/files/backup/'.$act.'/'.Core::Request('delete')))
	{
		unlink(H . 'engine/files/backup/'.$act.'/'.Core::Request('delete'));
		Core::msg_show('Backup удален');
	}
    
    if (isset($_GET['dropbox']) && $dropbox['e-mail'] != null && is_file(H . 'engine/files/backup/'.$act.'/'.$_GET['dropbox']))
    {
        if (empty($dropbox[$act . '_' . $_GET['dropbox']]))
        {
            Core::get('DropboxUploader');
            $file = H . 'engine/files/backup/'.$act.'/'.$_GET['dropbox'];
            try
            {
                $uploader = new DropboxUploader($dropbox['e-mail'], $dropbox['password']);
                $uploader->upload($file, 'secwind_'.$_SERVER['SERVER_NAME'].'/backups/'.$act.'/', $_GET['dropbox']);
                echo '<div class="msg">Успешно отправлен на Dropbox</div>';
                $dropbox[$act . '_' . $_GET['dropbox']] = true;
            }
            catch (Exception $e)
            {
                $label = ($e->getCode() & $uploader::FLAG_DROPBOX_GENERIC) ? 'DropboxUploader' : 'Exception';
                $error = sprintf("[%s] #%d %s", $label, $e->getCode(), $e->getMessage());
                echo '<div class="err">' . htmlspecialchars($error) . '</div>';
            }
        }
        else
        {
            Core::msg_show('Уже в Dropbox', 'msg');
        }
    }
    

	$dir = opendir(H . 'engine/files/backup/'.$act);
	$restore = array('files' => 'list.php?act=files&amp;restore&amp;', 'mysql' => '/admin/mysql/from_file.php?');

	while($file = readdir($dir))
	{
		if ($file == '.' || $file == '..')
			continue;
		echo 
			'<div class="link">'.
			' <a href="'.$restore[$act].'file='.$file.'">Восст. ' . $file .'</a>&nbsp; &nbsp;
              <a href="?act='.$act.'&amp;delete='.$file.'">Удалить</a>&nbsp; &nbsp; 
              <a href="?act='.$act.'&amp;dropbox='.$file.'">Dropbox</a>
              <span style="float:right">Создан: '.Core::time(filemtime(H . 'engine/files/backup/'.$act.'/'.$file)).
			'</span></div>';
	}
    
    Core::save_settings($dropbox, 'engine/files/data/dropbox.ini');

    ?>
    <div class="menu_razd">См. также</div>
	<a href='/admin/backup/files.php'><div class="link">Резервное копирование файлов</div></a>
	<a href='mysql.php'><div class="link">Резервное копирование базы данных</div></a>
    <a href='/admin/'><div class="link">Админка</div></a>
    <?
    require incDir.'foot.php';