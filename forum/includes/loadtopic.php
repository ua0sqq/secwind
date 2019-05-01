<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Скачать тему';

if (!$topic){
  include H . 'engine/includes/head.php';
  echo Core::msg_show('Отсутствует идентификатор темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$user_id){
  include H . 'engine/includes/head.php';
  echo Core::msg_show('Только для авторизованных!<br /><a href="index.php?topic='.$topic.'&amp;page=end">Назад</a>');
  include H . 'engine/includes/foot.php';
  
}

$sql->query("SELECT * FROM `forum_posts` WHERE `refid` = '$topic' ORDER BY `time` ASC ");

$name = 'Тема: '.text::output($topicRes['name']).PHP_EOL .'Скачано с сайта '.$_SERVER['HTTP_HOST'];

$text = array();
if(isset($_POST['loadtxt'])){
  while ($res = $sql->fetch()){
    $text[] = $res['user']." (".date("d.m.Y/H:i", $res['time']).")\r\n".$res['text']."";
  }
  array_unshift($text, $name);
  $tmp = implode("\r\n\r\n",$text);
  header ('Content-Encoding: none');
  header ('Content-type: text/plain');
  header ('Content-Disposition: attachment; filename="topic__'.$topic.'.txt"');
  header ('Last-Modified: ' . date("D, d M Y H:i:s T"));
  echo $tmp;
  exit;
}elseif(isset($_POST['loadgzip'])){
  while ($res = $sql->fetch()){
    $text[] = $res['user']." (".date("d.m.Y/H:i", $res['time']).")\r\n".$res['text']."";
  }
      
  array_unshift($text, $name);
  $tmp = implode("\r\n\r\n",$text);
  header ('Content-Encoding: none');
  header ('Content-type: multipart/alternative');
  header ('Content-Disposition: attachment; filename="topic_'.$topic.'.txt.gz"');
  header ('Last-Modified: ' . date("D, d M Y H:i:s T"));
  echo gzencode($tmp);
  exit;
}else{
  include H . 'engine/includes/head.php';

  echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topic.'&amp;page=1">'.text::output($topicRes['name']).'</a>  /  Загрузить тему</div>';
  
  echo '<form action="index.php?act=loadtopic&amp;topic='.$topic.'&amp;page=end" method="post"><div class="msg"><input type="submit" name="loadtxt" value="Загрузить .txt" />'.
/*' <input type="submit" name="loadgzip" value="Загрузить .gzip" />'.*/
'</div></form>';
}