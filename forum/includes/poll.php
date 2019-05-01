<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

$set['title'] .= ' | Отдать голос';
include H.'engine/includes/head.php';

if (!$topic){
  echo Core::msg_show('Отсутствует идентификатор темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$user_id || $topicRes['close']){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$topicRes['poll_name'] && !isset($_POST['poll'])){
  echo Core::msg_show('Ошибка!<br /><a href="index.php?topic='.$topicRes['id'].'&amp;page=1">Назад</a>');
  include H . 'engine/includes/foot.php';
  
}

echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topicRes['id'].'&amp;page=1">'.text::output($topicRes['name']).'</a>  /  Голосование</div>';

if (empty($_POST['var'])){
  echo Core::msg_show('Вы ничего не выбрали!<br /><a href="index.php?topic='.$topicRes['id'].'&amp;page=1">Назад</a>');
  include H . 'engine/includes/foot.php';
  
}

  
$pollCheck = $sql->query("SELECT COUNT(*) FROM `forum_polled` WHERE `refid` = '$topic' AND `user_id` = '$user_id' LIMIT 1 ")->result();
if (!$pollCheck){
  foreach ($_POST['var'] as $var){
    $sql->query("UPDATE `forum_polls` SET `count` = count + 1 WHERE `id` = '".intval($var)."' LIMIT 1 ");
    $sql->query("INSERT INTO `forum_polled` SET
    `refid` = '$topic',
    `poll` = '".intval($var)."',
    `user_id` = '$user_id' ");
    ++$i;
  }
  $pollSet = array('poll_close' => $pollSet['poll_close'], 'poll_mod' => $pollSet['poll_mod'], 'total_polls' => ($pollSet['total_polls'] + $i), 'total_polled' => ($pollSet['total_polled'] + 1));
  $sql->query("UPDATE `forum_topics` SET
  `poll_set` = '".serialize($pollSet)."'
  WHERE `id` = '$topic' LIMIT 1 ");
    
  
  header ('Refresh:1; URL=index.php?topic='.$topicRes['id'].'&page=end');
  echo '<div class="msg">Ваш голос принят, спасибо<br /><a href="index.php?topic='.$topicRes['id'].'&amp;page=1">Далее</a></div>';
}else{
  echo Core::msg_show('Вы уже отдавали голос!<br /><a href="index.php?topic='.$topicRes['id'].'&amp;page=1">Назад</a>');
}