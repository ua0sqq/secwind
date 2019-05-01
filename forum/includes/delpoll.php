<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Удаление голосования';
include H . 'engine/includes/head.php';

if (!$topic){
  echo Core::msg_show('Отсутствует идентификатор темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$moder){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}


echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topicRes['id'].'&amp;page=1">'.text::output($topicRes['name']).'</a>  /  Удаление голосования</div>';

if (isset($_POST['yes'])){
  $sql->query("DELETE FROM `forum_polls` WHERE `refid` = '$topic' LIMIT 50 ");
  $sql->query("DELETE FROM `forum_polled` WHERE `refid` = '$topic' ");
    
  $sql->query("UPDATE `forum_topics` SET
  `poll_name` = '',
  `poll_set` = ''
  WHERE `id` = '$topic' LIMIT 1 ");
    
  $sql->query("OPTIMIZE TABLE `forum_polled` ");
  
  header ('Refresh:1; URL=index.php?act=edittopic&topic='.$topic.'&page='.$page);  
  echo '<div class="msg">Голосование удалено<br /><a href="index.php?act=edittopic&amp;topic='.$topic.'&amp;page=end">Далее</a></div>';
}elseif(isset($_POST['no'])){
  header ('Location: index.php?topic='.$topic.'&page='.$page);
}else{
  echo '<form action="index.php?act=delpoll&amp;topic='.$topic.'&amp;page='.$page.'" method="post"><div class="rmenu">Вы действительно хотите удалить голосование?<br />';
  echo '<input type="submit" name="yes" value="Удалить" /> <input type="submit" name="no" value="Отмена" /></div></form>';
}