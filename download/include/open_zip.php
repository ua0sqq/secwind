<?php


$dir_clean = opendir('time_files/open_zip');
while ($file = readdir($dir_clean)) {
    if ($file != 'index.php' && $file != '.htaccess' && $file != '.' && $file != '..') {
        $time_file = filemtime('time_files/open_zip/' . $file);
        if ($time_file < ($time - 300))
            unlink('time_files/open_zip/' . $file);
    }
}
closedir($dir_clean);
$sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();

if ($sql->num_rows() == 0 || !is_file($res_down['dir'] . '/' . $res_down['name']) || ($res_down['type'] == 3 && !$admin)) {
    include H.'engine/includes/head.php';  
	echo 'Файл не найден<br /><a href="index.php">К категориям</a>';
    include H.'engine/includes/foot.php';
}
if (isset($_GET['more'])) {
    $more = abs(intval($_GET['more']));
    $sql->query("SELECT * FROM `down_more` WHERE `id` = '$more' LIMIT 1");
    $res_more = $sql->fetch();
    if (!$sql->num_rows() || !is_file($res_down['dir'] . '/' . $res_more['name'])) {
        include H.'engine/includes/head.php';     
		echo 'Файл не найден<br /><a href="index.php">К категориям</a>';
        include H.'engine/includes/foot.php';
        exit;
    }
    $file_open = $res_down['dir'] . '/' . $res_more['name'];
    $isset_more = '&amp;more=' . $more;
    $title_pages = $res_more['rus_name'];
}
else {
    $file_open = $res_down['dir'] . '/' . $res_down['name'];
    $title_pages = $res_down['rus_name'];
    $isset_more = '';
}

$title_pages = htmlspecialchars(mb_substr($title_pages, 0, 20));
$set['title'] = 'Просмотр архива &raquo; ' . (mb_strlen($res_down['rus_name']) > 20 ? $title_pages . '...' : $title_pages);
include H.'engine/includes/head.php';

require_once H.'engine/classes/zip.php';
$array = array('cgi', 'pl', 'asp', 'aspx', 'shtml', 'shtm', 'fcgi', 'fpl', 'jsp', 'py', 'htaccess', 'ini', 'php', 'php3', 'php4', 'php5', 'php6', 'phtml', 'phps');

