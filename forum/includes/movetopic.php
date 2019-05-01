<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Перемещение';
include H . 'engine/includes/head.php';

if (!$topic){
  echo Core::msg_show('Отсутствует идентификатор темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$moder){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (isset($_GET['otherforum']))
  $sql2 = abs(intval($_GET['otherforum']));
else
  $sql2 = $topic['refid'];

echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topicRes['id'].'&amp;page=end">'.text::output($topicRes['name']).'</a>  /  Перенести тему</div>';


if (isset($_POST['send'])){
  if (empty($_POST['forum']))
    $error = 'Неверные данные!';

  if (!isset($error)){
    $forumName = $sql->query("SELECT `name` FROM `forum_forums` WHERE `id`='".abs(intval($_POST['forum']))."' LIMIT 1 ")->fetch();
    
    $sql->query("INSERT INTO `forum_posts` SET
    `refid` = '$topic',
    `time` = '".time()."',
    `user_id` = '$user_id',
    `user` = '$user[nick]',
    `text` = 'Тема перенесена (из [b]".text::output($topicRes['forum'])."[/b])',
    `files` = '0'");
    $pid = mysqli_insert_id($sql->db);
    
    $sql->query("UPDATE `forum_topics` SET
    `refid`='".abs(intval($_POST['forum']))."',
    `time` = '".time()."',
    `forum`='".text::output($forumName['name'])."',
    `lastpost` = '$user[nick]:|:$pid',
    `count` = count + 1
    WHERE `id`='$topic' LIMIT 1 ");
    
    if ($topicRes['user_id'] != $user_id)
      journal::addEntry($topicRes['user_id'], 'Ваша <a href="index.php?post='.$pid.'">тема</a> перенесена (из <strong>'.text::output($topicRes['forum']).'</strong>)');
    
    $lastTopic = $sql->query("SELECT * FROM `forum_topics` WHERE `refid` = '".$topicRes['refid']."' ORDER BY `time` DESC LIMIT 1 ")->fetch();
    $sql2 = empty($lastTopic['name']) ? '' : $lastTopic['refid'].":|:".$lastTopic['name'].":|:".$lastTopic['time']; 
    $sql->query("UPDATE `forum_forums` SET
    `last_topic`='$sql2',
    `count`= count - 1 WHERE `id`='".$topicRes['refid']."' LIMIT 1 ");

    $lastTopic2 = $sql->query("SELECT * FROM `forum_topics` WHERE `refid` = '".abs(intval($_POST['forum']))."' ORDER BY `time` DESC LIMIT 1 ")->fetch();
    $sql->query("UPDATE `forum_forums` SET
    `last_topic`='".$lastTopic2['refid'].":|:".$lastTopic2['name'].":|:".$lastTopic2['time']."',
    `count`= count + 1 WHERE `id`='".abs(intval($_POST['forum']))."' LIMIT 1 ");

    header ('Refresh:1; URL=index.php?topic='.$topic.'&page=end#p'.$pid);
    echo '<div class="msg">Тема перенесена<br /><a href="index.php?topic='.$topic.'&amp;page=end#p'.$pid.'">Далее</a></div>';
    include H . 'engine/includes/foot.php';
    
  }
}

if (isset($error))
  echo Core::msg_show($error);

echo '<form name="form" action="index.php?act=movetopic&amp;topic='.$topic.'&amp;page=end" method="post"><div class="p_m">Переместить тему в: <select name="forum">';
$sql->query("SELECT * FROM `forum_forums` WHERE `refid` = '0' ORDER BY `realid` ASC ");
while ($res = $sql->fetch()){
  if ($res['type'] == 1){
    echo '<option value="'.$res['id'].'"'.($res['id'] == $topicRes['refid'] ? ' selected="selected"' : '').'>- '.text::output($res['name']).'</option>';
  }else{
    echo '<option disabled="disabled">'.text::output($res['name']).'</option>';
    $subForumReq = mysqli_query($sql->db, "SELECT * FROM `forum_forums` WHERE `refid` = '".$res['id']."' ORDER BY `realid` ASC ");
    while ($subForumRes = mysqli_fetch_assoc($subForumReq))
    {
      echo '<option value="'.$subForumRes['id'].'"'.($subForumRes['id'] == $topicRes['refid'] ? ' selected="selected"' : '').'>- '.text::output($subForumRes['name']).'</option>';
    }
  }
}
echo '</select><br /><input type="submit" name="send" value="Переместить" /></div></form>';