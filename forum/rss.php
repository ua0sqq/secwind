<?

include '../engine/includes/start.php';
require_once('includes/functions.php');
Core::get('text.class', 'classes');

if (!$topic){
  include H.'engine/includes/head.php';
  echo Core::msg_show('Отсутствует идентификатор темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

$sql->query("SELECT * FROM `forum_topics` WHERE `id` = '$topic' LIMIT 1 ");

if (!$sql->num_rows()){
  include H . 'engine/includes/head.php';
  echo Core::msg_show('Такой темы не существует!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

$topicRes = $sql->fetch();

header('content-type: application/rss+xml');
echo '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/"><channel>
<title>'.text::output($topicRes['name']).'</title>
<link>'.$_SERVER['HTTP_HOST'].'</link>
<description>'.$_SERVER['HTTP_HOST'].'-форум</description>
<language>ru-RU</language> ';

$date = $sql->query("SELECT `time` FROM `forum_posts` WHERE `refid` = '$topic' ORDER BY `time` DESC LIMIT 1 ")->fetch();

$sql->query("SELECT `forum_posts`.*, `forum_posts`.`id` AS `pid`, `user`.`nick` FROM `forum_posts` LEFT JOIN `user` ON `forum_posts`.`user_id` = `user`.`id` WHERE `forum_posts`.`refid` = '$topic' ORDER BY `forum_posts`.`time` DESC LIMIT 10 ");


echo '
<item>
<title>'.Core::time($date['time']).'</title>
<link>/forum/index.php?topic='.$topic.'&amp;page=end</link>
<description><![CDATA[';

while ($res = $sql->fetch()){
  echo '<b>'.$res['nick'].'</b> '.Core::time($res['time']).'<br />
' .text::output($res['text']).'<br />';
}
echo ']]></description><pubDate>'.Core::time($date['time']).'</pubDate>
</item>';

echo '</channel></rss>';