<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

    $set['title'] = 'Службы';
    include incDir.'head.php';
	Core::get('cache.class');

	function __ru_use($string)
	{
		switch($string)
		{
			default:
				return 'Везде';
					break;

			case 'index_page':
				return 'Главная страница';
					break;

			case 'reg':
				return 'Регистрация';
					break;

			case 'auth':
				return 'Авторизация';
					break;

			case 'enrty_admin':
				return 'Вход в админку';
					break;

			case 'exit':
				return 'Выход из аккаунта';
					break;

			case 'anketa':
				return 'Анкета';
					break;
		}
	}

	if (isset($_GET['select']))
	{
		$service = $sql->query("select * from `module_services` where `name` = '".my_esc(urldecode($_GET['select']))."'")->fetch();
		if (!$service)
		    Core::msg_show('Служба не найдена');
		else
		{
			$cache = new cache(tmpDir . 'service[name='.$_GET['select'].'].swc');
			if (!$cache->life(9999999999999999))
			{
				ob_start();
				$service = array_map('htmlspecialchars', $service);

				echo '
					<div class="post">
						<span class="status">Название:</span> '.$service['name'].'<br />
						<span class="status">Модуль:</span> '.($service['belongs'] == 'root' ? 'Система' : $service['belongs']).'<br />
						<span class="status">Файл: </span> '.$service['file'].'<br />
						<span class="status">Место: </span> '.$service['use_in'].' ('.__ru_use($service['use_in']).')<br />
						<span class="status">Описание:</span> '.(!empty($service['desc']) ? $service['desc'] : 'Описания нет').'
					</div>';
					
				$cache->write();
			}
			echo $cache->read();
		}
	}
	else
	{
		$cache = new cache(tmpDir . 'services[page='.(isset($_GET['page']) ? intval($_GET['page']) : 1).'].swc');
		if (!$cache->life())
		{
			ob_start();
			Core::get('page.class');
			$total = $sql->query('select count(*) from `module_services`')->result();
			$page = new page($total, $set['p_str']);
			$sql->query('select `name`, `file`, `belongs`, `use_in` from `module_services` order by `name` desc limit '.$page->limit());

			if ($total)
			{
				while($service = $sql->fetch())
				{
					$service = array_map('htmlspecialchars', $service);
					echo '
					<div class="post"><a href="?select='.$service['name'].'">'.$service['name'].'</a> ('.$service['belongs'].')<br />
					<span class="status">Файл: </span> '.$service['file'].'<br />
					<span class="status">Место: </span> '.__ru_use($service['use_in']).'</div>';
				}
				$page->display('?');
				unset($page, $service, $total);
			}
			else
			Core::msg_show('Нет запущенных служб');
			$cache->write();
		}
		echo $cache->read();
		unset($cache);
	}

	echo '<a href="/admin/modules/services.php"><div class="link">Службы</div></a><a href="/admin/?act=modules"><div class="link">Модули</div></a><a href="/admin/"><div class="link">Админка</div></a>';
    include incDir.'foot.php';