if (!isset($_GET['file'])) {
    $zip = new PclZip($file_open);
    if (($list = $zip->listContent()) == 0) {
        echo 'Неудалось открыть архив или выбранный файл не являеться ZIP архивом<br /><a href="index.php?act=view&amp;id=' . $id . '">Назад</a>';
        include H.'engine/includes/foot.php';
        exit;
    }
	
	$list_size = $list_content = $save_list = null;
	
    for ($i = 0; $i < sizeof($list); $i++) {
        for (reset($list[$i]); $key = key($list[$i]); next($list[$i])) {
            $file_size = str_replace("--size:", "", strstr($list_content, "--size"));
            $list_size .= str_replace($file_size, $file_size . '|', $file_size);
            $list_content = "[$i]--$key:" . $list[$i][$key];
            $zip_file = str_replace("--filename:", "", strstr($list_content, "--filename"));
            $save_list .= str_replace($zip_file, $zip_file . '|', $zip_file);
        }
    }
    $file_size_two = explode("|", $list_size);
    echo '<div class="p_m">' . $res_down['name'] . '</div><div class="foot">Вы можете скачать отдельные файлы из этого архива или просмотреть его код</div>';
    $preview = explode("|", $save_list);
    $total = count($preview) - 1;
	$page = new page($total, $set['p_str']);
    $start = $page->start();
	
		
    if ($total > 0) {
        $end = $start + $set['p_str'];
        if ($end > $total)
            $end = $total;
        for ($i = $start; $i < $end; $i++) {
            echo ($i % 2) ? '<div class="p_m">' : '<div class="p_m">';
            $path = $preview[$i];
            $file_name = preg_replace("#.*[\\/]#", '', $path);
            $dir = preg_replace("#[\\/]?[^\\/]*$#", '', $path);
            $format = explode('.', $file_name);
            $format_file = strtolower($format[count($format) - 1]);
            echo '<b>' . ($i + 1) . ')</b> ' . $dir . '/' . mb_convert_encoding($file_name, "UTF-8", "Windows-1251");
            if ($file_size_two[$i] > 0)
                echo ' (' . size_file($file_size_two[$i]) . ')';
            if ($format_file)
                echo ' - <a href="index.php?act=open_zip&amp;id=' . $id . '&amp;file=' . rawurlencode($path) . '&amp;start=' . $start . $isset_more . '">' . (in_array($format_file, $array) ? 'код' : 'скачать') . '</a>';
            echo '</div>';
        }
    }
    else
        echo '<div class="err">Архив пуст</div>';
    echo '<div class="msg">Вес распакованного архива: ' . text::size_data(array_sum($file_size_two)) . '</div>';
    echo '<div class="menu">Всего файлов: ' . $total . '</div>';
    $page->display('index.php?act=open_zip&amp;id=' . $id . '&amp;');
        echo '<p><form action="index.php" method="get"><input type="hidden" value="open_zip" name="act" />
            <input type="hidden" value="' . $id . '" name="id" /><input type="text" name="page" size="2"/><input type="submit" value="К странице &gt;&gt;"/></form></p>';
    
	
}
else {

    $FileName = rawurldecode(trim($_GET['file']));
    $format = explode('.', $FileName);
    $format_file = strtolower($format[count($format) - 1]);
    if (strpos($FileName, '..') !== false or strpos($FileName, './') !== false) {
        echo display_error('<a href="index.php?act=view&amp;id=' . $id . '">К файлу</a>');
        include H.'engine/includes/foot.php';
        exit;
    }
    $FileName = htmlspecialchars(trim($FileName), ENT_QUOTES, 'UTF-8');
    $FileName = strtr($FileName, array('&' => '', '$' => '', '>' => '', '<' => '', '~' => '', '`' => '', '#' => '', '*' => ''));
    $zip = new PclZip($file_open);
    $content = $zip->extract(PCLZIP_OPT_BY_NAME, $FileName, PCLZIP_OPT_EXTRACT_AS_STRING);
    $content = $content[0]['content'];
    $FileName = preg_replace("#.*[\\/]#si", "", $FileName);
    if (in_array($format_file, $array)) {
        $content_two = explode("\r\n", $content);
        echo '<div class="title"><b>' . mb_convert_encoding($FileName, "UTF-8", "Windows-1251") . '</b></div><div class="list1"><div class="phpcode">';
        $rus_simvol = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К',
            'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я');
        for ($i = 0; $i < 66; $i++) {
            if (strstr($content, $rus_simvol[$i]) !== false)
                $UTF = 1;
        }
        $php_code = trim($content);
        $php_code = substr($php_code, 0, 2) != "<?" ? "<?php\n" . $php_code . "\n?>" : $php_code;
        echo isset($UTF) ? highlight_string($php_code, true) : highlight_string(iconv('windows-1251', 'utf-8', $php_code), true);
        echo '</div></div><div class="menu">Всего строк: ' . count($content_two) . '</div>';
    }
    else {
        $NewNameFile = strtr(htmlspecialchars(mb_convert_encoding($FileName, "UTF-8", "Windows-1251")), array(' ' => '_', '@' => '', '%' => ''));
        if (file_exists('time_files/open_zip/' . $NewNameFile)) {
            header('Location: time_files/open_zip/' . $NewNameFile);
            exit;
        }
        $NewFile = 'time_files/open_zip/' . $NewNameFile;
        $dir = @fopen($NewFile, "wb");
        if ($dir) {
            if (flock($dir, LOCK_EX)) {
                fwrite($dir, $content);
                flock($dir, LOCK_UN);
            }
            fclose($dir);
            header('Location: time_files/open_zip/' . $NewNameFile);
        }
        else
            echo display_error('Не удалось сохранить файл на сервере');

    } //echo '<a href="index.php?act=open_zip&amp;id=' . $id . '&amp;page=' . ($page + 1) . '">Назад</a><br />';
	}

echo '<a href="index.php?act=view&amp;id=' . $id . '">К файлу</a>';

include H.'engine/includes/foot.php';