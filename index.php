<?php
    include 'engine/includes/start.php';
    
	if (isset($_GET['set_theme']) && ($_GET['set_theme'] == 'Wap' || $_GET['set_theme'] == 'Web'))
	{
		$tmp_theme = $_GET['set_theme'] == 'Wap' ? 'default' : 'web';
		setcookie('set_theme',$tmp_theme , 0, '/');
		$_COOKIE['set_theme'] = $tmp_theme;
	}
	
	include 'engine/includes/head.php';
    
	$cache = new cache(tmpDir . 'index_page.swc');
	if (!$cache->life())
	{
		ob_start();
		$res = mysqli_query($sql->db, 'select * from `main_menu` order by `pos`');
		while($menu = $sql->fetch($res))
		{
            $menu['icon'] = str_replace('{theme}', $set['theme'], $menu['icon']);
			if (empty($menu['link'])) // раздел
            {
                echo 
                    '<div class="menu_razd">',
                    (!empty($menu['icon']) ? '<img src="'.$menu['icon'].'" alt=""/>  ' : ''),
                    $menu['name'].'</div>';
                
                if (!empty($menu['file']) && is_file(H . $menu['file']))
                {
                    include H . $menu['file'];
                }
            }
            else // ссылка
            {
                echo 
                    '<a href="'.$menu['link'].'" class="link">',
                    (!empty($menu['icon']) ? '<img src="'.$menu['icon'].'" alt=""/>  ' : ''),
                    $menu['name'].
                    (!empty($menu['file']) && is_file(H . $menu['file']) ? include H . $menu['file'] : null),
                    '</a>';
            }
		}
		$cache->write();
		unset($res, $menu);
	}
	echo $cache->read();

	unset($cache);
	$sql->free();

	include 'engine/includes/foot.php';