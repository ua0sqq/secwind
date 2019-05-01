<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Обновление';
    include incDir.'head.php';

	function pre_update($file)
	{
		Core::get('zip');
		$zip = new Pclzip($file);
		$_SESSION['update_file'] = $file;
        $_SESSION['update_with_ftp'] = isset($_POST['ftp']);

		echo '<div class="post">Будут заменены следующие файлы:<br />';
		foreach ($zip->listContent() as $file)
		{
			if ($file['folder'])
				continue;

			echo $file['stored_filename'] .' ('.round($file['size'] / 1024, 2).' kb)<br />';
		}
			echo '</div><a class="link" href="?">Применить обновление</a>';
	}

    function ftp_update($file)
	{
		Core::get('zip');
		$zip = new Pclzip($file);
		$contents = $zip->listContent();

        if ($contents == 0)
			$error = 'Архив поврежден или пуст';
		else
		{
			$config = $zip->extract(PCLZIP_OPT_BY_NAME, 'update.ini', PCLZIP_OPT_EXTRACT_AS_STRING);//, PCLZIP_CB_POST_EXTRACT, 'get_ini');
			file_put_contents(tmpDir . 'update.ini', $config[0]['content']);
			$config = parse_ini_file(tmpDir . 'update.ini', true);
			if (empty($config))
				$error = 'update.ini не найден';
			else
			{
				if (empty($config['version']) || version_compare($config['version'], Core::Secwind('version'), '<='))
					$error = 'Невозможно установить обновление до версии '. $config['version'] .', когда уже установлена версия '. Core::Secwind('version');
			}
		}

		if (!isset($error))
		{
            @set_time_limit(60);
			$zip->delete(PCLZIP_OPT_BY_NAME, 'upload.ini');
			//$zip->extract(PCLZIP_OPT_SUBSTITUE_FILE, H);
            Core::get('ftp');
            
            $ftp_data = parse_ini_file(H . 'engine/files/data/modules.ini');
            $ftp = new ftp;
            if (!$ftp->connect($ftp_data['ftp_host'], $ftp_data['ftp_user'], $ftp_data['ftp_pass']))
            {
                echo "conntecting failed: ";
                print_r($ftp->error_no);
                print_r($ftp->error_msg);
            }
            else{
            $upd_dir = tmpDir . 'update_to_'.$config['version'] . '/';
            mkdir($upd_dir, 0777);
            $zip->extract(PCLZIP_OPT_SUBSTITUE_FILE, $upd_dir);
            
            foreach($zip->listContent() as $file)
            {
                if ($file['folder'])
                {
                    if (!is_dir(H . $file['filename']))
                    {
                        mkdir(H . $file['filename'], 0777);
                    }
                }
                else
                {
                    //$fil = $zip->extract(PCLZIP_OPT_BY_NAME, $file['filename'], PCLZIP_OPT_EXTRACT_AS_STRING);
                    
                    $ftp->put($file['filename'], $upd_dir . $file['filename']); //, $fil[0]['content']))
                    unlink($upd_dir . $file['filename']);
                }
            }
            //file_put_contents('text.txt', serialize($ftp->lastLines));

			if (!empty($config['sql']))
			{
				Global $sql;
				$sql->from_file(H . $config['sql']);
				$sql->free(true);
				Core::msg_show('Сделаны запросы из файла ' .$config['sql'], 'menu_razd');
				@unlink(H . $config['sql']);
			}

			if (!empty($config['chmod']))
			{
				$dirs = explode(' ', $config['chmod']);
				foreach($dirs as $dir)
				{
					chmod(H . $dir, 0777);
				}
				Core::msg_show('Выставлены права доступа 777 на необходимые папки', 'menu_razd');
			}

			Core::msg_show('SecWind обновлен до версии '.$config['version'] . '<br />Список изменений: '.nl2br(htmlspecialchars($config['descr'])), 'post');
			$data = unserialize(file_get_contents(H . 'engine/files/data/secwind.db'));
			$data['version'] = $config['version'];
			
			if (!empty($config['secwind']))
			{
				$data = array_merge($data, $config['secwind']);
			}
			
			//file_put_contents(H . 'engine/files/data/secwind.db', serialize($data));
            }
		}
        
		unset($zip, $config, $headers);
	
		if (isset($error))
			Core::msg_show($error);
	}
	
	function update($file)
	{
		Core::get('zip');
		$zip = new Pclzip($file);
		$contents = $zip->listContent();

        if ($contents == 0)
			$error = 'Архив поврежден или пуст';
		else
		{
			$config = $zip->extract(PCLZIP_OPT_SUBSTITUE_FILE, 'update.ini', PCLZIP_OPT_EXTRACT_AS_STRING);//, PCLZIP_CB_POST_EXTRACT, 'get_ini');
			file_put_contents(tmpDir . 'update.ini', $config[0]['content']);
			$config = parse_ini_file(tmpDir . 'update.ini', true);
			if (empty($config))
				$error = 'update.ini не найден';
			else
			{
				if (empty($config['version']) || version_compare($config['version'], Core::Secwind('version'), '<='))
					$error = 'Невозможно установить обновление до версии '. $config['version'] .', когда уже установлена версия '. Core::Secwind('version');
			}
		}

		if (file_exists(tmpDir . 'update.upd'))
			unlink(tmpDir . 'update.upd');

		if (!isset($error))
		{
			$zip->delete(PCLZIP_OPT_BY_NAME, 'upload.ini');
			$zip->extract(PCLZIP_OPT_SUBSTITUE_FILE, H);

			if (!empty($config['sql']))
			{
				Global $sql;
				$sql->from_file(H . $config['sql']);
				$sql->free(true);
				Core::msg_show('Сделаны запросы из файла ' .$config['sql'], 'menu_razd');
				@unlink(H . $config['sql']);
			}

			if (!empty($config['chmod']))
			{
				$dirs = explode(' ', $config['chmod']);
				foreach($dirs as $dir)
				{
					chmod(H . $dir, 0777);
				}
				Core::msg_show('Выставлены права доступа 777 на необходимые папки', 'menu_razd');
			}

			Core::msg_show('SecWind обновлен до версии '.$config['version'] . '<br />Список изменений: '.nl2br(htmlspecialchars($config['descr'])), 'post');
			$data = unserialize(file_get_contents(H . 'engine/files/data/secwind.db'));
			$data['version'] = $config['version'];
			
			if (!empty($config['secwind']))
			{
				$data = array_merge($data, $config['secwind']);
			}
			
			file_put_contents(H . 'engine/files/data/secwind.db', serialize($data));
		}

		unset($zip, $config, $headers);
	
		if (isset($error))
			Core::msg_show($error);
	}

	function get_ini($event, &$file)
	{
        Return Rename($file['filename'], tmpDir . 'update.upd');
	}

	switch($act)
	{
		default:
		
		if (
				isset($_SESSION['update_file']) && 
				(
					is_uploaded_file($_SESSION['update_file']) ||
					is_file($_SESSION['update_file'])
				))
			{
				if ($_SESSION['update_with_ftp'])
                {
                    ftp_update($_SESSION['update_file']);
                }
                else
                {
                    update($_SESSION['update_file']);
                }
                
				unset($_SESSION['update_file'], $_SESSION['update_with_ftp']);
			}
		
			echo '<div class="link">Ваша версия: '.Core::SecWind('version').'<br />';
		
			$last_version = @json_decode(file_get_contents('http://secwind.ru/?act=get_last_version'), true);

			if (empty($last_version) || (function_exists('json_last_error') && json_last_error()))
			{
                echo '</div>';
				Core::msg_show('Невозможно проверить на обновление');
			}
			else
			{
				echo (version_compare($last_version['last_version'], Core::SecWind('version'), '=')	? 'У вас установлена последняя версия' : 'Последняя: <a href="'.$last_version['link_update'].'">'.$last_version['last_version'] . ' ' .$last_version['status'].'</a>') . '<br />
				Дата выхода: '.Core::time($last_version['time_release']) . '<br />
				Список изменений в версии '.$last_version['last_version'] .': <br /> '.nl2br($last_version['changelog']).'</div>';
            }
            ?>
				<form class="link" method = "post" action="?act=import">
                    Ссылка к zip архиву:<br />
                    <input type="text" name="url" value="http://"/><br />
                    <label><input type="checkbox" name="ftp"/>Установка по <a href="/admin/modules/settings.php">ftp</a> (beta)</label><br />
                    <input value = "Импорт" type="submit" />
                </form>
				<form class="link" method="post" enctype="multipart/form-data" action="?act=upload">
                    Выберите архив. Расширение файла должен быть "<b>zip</b>" и присутствовать файл "<b>update.ini</b>"<br/>
                    <input type="file" name="file"/><br />
                    <label><input type="checkbox" name="ftp"/>Установка по <a href="/admin/modules/settings.php">ftp</a> (beta)</label><br />
                    <input value = "Загрузить" name="save" type="submit" />
                </form>
                <?php
		break;
		
		case 'import':
		if (!empty($_POST['url']))
		{
			$headers = @get_headers($_POST['url'], 1);

			if (!$headers)
				$error = 'Не удалось получить заголовки по указанному адресу';

			elseif ($headers['Content-Type'] != 'application/zip')
				$error = 'Только zip архивы';
        
			else
			{
				@set_time_limit(20);

				$up_file = pathinfo($_POST['url'], PATHINFO_FILENAME);

                file_put_contents(tmpDir . $up_file, file_get_contents($_POST['url']));
				
				pre_update(tmpDir . $up_file);
			}
		}
		break;

		case 'upload':

		if (!empty($_FILES['file']['name']))
		{
			$pathinfo = pathinfo($_FILES['file']['name']);
			if ($pathinfo['extension'] != 'zip')
				$error = 'Только zip архивы';
			else
			{
				$up_file = tmpDir . 'update_'.time().'.zip';
				move_uploaded_file($_FILES['file']['tmp_name'], $up_file);
				pre_update($up_file);
			}
		}
		break;
	}

	?>
    <a href='/admin/?act=about'><div class="link">SecWind</div></a>
    <a href='?'><div class="link">Обновление</div></a>
    <a href='/admin/'><div class="link">Админка</div></a>
    <?php
    include incDir.'foot.php';