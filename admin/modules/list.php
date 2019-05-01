<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Установленные модули';
    include incDir.'head.php';

	if (isset($_GET['select']))
	{
		$module = $sql->query("select * from `modules` where `name` = '".my_esc(urldecode($_GET['select']))."'")->fetch();
		if (!$module)
		    Core::msg_show('Модуль не найден');
		else
		{
			Core::get('cache.class');
			$cache = new cache(tmpDir . 'installed_modules[name='.$_GET['select'].'].swc');
			if (!$cache->life(9999999999999999))
			{
				ob_start();
				$base = null;
				$set_mod = parse_ini_file(H . 'engine/files/data/modules.ini');

				if ($set_mod['check_author'])
				{
					Core::get('Banbase');
					Core::get('GixSuApi', 'functions');
					!empty($module['author_e-mail']) ? $search['email'] = $module['author_e-mail'] : null;
					!empty($module['author_icq']) ? $search['icq'] = $module['author_icq'] : null;
					$search['wmid'] = !empty($module['author_wmid']) ? $module['author_wmid'] : null;
					$base = Banbase::search_arr($search);
					foreach($base as $arr => $val); // Надеюсь класс подправят

					$gix = GixSuApi($search['wmid']);
				}

			echo '<div class="post"><table><tr><td style="width:15%"><img src="/style/icons/modules/' . (file_exists(H . 'style/icons/modules/'.$_GET['select'] .'.png') ? htmlspecialchars($_GET['select']) : '0') .'.png"/></td><td><span class="status">Название модуля:</span> '.
					(!empty($module['ru_name']) ? $module['ru_name'] . ' ('.$module['name'] . ')'  : $module['name']) . '<br />'.
					(!empty($module['desc']) ? '<span class="status">Описание:</span> '.nl2br($module['desc']).'<br />' : '') .
					(!empty($module['version']) ? '<span class="status">Версия:</span> '.$module['version'].'<br />' : '').'</td></tr></table><span class="status">Автор:</span> '.$module['author_name'].'<br />'.
					(!empty($base) ? '<div class="p_t">'.$module['author_name'] . ' находится в черном списке Banbase.ru<br />Добавил '.$val['admin'].' на сайт '.$val['url'] . ' <br />'. $val['descr'].'</div>' : '') .
					(!empty($gix) && $gix['retval'] == 0 ? '<div class="p_m">Рейтинг автора на торговой площадке Gix.su: '.$gix['rating'].'<br /><a href="'.$gix['link'].'">Другие работы '.$gix['nick'].'</a></div>' : '') .
					(!empty($module['author_e-mail']) ? '<span class="status">E-mail:</span> '.$module['author_e-mail'].'<br />' : '') .
					(!empty($module['author_icq']) ? '<span class="status">ICQ:</span> <a href="http://www.icq.com/people/'.$module['author_icq'].'/">'.$module['author_icq'].'</a><br />' : '') .
					(!empty($module['author_wmid']) ? '<span class="status">WMID:</span> <a href="http://passport.webmoney.ru/asp/certview.asp?wmid='.$module['author_wmid'].'">'.$module['author_wmid'].'</a><br />' : '').'</div>'.
					(!empty($module['uninstaller']) ? '<a href="/'.$module['uninstaller'].'"><div class="link">Удалить</div></a>' : '<div class="menu_razd">Удаление вручную</div>');
				$cache->write();
			}
			echo $cache->read();
		}
	}
	else
	{
		Core::get('page.class');

		$total = $sql->query('select count(*) from `modules`')->result();
		$page = new page($total, $set['p_str']);
		$sql->query('select `name`, `ru_name`, `version` from `modules` order by `name` desc limit '.$page->limit());

		if ($total)
		{
			while($module = $sql->fetch())
			{
				echo '
					<table class="post" style="width:100%"><tr><td style="width:7%">
					<img width="70%" src="/style/icons/modules/' . (file_exists(H . 'style/icons/modules/'. $module['name'] .'.png') ? $module['name'] : '0') .'.png"/></td><td><a href="?select='.$module['name'].'">'
					.(!empty($module['ru_name']) ? $module['ru_name'] : $module['name']) .'</a>'
					.(!empty($module['version']) ? '<span class="status" style="float:right;font-size:smaller">'.$module['version'].'</span>' : '')
					.(!empty($module['desc']) ? '<br />' . $module['desc']: '').'</td></tr></table>';
			}

			$page->display('?');
		}
		else
		{
			Core::msg_show('Нет установленных модулей');
		}
	}

	echo '<a href="/admin/?act=modules"><div class="link">Модули</div></a><a href="/admin/"><div class="link">Админка</div></a>';
    include incDir.'foot.php';