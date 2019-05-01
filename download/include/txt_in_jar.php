<?php

$dir_clean = opendir('time_files/created_java/files');
while ($file = readdir($dir_clean)) {
    if ($file != 'index.php' && $file != '.htaccess' && $file != '.' && $file != '..') {
        $time_file = filemtime('time_files/created_java/files/' . $file);
        if ($time_file < ($time - 300))
            unlink('time_files/created_java/files/' . $file);
    }
}
closedir($dir_clean);
$req_down = $sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();
$format_file = format($res_down['name']);

if ($sql->num_rows() == 0 || !is_file($res_down['dir'] . '/' . $res_down['name']) || $format_file != 'txt' || ($res_down['type'] == 3 && !$admin)) {
    include H.'engine/includes/head.php';   
    echo Core::msg_show('Файл не найден<br /><a href="index.php">К категориям</a>');
    include H.'engine/includes/foot.php';
}

if (isset($_GET['more'])) {
    $more = abs(intval($_GET['more']));
    $req_more = $sql->query("SELECT * FROM `down_more` WHERE `id` = '$more' LIMIT 1");
    $res_more = $sql->fetch();
    $format_file = format($res_more['name']);
    if (!$sql->num_rows() || !is_file($res_down['dir'] . '/' . $res_more['name']) || $format_file != 'txt') {
        include H.'engine/includes/head.php';      
        echo Core::msg_show('Файл не найден<br /><a href="index.php">К категориям</a>');
        include H.'engine/includes/foot.php';
    }
    $down_file = $res_down['dir'] . '/' . $res_more['name'];
    $title_pages = $res_more['rus_name'];
    $txt_file = $res_more['name'];
}
else {
    $down_file = $res_down['dir'] . '/' . $res_down['name'];
    $title_pages = $res_down['rus_name'];
    $txt_file = $res_down['name'];
}

if (!isset($_SESSION['down_' . $id])) {
    $sql->query("UPDATE `down_files` SET `field`=`field`+1 WHERE `id`='$id'");
    $_SESSION['down_' . $id] = 1;
}

$file = str_replace('.' . $format_file, '', $txt_file);
$name = str_replace('.' . $format_file, '', $txt_file);
$tmp = 'time_files/created_java/files/' . $name . '.jar';
$tmp_jad = 'time_files/created_java/files/' . $name . '.jar.jad';
if (!file_exists($tmp)) {
    $midlet_name = mb_substr($res_down['rus_name'], 0, 10);
    $midlet_name = iconv('UTF-8', 'windows-1251', $midlet_name);
    $book_text = file_get_contents($res_down['dir'] . '/' . $res_down['name']);
    $charset_text = strtolower(mb_detect_encoding($book_text, 'UTF-8, windows-1251'));
    if ($charset_text != 'windows-1251')
        $book_text = iconv('utf-8', 'windows-1251', $book_text);
    $files = fopen("time_files/created_java/java/textfile.txt", 'w+');
    flock($files, LOCK_EX);
    $book_name = iconv('UTF-8', 'windows-1251', $res_down['rus_name']);
    $result = PHP_EOL . $book_name . PHP_EOL . PHP_EOL . '----------' . PHP_EOL . PHP_EOL . trim($book_text) . PHP_EOL . PHP_EOL . 'Downloaded from '. $_SERVER['HTTP_HOST'];
    fputs($files, $result);
    flock($files, LOCK_UN);
    fclose($files);
    $manifest_text = 'Manifest-Version: 1.0
MIDlet-1: Файл #' . $id . ', , br.BookReader
MIDlet-Name: $tmp_jad
MIDlet-Vendor: Tadochi
MIDlet-Version: 1.5.3
MIDletX-No-Command: true
MIDletX-LG-Contents: true
MicroEdition-Configuration: CLDC-1.0
MicroEdition-Profile: MIDP-1.0
TCBR-Platform: Generic version (all phones)';
    $files = fopen("time_files/created_java/java/META-INF/MANIFEST.MF", 'w+');
    flock($files, LOCK_EX);
    fputs($files, $manifest_text);
    flock($files, LOCK_UN);
    fclose($files);
    Core::get('zip');
    $archive = new PclZip($tmp);
    $list = $archive->create('time_files/created_java/java', PCLZIP_OPT_REMOVE_PATH, 'time_files/created_java/java');
    if (!file_exists($tmp)) {
        include H.'engine/includes/head.php';
		
        echo Core::msg_show('Ошибка создания JAR файла');
        include H.'engine/includes/foot.php';
    }
}
if (!file_exists($tmp_jad)) {
    $filesize = filesize($tmp);
    $jad_text = 'Manifest-Version: 1.0
MIDlet-1: Файл #' . $id . ', , br.BookReader
MIDlet-Name: Файл #' . $id . '
MIDlet-Vendor: Tadochi
MIDlet-Version: 1.5.3
MIDletX-No-Command: true
MIDletX-LG-Contents: true
MicroEdition-Configuration: CLDC-1.0
MicroEdition-Profile: MIDP-1.0
TCBR-Platform: Generic version (all phones)
MIDlet-Jar-Size: ' . $filesize . '
MIDlet-Jar-URL: ' . $home . '/' . $dir_load . '/' . $tmp;
    $files = fopen($tmp_jad, 'w+');
    flock($files, LOCK_EX);
    fputs($files, $jad_text);
    flock($files, LOCK_UN);
    fclose($files);
}


include H.'engine/includes/head.php';


echo '<div class="p_t"><b>' . text::output($title_pages) . '</b></div>';
echo '<div class="menu">Скачать: <a href="' . text::output($tmp) . '">JAR</a> | <a href="' . text::output($tmp_jad) . '">JAD</a></div>';
echo '<div class="menu">Файлы будут доступны для скачивания в течение 5 минут</div>';
echo '<div class="p_t"><a href="index.php?act=view&amp;id=' . $id . '">К файлу</a></div>';

?>