<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Рейтинг';
include H . 'engine/includes/head.php';

if (!$post || !$topic){
  echo Core::msg_show('Отсутствует идентификатор сообщения или темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}


    $back =isset($_SERVER['HTTP_REFERER']) && stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) ? htmlspecialchars($_SERVER['HTTP_REFERER']).'#p'.$post : 'index.php?topic='.$topic;



if (!$user_id || $topicRes['close']){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="'.$back.'">Назад</a>');
  include H . 'engine/includes/foot.php';
  
}

echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topic.'&amp;page=end">'.text::output($topicRes['name']).'</a>  /  Рейтинг сообщения</div>';

if ($topicRes['close'])
  $error = 'Тема закрыта!<br /><a href="'.$back.'">Назад</a>';

if ($postRes['user_id'] == $user_id)
  $error = 'За свой пост нельзя отдать голос!<br /><a href="'.$back.'">Назад</a>';

$check = $sql->query("SELECT COUNT(*) FROM `forum_posts_rating` WHERE `refid` = '$post' AND `user_id` = '$user_id' ")->result();
if ($check)
  $error = 'Вы уже отдавали голос за этот пост!<br /><a href="'.$back.'">Назад</a>';

if ((!isset($_GET['plus']) && !isset($_GET['minus'])) || (isset($_GET['plus']) && isset($_GET['minus'])))
  $error = 'Неверные данные!<br /><a href="index.php">Форум</a>';

if (!isset($error)){
  
  $sql->query("INSERT INTO `forum_posts_rating` SET
  `refid` = '$post',
  `user_id` = '$user_id'
  ");

  if (isset($_GET['plus'])){
    $rating = $postRes['rating'] + 1;
  }elseif(isset($_GET['minus'])){
    $rating = $postRes['rating'] - 1;
  }
  
  $sql->query("UPDATE `forum_posts` SET `rating` = '$rating' WHERE `id` = '$post' LIMIT 1 ");
  
  journal_add($postRes['user_id'], 'Изменение рейтинга сообщения в теме: <a href="index.php?post='.$post.'">'.text::output($topicRes['name']).'</a>');

  header ('Refresh:1; URL='.html_entity_decode($back));
  echo '<div class="msg">Ваш голос принят, спасибо!<br /><a href="'.$back.'">Далее</a></div>';
}else{
  echo Core::msg_show($error);
}