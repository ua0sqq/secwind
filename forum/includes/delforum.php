<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Удаление';
include H . 'engine/includes/head.php';

if (!$forum){
  echo Core::msg_show('Отсутствует идентиикатор форума!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$admin){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}


echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?forum='.$forumRes['id'].'">'.text::output($forumRes['name']).'</a>  '. ($forumRes['refid'] ? 'Удалить подфорум' : 'Удалить форум').'</div>';

if (isset($_GET['movetopics']) && $forum){
  if (isset($_POST['send'])){
    if (empty($_POST['forum']))
      $error = 'Неверные данные!<br /><a href="index.php">Форум</a>';
      if (!isset($error)){
        $forumName = $sql->query("SELECT `name` FROM `forum_forums` WHERE `id` = '".abs(intval($_POST['forum']))."' LIMIT 1 ")->fetch();
        $sql->query("UPDATE `forum_topics` SET
        `refid` = '".abs(intval($_POST['forum']))."',
        `forum` = '".my_esc($forumName['name'])."'
        WHERE `refid` = '$forum' ");
        
        $lastTopic = $sql->query("SELECT * FROM `forum_topics` WHERE `refid` = '".abs(intval($_POST['forum']))."' ORDER BY `time` DESC LIMIT 1 ")->fetch();
        $sql->query("UPDATE `forum_forums` SET
        `last_topic` = '".$lastTopic['refid'].":|:".$lastTopic['name'].":|:".$lastTopic['time']."',
        `count` = count + ".$forumRes['count']." WHERE `id` = '".abs(intval($_POST['forum']))."' LIMIT 1 ");
        
        $sql->query("UPDATE `forum_forums` SET
        `last_topic` = '',
        `count` = '0' WHERE `id` = '$forum' LIMIT 1 ");

        echo '<div class="post">Темы перенесены<br /><a href="index.php?act=delforum&amp;forum='.$forum.'&amp;yes">Далее (Удалить подфорум)</a></div>';
      }else{
        echo Core::msg_show($error);
      }  
  }else{
    echo '<form name="form" action="index.php?act=delforum&amp;forum='.$forum.'&amp;movetopics" method="post"><div class="p_m">Переместить темы в: <select name="forum">';
    
    $sql->query("SELECT * FROM `forum_forums` WHERE `refid` = '0' ORDER BY `realid` ASC ");
    while ($res = $sql->fetch()){
      if ($res['type'] == 1){
        echo '<option value="'.$res['id'].'"'.($res['id'] == $forum ? ' selected="selected"' : '').'>- '.text::output($res['name']).'</option>';
      }else{
        echo '<option disabled="disabled">'.text::output($res['name']).'</option>';
        $subForumReq = mysqli_query($db, "SELECT * FROM `forum_forums` WHERE `refid` = '".$res['id']."' ORDER BY `realid` ASC ");
        while ($subForumRes = mysqli_fetch_assoc($subForumReq)){
          echo '<option value="'.$subForumRes['id'].'"'.($subForumRes['id'] == $forum ? ' selected="selected"' : '').'>- '.text::output($subForumRes['name']).'</option>';
        }
      }
    }
    echo '</select><br /><input type="submit" name="send" value="Переместить" /></div></form>';
    
    include H . 'engine/includes/foot.php';
    
  }
}

if (isset($_POST['yes'])){
  if ($forumRes['type'] == 0 && $forumRes['count'])
    $error = 'Сначала удалите подфорумы!<br /><a href="index.php?forum='.$forum.'">Форум</a>';

  if ($forumRes['type'] == 1 && $forumRes['count'])
    $error = 'Сначала удалите или перенесите темы!<br /><a href="index.php?act=delforum&amp;forum='.$forum.'&amp;movetopics">Перенести темы</a> | <a href="index.php">Форум</a>';
  if (!isset($error)){
    if ($forumRes['refid'])
      $sql->query("UPDATE `forum_forums` SET `count` = count - 1 WHERE `id` = '".$forumRes['refid']."' LIMIT 1 ");
      if (file_exists(('../forum/files/icons/'.$forum.'.png')))
        unlink('../forum/files/icons/'.$forum.'.png');
      $sql->query("DELETE FROM `forum_forums` WHERE `id` = '$forum' LIMIT 1 ");
      header ('Refresh:1; URL=index.php');
      echo '<div class="msg">Форум удален<br /><a href="index.php">Форум</a></div>';
  }else{
    echo Core::msg_show($error);
  }
}elseif(isset($_POST['no'])){
  header ('Location: index.php');
}else{
  echo '<form action="index.php?act=delforum&amp;forum='.$forum.'" method="post"><div class="rmenu">Вы действительно хотите удалить форум?<br />';
  echo '<input type="submit" name="yes" value="Удалить" /> <input type="submit" name="no" value="Отмена" /></div></form>';
}