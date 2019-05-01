<?php
    /**
    *@author Flyself
    */
	
    include '../engine/includes/start.php';
	
	if (!$user_id)
	    Core::stop();
	
	$do = isset($_GET['do'])? $_GET['do'] : false;
	$error = false;
	$dir_load = 'download';
	$filesroot = '../' . $dir_load;
	$screenroot = $filesroot . '/screen';
	$loadroot = $filesroot . '/files';
	$set_down = array('mod' => 1, 'theme_screen' => 1, 'top' => 25);
	$old = $time - 259200;
	$array = array('add_cat', 'down_file', 'view', 'open_zip', 'jad_file', 'txt_in_jar', 'txt_in_zip', 'edit_about', 'edit_screen', 'file_more', 'load_file', 'del_file', 'comms', 'new_files', 'top_files', 'comms_all', 'recount', 'edit_cat', 'del_cat', 'scan_dir', 'mod_files', 'search');
	
    Core::get(array('text.class', 'page.class', 'cache.class'), 'classes');

	function format($file)
	{
	    return pathinfo($file, PATHINFO_EXTENSION);
	}
	
	function display_error($msg)
	{
	    return '<div class="err">'.$msg.'</div>';
	}
	
	function check($str)
	{
	    return my_esc($str, true);
	}
	
	function show_file($res_down = array()) 
	{	global $filesroot;	$out = '';
	$f1 = strrpos($res_down['name'], ".");
    $f2 = substr($res_down['name'], $f1 + 1, 999);
    $format_file = strtolower($f2);
    if ($format_file == 'jpg' || $format_file == 'jpeg' || $format_file == 'gif' || $format_file == 'png') {
        $preview = $res_down['dir'] . '/' . $res_down['name'];
    }
	/*    else
        if ($format_file == 'thm' || $format_file == 'nth') {
            if (is_file('screen/' . $res_down['id'] . '.gif')) {
                $preview = 'screen/' . $res_down['id'] . '.gif';
            }
            else {
                $preview = 'images/easy.gif';
            }
        }*/
    if (isset($preview)) {
        //$out = '<img src="preview.php?type=1&amp;img=' . urlencode($preview) . '" alt="preview" />';
        $out = '<img src="'.htmlspecialchars($preview) . '" width="10%"  alt="preview" />';
    }
    $out .= '<img src="' . $filesroot . '/images/' . (file_exists($filesroot . '/images/' . $format_file . '.png') ? $format_file . '.png' : 'file.gif') . '" alt="file" /> ';
    $out .= '<a href="?act=view&amp;id=' . $res_down['id'] . '">' . text::output($res_down['rus_name']) . '</a> (' . $res_down['field'] . ')';
    if (is_file('about/' . $res_down['id'] . '.txt')) {
        $about = file_get_contents('about/' . $res_down['id'] . '.txt');
        if (mb_strlen($about) > 100)
            $about = iconv('UTF-8', 'UTF-8//IGNORE', mb_substr($about, 0, 90, 'utf-8')) . '...';
        $out .=  '<br />'.text::output($about);
    }
    $out .= '<div class="status"><a href="index.php?act=comms&amp;id=' . $res_down['id'] . '">Комментарии</a> (' . $res_down['total'] . ')</div>';
    return $out;
	}

	if (in_array($act, $array) && file_exists('include/' . $act . '.php')) {
    require_once (H.'download/include/' . $act . '.php');
	}
	else {
	
	$tree = array();
	$sql->query("SELECT * FROM `down_files` WHERE `type` = 1 AND `id` = '$id' LIMIT 1");
    $cat = $sql->num_rows();
    $res_down_cat = $sql->fetch();
	$way = null;
        $dirid = $id;
        while ($dirid != '0' && $dirid != "") {
            $res_down = $sql->query("SELECT * FROM `down_files` WHERE `type` = 1 AND `id` = '$dirid' LIMIT 1")->fetch();
            $tree[] = '<a href="index.php?id=' . $dirid . '">' . text::output($res_down['rus_name']) . '</a>';
            $dirid = $res_down['refid'];
        }
        krsort($tree);
       //$way = implode(',', $tree);
		
	//$set['meta_keywords'] = $res_down_cat['rus_name'];
	//$set['meta_description']= strip_tags($way);
	$set['title'] = 'Загрузки' . ($id ? ' / ' . $res_down_cat['rus_name'] : null);
	
	include H.'engine/includes/head.php';
    
    $cache = new cache(H.'engine/files/tmp/download[dir='.$id.';page='.(isset($_GET['page']) ? intval($_GET['page']) : 1).'].swc');
    
    if (!$cache->life())
    {
        ob_start();

    if ($id) {
        if ($cat == 0 || !is_dir($res_down_cat['dir'] . '/' . $res_down_cat['name'])) {
            // Если неправильно выбран каталог, выводим ошибку
            echo 'Каталог не существует<br /><a href="index.php">К категориям</a>';
			include H.'engine/includes/foot.php';
        }
        $title_pages = mb_substr($res_down_cat['rus_name'], 0, 30);
        $textl = mb_strlen($res_down_cat['rus_name']) > 30 ? $title_pages . '...' : $title_pages;

        $cdir = array_pop($tree);
        echo '<div class="p_m"><a href="index.php?">Загрузки</a> &raquo; ';
        echo implode(',', $tree);
        echo '<b>' . strip_tags($cdir) . '</b></div>';
		$total_new = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = '2'  AND `time` > $old AND `dir` LIKE '" . ($res_down_cat['dir'] . '/' . $res_down_cat['name']) . "%'")->result();
        if($total_new)
            echo '<div class="menu_razd"><a href="index.php?act=new_files&amp;id=' . $id . '">Новые файлы</a> (' . $total_new . ')</div>';
    }
    else {
        echo '<div class="fmenu">Загрузки</div>';
        $total_mod = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = '3'")->result();
        if($total_mod)
            echo '<div class="err"><a href="index.php?act=mod_files">Файлы на модерации</a> (' . $total_mod . ')</div>';
        $total_new = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = '2'  AND `time` > $old")->result();
        if($total_new)
            echo '<div class="p_t"><a href="index.php?act=new_files">Новые файлы</a> (' . $total_new . ')</div>';
            echo '
                <div class="menu_razd"><a href="index.php?act=top_files&amp;id=0">Популярные</a> | ',
                '<a href="index.php?act=top_files&amp;id=1">Самые комментируемые</a> | ',
                '<a href="index.php?act=comms_all">Обзор комментариев</a></div>';
    }
    // Подсчитываем число папок и файлов
    
    $total_cat = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `refid` = '$id' AND `type` = 1")->result();
    $total_files = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `refid` = '$id' AND `type` = 2")->result();
    $sum_total = $total_files + $total_cat;
    $page = new page($sum_total, $set['p_str']);

    if ($sum_total) {        // Сортировка
		
		$_SESSION['sort_down'] = $_SESSION['sort_down2'] = 0;
		
        if (isset($_POST['sort_down']))
            $_SESSION['sort_down'] = $_POST['sort_down'] ? 1 : 0;
        if (isset($_POST['sort_down2']))
            $_SESSION['sort_down2'] = $_POST['sort_down2'] ? 1 : 0;
        $sql_sort = $_SESSION['sort_down'] ? '`name`' : '`time`';
        $sql_sort .= $_SESSION['sort_down2'] ? ' ASC' : ' DESC';
		
			
        $sql->query("SELECT * FROM `down_files` WHERE `refid` = '$id' AND `type` < 3 ORDER BY `type` ASC, $sql_sort LIMIT ".$page->limit());
        $i = 1;
        //Выводим список папок и файлов
        while ($res_down = $sql->fetch()) {
            echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
            //$preview = '';
            if ($res_down['type'] == 1) {
                echo '<img src="' . $filesroot . '/images/cat.gif" alt="cat" /> <a href="index.php?id=' . $res_down['id'] . '">' . text::output($res_down['rus_name']) . '</a> (' . $res_down['total'] . ')';
                if ($res_down['field'])
                    echo '<div class="status">Расширения для выгрузки: ' . $res_down['text'] . '</div>';
            }
            else
                echo show_file($res_down);
            echo '</div>';
            ++$i;
        }
    }
    //else 
      //  echo '<div class="err">В данной категории нет файлов</div>';
    
    if ($id)
    {
    echo '<div class="sub">Загрузки / ';
    if ($total_cat > 0)
        echo 'Папок: ' . $total_cat;
    echo '&nbsp;&nbsp;';
    if ($total_files > 0)
        echo 'Файлов: ' . $total_files;
    echo '</div>';
    }
    // Постраничная навигация
    $page->display('index.php?id=' . $id . '&amp;');
    $cache->write();
    }
    echo $cache->read();
    

    if ($moder) {
        // Выводим ссылки на модерские функции
        echo '<div class="menu">';
        echo '<a href="index.php?act=recount&amp;cat&amp;id=' . $id . '">Пересчитать файлы в папке</a><br /><a href="index.php?act=recount">Пересчитать файлы в ЗЦ</a><br /><a href="index.php?act=add_cat&amp;id=' . $id . '">Создать папку</a><br/>';
        if ($id) {
            $del_cat = $sql->query("SELECT COUNT(*) FROM `down_files` WHERE `type` = 1 AND`refid` = '$id'")->result();
            if (!$del_cat) {
                echo '<a href="index.php?act=del_cat&amp;id=' . $id . '">Удалить папку</a><br/>';
            }
            echo '<a href="index.php?act=edit_cat&amp;id=' . $id . '">Изменить папку</a><br/>';
            //echo "<a href='?act=import&amp;cat=" . $cat . "'>Импорт файла</a><br/>";
            echo '<a href="index.php?act=down_file&amp;id=' . $id . '">Выгрузить файл</a><br />';
        }
        echo '<a href="index.php?act=scan_dir&amp;id=' . $id . '">Обновить</a> ' . ($id ? '| <a href="index.php?act=scan_dir">ЗЦ</a>' : '') . ' <br/><a href="index.php?act=scan_dir&amp;do=clean&amp;id=' . $id . '">Очистка БД от мусора</a>';
        echo'</div>';

    }
    else
        if ($id)
            echo '<div class="menu"><a href="index.php?act=down_file&amp;id=' . $id . '">Выгрузить файл</a></div>';
    echo '<div class="post"><a href="index.php?act=search">Поиск</a>';
    if (!empty($cat))
        echo '<br /><a href="index.php">В загрузки</a>';
    echo '</div>';
}

include H.'engine/includes/foot.php';