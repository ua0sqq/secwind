<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Список проголосовавших';
include H.'engine/includes/head.php';

if (!$topic){
  echo Core::msg_show('Отсутствыет идентификатор темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$user_id){
  echo Core::msg_show('Только для авторизованных!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topic.'">'.text::output($topicRes['name']).'</a>  /  '.text::output($topicRes['poll_name']).'</div>';

$sql2 = $pollSet['poll_mod'] < 1 ? array('') : array(', COUNT(`forum_polled`.`user_id`) AS `count`', ' GROUP BY `forum_polled`.`user_id`');

$total = $pollSet['total_polled'];
$page = new page($total, $set['p_str']);
if ($total){

    $page->display('index.php?act=polled&amp;topic='.$topic.'&amp;');
  
  $req = $sql->query("SELECT `forum_polled`.*".$sql2[0].", `user`.`id`, `user`.`nick`, `user`.`pol` FROM `forum_polled` LEFT JOIN `user` ON `forum_polled`.`user_id` = `user`.`id` WHERE `forum_polled`.`refid` = '$topic'".$sql2[1]." LIMIT ".$page->limit());
  
  while ($res = $sql->fetch()){
    echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
    $array = array('status' => (($pollSet['poll_mod'] < 1 && $moder) ? '' : $res['count']));
    echo Core::user_show($res, $array);
    echo '</div>';
    ++$i;
  }
  echo '<div class="fmenu">Пользователей: '.$total.'</div>';
  $page->display('index.php?act=polled&amp;topic='.$topic.'&amp;');
}
else
{
Core::msg_show('Пусто');
}