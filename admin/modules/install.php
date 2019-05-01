<?php
    include '../../engine/includes/start.php';

    if (!$creator)
        Core::stop();

	$set_mod = parse_ini_file(H . 'engine/files/data/modules.ini');
	$set['title'] = 'Установка модулей';
    include incDir.'head.php';

	if (isset($_GET['select']))
	{
		if (!file_exists(H . 'engine/files/modules/configs/'.$_GET['select'].'.app'))
			Core::msg_show('Модуль не найден');
		else
		{
			if (isset($_GET['uninstall']))
			{
				unlink(H.'engine/files/modules/configs/'.$_GET['select'].'.app');
				unlink(H.'engine/files/modules/archives/'.$_GET['select'].'.zip');
				unlink(H.'style/icons/modules/'.$_GET['select'].'.png');
				echo '
					<a href="/admin/?act=modules"><div class="link">Модули</div></a>
					<a href="/admin/modules/install.php"><div class="link">Установить модули</div></a>
					<a href="/admin/"><div class="link">Админка</div></a>';
				include H . 'engine/includes/foot.php';
			}
			$module = array_map('htmlspecialchars', parse_ini_file(H . 'engine/files/modules/configs/'.$_GET['select'].'.app'));
			if (isset($_GET['install']))
			{
				$dir_install = empty($module['dir_install']) ? H : H . $module['dir_install'] . '/';
                
                $dir_pre_install = empty($module['dir_install']) ? tmpDir : tmpDir . $module['dir_install'] . '/';
                
                $ftp_dir_ins = str_replace(H, '', $dir_install);
                $ftp_dir_pre = str_replace(H, '', $dir_pre_install);
                
				if (!is_dir($dir_install) && !mkdir($dir_install))
					Core::msg_show('Возникла проблема с папкой для установки');
				else
				{
					Core::get('zip');
                    
					$zip = new Pclzip(H . 'engine/files/modules/archives/'.$_GET['select'].'.zip');
                    if (!empty($ftp_data['ftp_host']))
                    {
                        Core::get('ftp');
                        $ftp_data = parse_ini_file(H . 'engine/files/data/modules.ini');
                        $ftp = new ftp;
                        if (!$ftp->connect($ftp_data['ftp_host'], $ftp_data['ftp_user'], $ftp_data['ftp_pass']))
                        {
                            echo "Ошибка соединения через ftp: ";
                            print_r($ftp->error_no);
                            print_r($ftp->error_msg);
                            $zip->extract(PCLZIP_OPT_SUBSTITUE_FILE, $dir_install);
                        }
                        else
                        {
                            @mkdir($dir_pre_install, 0777);
                    
                            $zip->extract(PCLZIP_OPT_SUBSTITUE_FILE, $dir_pre_install);
            
                            foreach($zip->listContent() as $file)
                            {
                                if ($file['folder'])
                                {
                                    if (!is_dir($dir_install . $file['filename']))
                                    {
                                        mkdir($dir_install . $file['filename'], 0777);
                                    }
                                }  
                                else
                                {
                                    //$fil = $zip->extract(PCLZIP_OPT_BY_NAME, $file['filename'], PCLZIP_OPT_EXTRACT_AS_STRING);
                    
                                    $ftp->put($ftp_dir_ins. $file['filename'], $dir_pre_install . $file['filename']); //, $fil[0]['content']))
                                    unlink($dir_pre_install . $file['filename']);
                                }
                            }
                        }
                    }
                    else
                    {
                        $zip->extract(PCLZIP_OPT_SUBSTITUE_FILE, H);
                    }
                        
                    Core::msg_show('Модуль установлен', 'menu_razd');

					if (!empty($module['file_sql']))
					{
						$sql->from_file($dir_install . $module['file_sql']);
						$sql->free(true);
						Core::msg_show('Сделаны запросы из файла ' .$module['file_sql'], 'menu_razd');
						unlink($dir_install . $module['file_sql']);
					}

					if (!empty($module['dir_chmod']))
					{
						$dirs = explode(' ', $module['dir_chmod']);
						foreach($dirs as $dir)
						{
							chmod(H . $dir, 0777);
						}
						Core::msg_show('Выставлены права доступа 777 на необходимые папки', 'menu_razd');
					}

					mysqli_query($sql->db, "INSERT INTO `modules` SET 
					`name` = '".my_esc($module['module_name'])."',
					`ru_name` = '".(!empty($module['module_runame']) ? my_esc($module['module_runame']) : '')."',
					`desc` = '".(!empty($module['module_desc']) ? my_esc($module['module_desc']) : '')."',
					`version` = '".(!empty($module['module_version']) && is_numeric($module['module_version']) ? $module['module_version'] : '')."',
					`uninstaller` = '".(!empty($module['file_uninstaller']) ? my_esc($module['file_uninstaller']) : '')."',
					`author_name` = '". my_esc($module['author_name'])."',
					`author_e-mail` = '".(!empty($module['author_e-mail']) ? my_esc($module['author_e-mail']) : '')."',
					`author_icq` = '".(!empty($module['author_icq']) ? intval($module['author_icq']) : '')."',
					`author_wmid` = '".(!empty($module['author_wmid']) ? intval($module['author_wmid']) : '')."'");


					unlink(H.'engine/files/modules/configs/'.$_GET['select'].'.app');
					unlink(H.'engine/files/modules/archives/'.$_GET['select'].'.zip');
					if (file_exists(H.'style/icons/modules/'.$_GET['select'].'.png'))
						Rename(H.'style/icons/modules/'.$_GET['select'].'.png', H.'style/icons/modules/'.$module['module_name'].'.png');

					echo '<a href="/admin/?act=modules"><div class="link">Модули</div></a><a href="/admin/"><div class="link">Админка</div></a>';
					include H . 'engine/includes/foot.php';
				}
			}

			Core::get('cache.class');
			$cache = new cache(tmpDir . 'modules[name='.$_GET['select'].';screens='.(isset($_GET['screens']) ? 1 : 0).'].swc');
			if (!$cache->life(9999999999999999))
			{
				ob_start();
				$base = null;

				if ($set_mod['check_author'] == 1)
				{
					Core::get('Banbase');
					Core::get('GixSuApi', 'functions');
					$search['wmid'] = !empty($module['author_wmid']) ? $module['author_wmid'] : null;
					!empty($module['author_e-mail']) ? $search['email'] = $module['author_e-mail'] : null;
					!empty($module['author_icq']) ? $search['icq'] = $module['author_icq'] : null;
					$base = Banbase::search_arr($search);
					foreach($base as $arr => $val); // Надеюсь класс подправят

					$gix = GixSuApi($search['wmid']);
				}

				echo '<div class="post"><table><tr><td style="width:15%">'.
					'<img src="/style/icons/modules/' . (file_exists(H . 'style/icons/modules/'.$_GET['select'] .'.png') ? htmlspecialchars($_GET['select']) : '0') .'.png"/></td><td><span class="status">Название модуля:</span> '.
					(!empty($module['module_runame']) ? $module['module_runame'] . ' ('.$module['module_name'] . ')'  : $module['module_name']) . '<br />'.
					(!empty($module['secwind_version']) ? '<span class="status">Для версии secwind:</span> '.$module['secwind_version'] : '<span class="status">Подходит для всех версий</span>') . '<br />'.
					(!empty($module['module_desc']) ? '<span class="status">Описание:</span> '.nl2br($module['module_desc']).'<br />' : '') .
					(!empty($module['module_version']) ? '<span class="status">Версия:</span> '.$module['module_version'].'<br />' : '').'</td></tr></table>';

					if (!empty($module['module_screens']))
					{
						$imgs = explode(' ', $module['module_screens']);
						$img_all = count($imgs);

						if (isset($_GET['screens']))
						{
							$i = 1;
							foreach ($imgs as $img)
							{
								echo '<a href="http://dbwap.ru/'.$img.'"><img src="http://dbwap.ru/'.$img.'.png" width="30%"/></a>'.($img_all <= 3 ? ' . ' : '<br  />');
								if ($i++ == 5)
									break;
							}
							echo '<br /><a href="?select='.$_GET['select'].'">Скрыть скриншоты</a><br />'; // Не дыра 
						}
						else
							echo '<a href="?select='.$_GET['select'].'&amp;screens">Скриншоты ('.$img_all.')</a><br />';
					}

				echo 
					'<span class="status">Автор:</span> '.$module['author_name'].'<br />'.
					(!empty($base) ? '<div class="news">'.$module['author_name'] . ' находится в черном списке Banbase.ru<br />Добавил '.$val['admin'].' на сайт '.$val['url'] . ' <br />'. $val['descr'].'</div>' : '') .
					(!empty($gix) && $gix['retval'] == 0 ? '<div class="p_m">Рейтинг автора на торговой площадке Gix.su: '.$gix['rating'].'<br /><a href="'.$gix['link'].'">Другие работы '.$gix['nick'].'</a></div>' : '') .
					(!empty($module['author_e-mail']) ? '<span class="status">E-mail:</span> '.$module['author_e-mail'].'<br />' : '') .
					(!empty($module['author_icq']) ? '<span class="status">ICQ:</span> <a href="http://www.icq.com/people/'.$module['author_icq'].'/">'.$module['author_icq'].'</a><br />' : '') .
					(!empty($module['author_wmid']) ? '<span class="status">WMID:</span> <a href="http://passport.webmoney.ru/asp/certview.asp?wmid='.$module['author_wmid'].'">'.$module['author_wmid'].'</a><br />' : '') .
					(!empty($module['file_uninstaller']) ? '<span class="status">Файл удаления:</span> есть' : '<span class="status">Файл удаления:</span> вручную') . '<br />' .
					(!empty($module['file_sql']) ? '<span class="status">Файл SQL:</span> да<br />' : '') . 
					(!empty($module['dir_install']) ? '<span class="status">Установка в папку:</span> '.$module['dir_install'] : '<span class="status">Установка в:</span> корень') . '<br />'.
					(!empty($module['dir_chmod']) ? '<span class="status">Chmod в:</span> '.$module['dir_chmod'].'<br />' : '') .'</div>';
                echo '<div class="post">Установка будет через <a href="settings.php">ftp</a>, настройте данные</div>';
				echo '<a href="?select='.$_GET['select'].'&amp;install"><div class="link">Установить</div></a><a href="?select='.$_GET['select'].'&amp;uninstall"><div class="link">Удалить</div></a>';
				$cache->write();
			}
			echo $cache->read();
		}
	}
	else
	{
		$modules = opendir(H . 'engine/files/modules/configs/');

		echo '<div class="menu_razd">Выберите модуль, который хотели бы установить</div>';

		while ($modul = readdir($modules))
		{
			if ($modul == '.' || $modul == '..')
				continue;

			$name = pathinfo($modul, PATHINFO_FILENAME);
			$module = array_map('htmlspecialchars', parse_ini_file(H . 'engine/files/modules/configs/'.$name.'.app'));
			echo '
				<table class="post" style="width:100%"><tr>
				<td style="width:7%">
				<img width="70%" src="/style/icons/modules/' . (file_exists(H . 'style/icons/modules/'. $name .'.png') ? $name : '0') .'.png"/>
				</td>
				<td><a href="?select='.$name.'">'
				.(!empty($module['module_runame']) ? $module['module_runame'] : $module['module_name']) .'</a> '
				.(!empty($module['module_version']) ? '<span class="status" style="float:right;font-size:smaller">'.$module['module_version'].'</span>' : '')
				.(!empty($module['module_desc']) ? '<br />' . $module['module_desc']: '').'</td></tr></table>';
		}
	}

	echo '<a href="/admin/?act=modules"><div class="link">Модули</div></a><a href="/admin/"><div class="link">Админка</div></a>';

    include incDir.'foot.php';