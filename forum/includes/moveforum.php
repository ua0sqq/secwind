<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Перемещение';
include H . 'engine/includes/head.php';

if (!$forum){
  echo Core::msg_show('Отсутствует идентификатор форума!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$moder){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

$go = $forumRes['refid'] ? 'forum='.$forumRes['refid'] : '';
$sql2 = $forumRes['refid'] ? " ='".$forumRes['refid']."'" : "=0"; 

if (isset($_GET['up']) && !isset($_GET['down'])){
  $forumUp = $sql->query("SELECT `id`, `realid`, `name` FROM `forum_forums` WHERE `refid`$sql2 AND `realid` > '".$forumRes['realid']."' ORDER BY `realid` ASC LIMIT 1 ")->fetch();
  if ($forumUp['realid'] && $forumUp != $forum){
    $sql->query("UPDATE `forum_forums` SET `realid`='".$forumUp['realid']."' WHERE `id`='".$forum."' LIMIT 1 ");
    $sql->query("UPDATE `forum_forums` SET `realid`='".$forumRes['realid']."' WHERE `id`='".$forumUp['id']."' LIMIT 1 ");
  }
}elseif (isset($_GET['down']) && !isset($_GET['up'])){
  $forumDown = $sql->query("SELECT `id`, `realid`, `name` FROM `forum_forums` WHERE `refid`$sql2 AND `realid` < '".$forumRes['realid']."' ORDER BY `realid` DESC LIMIT 1 ")->fetch();
  if ($forumDown['realid'] && $forumDown != $forum){
    $sql->query("UPDATE `forum_forums` SET `realid`='".$forumDown['realid']."' WHERE `id`='".$forum."' LIMIT 1 ");
    $sql->query("UPDATE `forum_forums` SET `realid`='".$forumRes['realid']."' WHERE `id`='".$forumDown['id']."' LIMIT 1 ");
  }
}else{
  echo Core::msg_show('Неверные данные!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}
header ('Location: index.php?'.$go);