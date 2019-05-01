<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Удаление';
include H . 'engine/includes/head.php';

if (!$topic){
  echo Core::msg_show('Отсутствует идентификатор темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$moder){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}


echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topic.'&amp;page=1">'.text::output($topicRes['name']).'</a>  /  Удаление темы</div>';

if (isset($_POST['yes'])){

  $sql->query("SELECT `user_id`,`rating` FROM `forum_posts` WHERE `refid` = '$topic' ");
  while ($postRes = $sql->fetch()){
    if ($postRes['rating'])
      $sql->query("DELETE FROM `forum_posts_rating` WHERE `refid` = '".$postRes['id']."' ", true);
  }

  $sql->query("SELECT * FROM `forum_files` WHERE `topic` = '$topic' ");
  $i = 0;
  if ($sql->num_rows()){
    while ($fileRes = $sql->fetch()){
      unlink('../forum/files/attach/'.$fileRes['name']); 
    }
    $sql->query("DELETE FROM `forum_files` WHERE `topic` = '$topic' ");
    $sql->query("OPTIMIZE TABLE `forum_files` ");
  
  }

  if ($topicRes['poll_name']){
    $sql->query("DELETE FROM `forum_polls` WHERE `refid` = '$topic' ");
    $sql->query("DELETE FROM `forum_polled` WHERE `refid` = '$topic' ");
    $sql->query("OPTIMIZE TABLE `forum_polled` ");
  }

  $sql->query("DELETE FROM `forum_posts` WHERE `refid` = '$topic' ");
  if ($topicRes['count'] > 100)
    $sql->query("OPTIMIZE TABLE `forum_posts` ");
  $sql->query("DELETE FROM `forum_topics` WHERE `id` = '$topic' LIMIT 1 ");
  $sql->query("DELETE FROM `forum_readed` WHERE `topic` = '$topic' ");
  
  $lastTopic = $sql->query("SELECT * FROM `forum_topics` WHERE `refid` = '".$topicRes['refid']."' ORDER BY `time` DESC LIMIT 1 ")->fetch();
  $sql2 = empty($lastTopic['name']) ? "" : $lastTopic['refid'].":|:".$lastTopic['name'].":|:".$lastTopic['time'];
  $sql->query("UPDATE `forum_forums` SET
`last_topic` = '$sql2',
`count`= count - 1
WHERE `id`='".$topicRes['refid']."' LIMIT 1 ");

  journal_add($topicRes['user_id'], 'Ваша тема <strong>'.text::output($topicRes['name']).'</strong> удалена (удалил <a href="../users/profile.php?user='.$user_id.'">'.$user['nick'].'</a>)');
  
  header ('Refresh:1; URL=index.php?forum='.$topicRes['refid']);
  echo '<div class="msg">Тема удалена<br /><a href="index.php?forum='.$topicRes['refid'].'">Далее</a></div>';

}elseif(isset($_POST['no'])){
  header ('Location: index.php?topic='.$topic.'&page=end');
}else{
  echo '<form action="index.php?act=deltopic&amp;topic='.$topic.'" method="post"><div class="rmenu">Вы хотите удалить тему?<br />
<input type="submit" name="yes" value="Удалить" /> <input type="submit" name="no" value="Отмена" /></div></form>';
}