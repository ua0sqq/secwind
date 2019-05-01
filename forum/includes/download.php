<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

if (!$file){
include H . 'engine/includes/head.php';
  echo Core::msg_show('Отсутствует идентификатор файла!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

$sql->query("UPDATE `forum_files` SET
`down`= down + 1 WHERE `id` = '$file' LIMIT 1 ");

$file = '/forum/files/attach/'.$fileRes['name'];

$ext = pathinfo($fileRes['name'], PATHINFO_EXTENSION);
if ($ext == 'jar' && isset($_GET['jad'])){
  include 'includes/fileupload.php';
  $filename = str_replace(".jar", "", $fileRes['name']);
	$filesize = filesize('../'.$file);
  $zip = new PclZip('../'.$file);
	$content = $zip->extract(PCLZIP_OPT_BY_NAME,'META-INF/MANIFEST.MF',PCLZIP_OPT_EXTRACT_AS_STRING);
	
  header('Content-type: text/vnd.sun.j2me.app-descriptor');
	header('Content-Disposition: attachment; filename="'.basename($filename).'.jad";');
	echo $content[0]['content']."\n".'MIDlet-Jar-Size: '.$filesize."\n".'MIDlet-Jar-URL: '.$_SERVER['HTTP_HOST'].'/'.$file."\n".'MIDlet-Delete-Confirm: Файл скачен с сайта '.$_SERVER['HTTP_HOST'];
    
}
elseif ($ext == 'png' || $ext == 'jpg' || $ext == 'gif')
{
echo '<img src="'.$file.'"/>';
exit;
}
else
   {
        core::get('downloadfile', 'includes');
        DownloadFile(H . $file, $fileRes['name']);exit;
    }
  //header ('Location: ../'.$file);