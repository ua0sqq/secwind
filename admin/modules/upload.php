<?php
    include '../../engine/includes/start.php';

    if (!$creator)
        Core::stop();

	$set_mod =parse_ini_file(H . 'engine/files/data/modules.ini');
	$set['title'] = 'Загрузка модулей';
    include incDir.'head.php';

    if (!empty($_FILES['file']['name']))
    {
        $pathinfo = pathinfo($_FILES['file']['name']);
		if ($pathinfo['extension'] != 'zip')
            $error = 'Только zip архивы';
		elseif (file_exists(H . 'engine/files/modules/archives/'.$_FILES['file']['name']))
            $error = 'Такой архив уже есть';
        else
        {
			Core::get('zip');

			function get_ini($event, &$file)
            {
				Global $pathinfo;
				Return Rename($file['filename'], H.'engine/files/modules/configs/'.$pathinfo['filename'].'.app');
            }

			function get_icon($event, &$file)
            {
				Global $pathinfo;
				Rename($file['filename'], H.'style/icons/modules/'.$pathinfo['filename'].'.png');
				Return 1; 
            }

			$zip = new Pclzip($_FILES['file']['tmp_name']);
			$contents = $zip->listContent();
            
			if ($contents == 0)
                $error = 'Архив поврежден или пуст';
			{
				$config = $zip->extract(PCLZIP_OPT_BY_NAME, 'module.ini', PCLZIP_CB_POST_EXTRACT, 'get_ini');
				if (empty($config))
					$error = 'module.ini не найден';
				{
					$icon = $zip->extract(PCLZIP_OPT_BY_NAME, 'icon.png', PCLZIP_CB_POST_EXTRACT, 'get_icon');
					$config = parse_ini_file(H.'engine/files/modules/configs/'.$pathinfo['filename'].'.app');
					$dir_install = empty($config['dir_install']) || strtolower($config['dir_install']) == 'корень' ? null : $config['dir_install'] . '/';

					if (empty($icon))
						Core::msg_show('Не найдена иконка модуля');

					if (empty($config['module_name']) || empty($config['author_name']))
					{
						$error = 'Название модуля или имя автора не указана. Загрузка модуля невозможна';
					}
					elseif ($set_mod['req_un_file'] && (empty($config['file_uninstaller']) || (!array_key_exists(str_replace($dir_install, '', strtok($config['file_uninstaller'], '?')), $contents) && !array_key_exists(strtok($config['file_uninstaller'], '?'), $contents))))
					{
						$error = 'Файл удаления не найден';
					}

					if (!empty($config['secwind_version']) && version_compare($config['secwind_version'], Core::Secwind('version'), '>')) // если версия SecWind не соответстует требованиям модуля 
					{
						$error = 'Модуль подходит для версии SecWind с '.$config['secwind_version'] .', ваша версия - '. Core::Secwind('version');
					}
				}
			}

			if (isset($error))
			{
				if (file_exists(H.'engine/files/modules/configs/'.$pathinfo['filename'].'.app'))
					unlink(H.'engine/files/modules/configs/'.$pathinfo['filename'].'.app');
				if (file_exists(H.'style/icons/modules/'.$pathinfo['filename'].'.png'))
				    unlink(H.'style/icons/modules/'.$pathinfo['filename'].'.png');
			}
			else
			{
				$zip->delete(PCLZIP_OPT_BY_NAME, 'module.ini');
				$zip->delete(PCLZIP_OPT_BY_NAME, 'icon.png');
				Move_uploaded_file($_FILES['file']['tmp_name'], H.'engine/files/modules/archives/'.$_FILES['file']['name']);
				Core::msg_show('Модуль "'.$_FILES['file']['name'].'" успешно загружен, теперь нужно его <a href="install.php?select='.strtok($_FILES['file']['name'], '.').'">установить</a>', 'menu_razd');
		    }
		    unset($zip, $config);
		}

		if (isset($error))
			Core::msg_show($error);
    }


    ?>
    <form method="post" enctype="multipart/form-data">
        <div class="post">Выберите модуль. Расширение файла должен быть "<b>zip</b>" и присутстовать файл "<b>module.ini</b>"</div>
        <input type="file" name="file"/><br />
        <input value = "Загрузить" name="save" type="submit" />
    </form>
	<a href='/admin/?act=modules'><div class="menu_razd">Модули</div></a>
    <a href='/admin/'><div class="menu_razd">Админка</div></a>
    <?php
    include incDir.'foot.php';