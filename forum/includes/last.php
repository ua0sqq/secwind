<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Новые, последние темы';
include H . 'engine/includes/head.php';

if (!$user_id){
  echo Core::msg_show('Только для авторизованных!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

$timeRes = isset($_REQUEST['time']) ? abs(intval($_REQUEST['time'])) : null;
if ($timeRes)
  $time = time() - $timeRes * 3600;

$unread = isset($_GET['unread']) ? 1 : 0;
$nav = '';

if ($unread || isset($_GET['reset'])){
  $tree = '<a href="index.php">Форум</a>  /  Непрочитанные';
  $nav = 'unread&amp;';
}else{
  $nav = $time ? 'time='.$timeRes.'&amp;' : '';
  $tree = '<a href="index.php">Форум</a>  /  Последние темы'.($time ? ' за '.$timeRes.' час.' : '');
}
echo '<div class="fmenu">'.$tree.'</div>';

if (isset($_GET['reset'])){

   $sql->query("SELECT `forum_topics`.`id`, `forum_topics`.`lastpost` FROM `forum_topics` LEFT JOIN `forum_readed` ON `forum_topics`.`id` = `forum_readed`.`topic` AND `forum_readed`.`user_id` = '$user_id' WHERE `forum_readed`.`topic` Is Null ");
  while ($res = $sql->fetch()){
    $lastPost = explode(':|:', $res['lastpost']);
    $sql->query("INSERT INTO `forum_readed` SET
    `topic` = '".$res['id']."',
    `user_id` = '$user_id',
    `time` = '".time()."',
    `lastpost` = '".$lastPost[1]."' ", true);
  }
  $sql->query("SELECT `forum_topics`.`id`, `forum_topics`.`lastpost` FROM `forum_topics` LEFT JOIN `forum_readed` ON `forum_topics`.`id` = `forum_readed`.`topic` AND `forum_readed`.`user_id` = '$user_id' WHERE `forum_topics`.`time` > `forum_readed`.`time` ");
  while ($res = $sql->fetch()){
    $lastPost = explode(':|:', $res['lastpost']);
    $sql->query("UPDATE `forum_readed` SET
    `time` = '".time()."',
    `lastpost` = '".$lastPost[1]."'
    WHERE `topic` = '".$res['id']."' AND `user_id` = '$user_id' ", true);
  }

  echo '<div class="post">Все темы приняты как прочитанные<br /><a href="index.php">Форум</a></div>';
  include H . 'engine/includes/foot.php';
  
}

if ($unread){
  $total = $sql->query("SELECT COUNT(*) FROM `forum_topics` LEFT JOIN `forum_readed` ON `forum_topics`.`id` = `forum_readed`.`topic` AND `forum_readed`.`user_id` = '$user_id' WHERE (`forum_readed`.`topic` Is Null OR `forum_topics`.`time` > `forum_readed`.`time`) ")->result();
}else{
  $total = $sql->query("SELECT COUNT(*) FROM `forum_topics` ".($time ? "WHERE `forum_topics`.`time` > '".$time."' " : ""))->result();
}
$page = new page($total, $set['p_str']);
if ($total){
$page->display('index.php?act=last&amp;'.$nav);

  if ($unread){
    $req = $sql->query("SELECT *, `forum_topics`.`time` AS `topictime`, `forum_topics`.`lastpost` AS `lastpost` FROM `forum_topics` LEFT JOIN `forum_readed` ON `forum_topics`.`id` = `forum_readed`.`topic` AND `forum_readed`.`user_id` = '$user_id' WHERE (`forum_readed`.`topic` Is Null OR `forum_topics`.`time` > `forum_readed`.`time`) ORDER BY `forum_topics`.`time` DESC LIMIT ".$page->limit());
  }else{
    $req = $sql->query("SELECT *, `forum_topics`.`time` AS `topictime` FROM `forum_topics` ".($time ? "WHERE `time` > '$time' " : "")."ORDER BY `time` DESC LIMIT ".$page->limit());
  }

  while ($res = $sql->fetch()){
    echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
    $sub = 'Автор: '.$res['user'];
    if ($res['count'] > 1){
      $lastPost = explode(':|:', $res['lastpost']);
      $sub .= ' <a href="index.php?post='.$lastPost[1].'">Последн.</a>: '.$lastPost[0];
    }
    $sub .= ' '.Core::time($res['topictime']).'<br />';
    $sub .= 'Подфорум: <a href="index.php?forum='.$res['refid'].'">'.$res['forum'].'</a>';
    echo show_topic($res, $sub, ($unread ? 1 : 0));
    echo '</div>';
    ++$i;
  }
  if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';

  echo '<div class="fmenu">Тем: '.$total.'</div>';
   $page->display('index.php?act=last&amp;'.$nav);

    
}else{
  echo '<div class="p_m">Пусто</div>';
}

if ($unread && $total)
  echo '<div class="menu_razd"><a href="index.php?act=last&amp;reset">Принять как прочитанное</a></div>';
echo '<form class="post" action="index.php?act=last" method="get">
<input type="hidden" name="act" value="last" />
Показать за период (час.) <input type="text" size="2" name="time" value="'.($timeRes ? $timeRes : '24').'" /> <input type="submit" value="OK" />';
if ($time || $unread)
  echo '<br /><a href="index.php?act=last">Показать все</a>';
echo '</form>';