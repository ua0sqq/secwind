<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();
    $set['title'] = 'Поиск левого аккунта';
    include incDir . 'head.php';
    Core::get('page.class');

    /**
    * Написан 25.06.12
    */

    $ip = !empty($_GET['ip']) ? $_GET['ip'] : false;
    $ip2 = !empty($_GET['ip2']) ? $_GET['ip2'] : false;

	if ($ip && substr_count($ip, '.'))
	{
		
		/**
		* Если поиск по диапазону
		*/
			
		if ($ip2)
		{
		    $total = $sql->query('SELECT COUNT(*) FROM `user` WHERE `ip` between '.ip2long($ip).' and '.ip2long($ip2))->result();
            $page = new page($total, $set['p_str']);
            $query = $sql->query('SELECT `id`,`nick`,`pol`,`date_reg`,`date_last`,`ip`,`ua`FROM `user` WHERE `ip` between '.ip2long($ip).' and '.ip2long($ip2) .' limit '.$page->limit());
		}
		else
		{
			$total = $sql->query('SELECT COUNT(*) FROM `user` WHERE `ip`='.ip2long($ip))->result();
            $page = new page($total, $set['p_str']);
            $query = $sql->query('SELECT `id`,`nick`,`pol`,`date_reg`,`date_last`,`ip`,`ua` FROM `user` WHERE `ip`='.ip2long($ip) .' limit '.$page->limit());
		}

		if (!$sql->num_rows())
		{
			echo '<div class="err">Никого нет по заданному ip</div>';
		}
			
		while ($ank = $sql->fetch())
	    {
			echo
			    '<div class="p_t">'.
			    Core::user_show($ank) .
				'Регистрация: '.Core::time($ank['date_reg']).'<br />'.
				'Посл. посещение: '.Core::time($ank['date_last']).'<br />'.
				'IP: '.long2ip($ank['ip']).'&nbsp; [<a href="./ban_ip.php?min='.$ank['ip'].'">Бан</a>]<br />'.
				'UA: '.$ank['ua'].'</div>';
		}
	    $page->display('?ip='.htmlspecialchars($ip).'&amp;' . ($ip2 ? 'ip2='.htmlspecialchars($ip2) : null) . '&amp;');
	}

	?>
	<form class="post">
        Поиск по ip:
		<br />
		IP (начальный диапазон):
		<br />
		<input type="text" name="ip" value="<?=htmlspecialchars($ip)?>"/>
		<br />
		IP (конец. не обязательно):
		<br />
		<input type="text" name="ip2" value="<?=htmlspecialchars($ip2)?>"/>
		<br />
		<button>
		Найти
		</button>
	</form>
	<a href='/admin/?act=security'><div class="link">Безопасность</div></a>
    <a href='/admin/'><div class="link">Админка</div></a>
	<?php
    include incDir.'foot.php';