<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$cur_page = isset($_GET['page']) ? intval($_GET['page']) : 1;

$set['title'] .= ' | Удаление';
include H . 'engine/includes/head.php';

if (!$post && !$topic){
  echo Core::msg_show('Отсутствует идентификатор сообщения или темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}
$check = false;

if (!$moder){
  if ($postRes['user_id'] != $user_id)
  $check = true;

  if ($postRes['time'] < time() - 300)
    $check = true;

  if ($topicRes['close'])
    $check = true;

  if ($check){
    echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php?topic='.$topic.'&amp;page='.$cur_page.'#p'.$post.'">Назад</a>');
    require_once($path.'incfiles/end.php');
    
  }
}


echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topic.'&amp;page='.$cur_page.'#p'.$post.'">'.text::output($topicRes['name']).'</a>  /  Удаление поста</div>';

if (isset($_POST['yes'])){
  
  $lastPost = $sql->query("SELECT `id`, `user`, `time` FROM `forum_posts` WHERE `refid` = '".$postRes['refid']."' AND `id` != '$post' ORDER BY `time` DESC LIMIT 1 ")->fetch();

  $sql->query("UPDATE `forum_topics` SET
  `time` = '".$lastPost['time']."',
  `count` = count - 1,
  `lastpost` = '".$lastPost['user'].":|:".$lastPost['id']."'
  WHERE `id` = '".$postRes['refid']."' LIMIT 1 ");

  $lastTopic = $sql->query("SELECT * FROM `forum_topics` WHERE `refid` = '".$topicRes['refid']."' ORDER BY `time` DESC LIMIT 1 ")->fetch();
  $sql->query("UPDATE `forum_forums` SET `last_topic` = '".$lastTopic['refid'].":|:".$lastTopic['name'].":|:".$lastTopic['time']."' WHERE `id` = '".$topicRes['refid']."' LIMIT 1 ");
  
  if ($postRes['files']){
    $sql->query("SELECT * FROM `forum_files` WHERE `refid` = '$post' LIMIT 10 ");
    if ($sql->num_rows()){
      while ($fileRes = $sql->fetch()){
        unlink('../forum/files/attach/'.$fileRes['name']);
      }
      $sql->query("DELETE FROM `forum_files` WHERE `refid` = '$post' ", true);
    }
  }
  
  if ($postRes['rating'])
    $sql->query("DELETE FROM `forum_posts_rating` WHERE `refid` = '$post' ");
  
  $sql->query("DELETE FROM `forum_posts` WHERE `id` = '$post' LIMIT 1 ");
  
  header ('Refresh:1; URL=index.php?topic='.$topic.'&page='.$cur_page);
  echo '<div class="msg">Пост удален<br /><a href="index.php?topic='.$topic.'&amp;page='.$cur_page.'">Далее</a></div>';

}elseif(isset($_POST['no'])){
  header ('Location: index.php?topic='.$topic.'&page='.$cur_page);
}else{
  $check = $sql->query("SELECT COUNT(*) FROM `forum_posts` WHERE `refid` = '".$postRes['refid']."' LIMIT 2 ")->result();
  if ($check == 1){
    echo '<form action="index.php?act=deltopic&amp;topic='.$topic.'" method="post"><div class="rmenu">Это последний пост в теме, при удалении этого поста будет удалена и тема<br />';
    if ($moder)
      echo '<input type="submit" name="yes" value="Удалить" /> ';
    echo '<input type="submit" name="no" value="Отмена" /></div></form>';
    include H . 'engine/includes/foot.php';
    
  }

  $firstPost = $sql->query("SELECT `id` FROM `forum_posts` WHERE `refid`='".$postRes['refid']."' ORDER BY `time` ASC LIMIT 1 ")->fetch();

  echo '<form action="index.php?act=delpost&amp;topic='.$topic.'&amp;post='.$post.'&amp;page='.$cur_page.'" method="post"><div class="rmenu">'.($firstPost['id'] == $post ? 'Это первый пост темы, его удаление не рекомендуется!<br />' : '').'Вы действительно хотите удалить сообщение?<br />';
  echo '<input type="submit" name="yes" value="Удалить" /> <input type="submit" name="no" value="Отмена" /></div></form>';
}