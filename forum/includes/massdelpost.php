<?php

# fixed

$set['title'] .= ' | Массовое удаление постов';
include H . 'engine/includes/head.php';

if (!$topic){
  echo Core::msg_show('Отсутствует идентификатор темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
}

if (!$moder){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topic.'&amp;page=1">'.text::output($topicRes['name']).'</a> /  Удаление постов</div>';

if (isset ($_POST['yes'])){
  $dc = $_SESSION['dc'];
  foreach ($dc as $delid){

    $fileReq = $sql->query("SELECT * FROM `forum_files` WHERE `refid` = '$delid' ");
    
    if ($sql->num_rows()){
      
      while ($fileRes = $sql->fetch()){
        unlink('../forum/files/attach/'.$fileRes['name']);
      }
      $sql->query("DELETE FROM `forum_files` WHERE `refid` = '$delid' LIMIT 1 ", true);
    }
    $postRes = $sql->query("SELECT `user_id`, `rating` FROM `forum_posts` WHERE `id` = '$delid' LIMIT 1 ")->fetch();
    ++$i;
    
    if ($postRes['rating'])
      $sql->query("DELETE FROM `forum_posts_rating` WHERE `refid` = '$delid' ");

    $sql->query("DELETE FROM `forum_posts` WHERE `id` = '$delid' LIMIT 1 ");
  }

  $lastPost = $sql->query("SELECT `id`, `time`, `user` FROM `forum_posts` WHERE `refid` = '$topic' ORDER BY `time` DESC LIMIT 1 ")->fetch();

  $sql->query("UPDATE `forum_topics` SET
  `time` = '".$lastPost['time']."',
  `lastpost` = '".$lastPost['user'].":|:".$lastPost['id']."',
  `count` = count - $i
  WHERE `id` = '$topic' LIMIT 1 ");

  $lastTopic = $sql->query("SELECT * FROM `forum_topics` WHERE `refid` = '".$topicRes['refid']."' ORDER BY `time` DESC LIMIT 1 ")->result();
  $sql->query("UPDATE `forum_forums` SET
  `last_topic`='".$lastTopic['refid'].":|:".$lastTopic['name'].":|:".$lastTopic['time']."'
  WHERE `id`='".$topicRes['refid']."' LIMIT 1 ");

  header ('Refresh:1; URL=index.php?topic='.$topic.'&page=1');
  echo '<div class="msg">Отмеченные посты удалены<br /><a href="index.php?topic='.$topic.'&amp;page=1">Далее</a></div>';
}elseif(isset($_POST['no'])){
  header ('Location: index.php?topic='.$topic.'&page=end');
}else{
  if (empty ($_POST['delch'])){
    echo Core::msg_show('Вы ничего не выбрали для удаления!<br /><a href="index.php?topic='.$topic.'&amp;page=1">Назад</a>');
    include H . 'engine/includes/foot.php';
    
  }
  foreach ($_POST['delch'] as $v){
    $dc[] = intval($v);
    ++$i;
  }
  $_SESSION['dc'] = $dc;

  $check = $sql->query("SELECT COUNT(*) FROM `forum_posts` WHERE `refid` = '$topic' ")->result();
  if ($check == $i){
    echo '<form action="index.php?act=deltopic&amp;topic='.$topic.'" method="post"><div class="rm">Вы собираетесь удалить все посты в теме, при удалении будет удалена и тема<br />';
    if ($moder)
      echo '<input type="submit" name="yes" value="Удалить" /> ';
    echo '<input type="submit" name="no" value="Отмена" /></div></form>';
    include H . 'engine/includes/foot.php';
    
  }

  $firstPost = $sql->query("SELECT `id` FROM `forum_posts` WHERE `refid` = '$topic' ORDER BY `time` ASC LIMIT 1 ")->fetch();
  
  echo '<form action="index.php?act=massdelpost&amp;topic='.$topic.'&amp;page=1" method="post"><div class="rmenu">'.(in_array($firstPost['id'], $_POST['delch']) ? 'В числе выбранного есть первый пост темы, его удаление не рекомендуется!<br />' : '').'Вы уверены в удалении сообщений ('.$i.' шт.)?<br />';
  echo '<input type="submit" name="yes" value="Удалить" /> <input type="submit" name="no" value="Отмена" /></div></form>';
}