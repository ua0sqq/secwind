<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

	$set_mod = parse_ini_file(H . 'engine/files/data/modules.ini');
	$set['title'] = 'Импорт модуля';
    include incDir.'head.php';

	empty($_POST['url']) && !empty($_GET['url']) ? $_POST['url'] = $_GET['url'] : null;

    if (!empty($_POST['url']))
    {
        $headers = @get_headers($_POST['url'], 1);

		if (!$headers)
			$error = 'Не удалось получить заголовки по указанному адресу';
		
		elseif (empty($_GET['url']) && !empty($headers['Content-Length']) && $headers['Content-Length'] >= 2097152)
			$error = 'Размер архива превышает 2 мб, <a href="?url='.htmlspecialchars($_POST['url']).'">продолжить</a>';

		elseif ($headers['Content-Type'] != 'application/zip')
            $error = 'Только zip архивы';
        
		else
		{
            $modul = pathinfo($_POST['url'], PATHINFO_FILENAME);

			if (file_exists(H . 'engine/files/modules/archives/' . $modul))
                $error = 'Такой архив уже есть в списке архивов';
			else
			{
                file_put_contents(tmpDir . $modul, file_get_contents($_POST['url']));
				Core::get('zip');
				$zip = new Pclzip(tmpDir . $modul);
				$name = pathinfo($modul, PATHINFO_FILENAME);
				$contents = $zip->listContent();

				function get_ini($event, &$file)
				{
					Global $name;
                    Return Rename($file['filename'], H.'engine/files/modules/configs/'.$name.'.app');
					//Return 1; 
				}

				function get_icon($event, &$file)
				{
					Global $name;
                    Rename($file['filename'], H.'style/icons/modules/'.$name.'.png');
					Return 1; 
				}
            
				if ($contents == 0)
					$error = 'Архив поврежден или пуст';
				else
				{
					$config = $zip->extract(PCLZIP_OPT_BY_NAME, 'module.ini', PCLZIP_CB_POST_EXTRACT, 'get_ini');
					if (empty($config))
						$error = 'module.ini не найден';
					else
					{
						$icon = $zip->extract(PCLZIP_OPT_BY_NAME, 'icon.png', PCLZIP_CB_POST_EXTRACT, 'get_icon');
						$config = parse_ini_file(H.'engine/files/modules/configs/'.$name.'.app');
						$dir_install = empty($config['dir_install']) || strtolower($config['dir_install']) == 'корень' ? null : $config['dir_install'] . '/';

						if (empty($icon))
							Core::msg_show('Не найдена иконка модуля');
						
						if (empty($config['module_name']) || empty($config['author_name']))
							$error = 'Название модуля или имя автора не указана. Загрузка модуля невозможна';
						elseif ($set_mod['req_un_file'] && (empty($config['file_uninstaller']) || (!array_key_exists(str_replace($dir_install, '', strtok($config['file_uninstaller'], '?')), $contents) && !array_key_exists(strtok($config['file_uninstaller'], '?'), $contents))))
							$error = 'Файл удаления не найден';
						if (!empty($config['secwind_version']) && version_compare($config['secwind_version'], Core::Secwind('version'), '>')) // если версия SecWind не соответстует требованиям модуля 
							$error = 'Модуль подходит для версии SecWind с '.$config['secwind_version'] .', ваша версия - '. Core::Secwind('version');
					}
				}

				if (isset($error))
				{
					if (file_exists(H.'engine/files/modules/configs/'.$name.'.app'))
						unlink(H.'engine/files/modules/configs/'.$name.'.app');
					if (!empty($icon) && file_exists(H.'style/icons/modules/'.$name.'.png'))
						unlink(H.'style/icons/modules/'.$name.'.png');
				}
				else
				{
					$zip->delete(PCLZIP_OPT_BY_NAME, 'module.ini');
					$zip->delete(PCLZIP_OPT_BY_NAME, 'icon.png');
					Rename(tmpDir . $modul, H.'engine/files/modules/archives/'.$modul);
					Core::msg_show('Модуль "'.$name.'" успешно загружен, теперь нужно его <a href="install.php?select='.$name.'">установить</a>', 'menu_razd');
				}
				unset($zip, $config, $headers);
			}
		}
		if (isset($error))
			Core::msg_show($error);
	}

    ?>
    <form method = "post">
        Путь к zip архиву:<br />
        <input type="text" name="url" value="http://"/><br />
        <input value = "Импорт" type="submit" />
    </form>
    <a href='/admin/'><div class="menu_razd">Админка</div></a>
    <?php
    include incDir.'foot.php';