<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Редактирование';
include H . 'engine/includes/head.php';

if (!$forum){
  echo Core::msg_show('Отсутствует идентификатор форума!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$admin){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}


echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?forum='.$forumRes['id'].'">'.text::output($forumRes['name']).'</a>  ' .($forumRes['refid'] ? 'Редактировать подфорум' : 'Редактировать форум').'</div>';

$name = isset($_POST['name']) ? trim($_POST['name']) : $forumRes['name'];
$text = isset($_POST['text']) ? trim($_POST['text']) : $forumRes['text'];
$type = isset($_POST['type']) ? intval($_POST['type']) : $forumRes['type'];

if (isset($_POST['send'])){

  if (empty($name))
    $error .= 'Не введено название!<br />';

  if (mb_strlen($name) > 100)
    $error .= 'Слишком длинное название!<br />';

  if (!isset($error)){
    $sql->query("UPDATE `forum_forums` SET
    `type` = '$type',
    `name` = '".my_esc($name)."',
    `text` = '".my_esc($text)."'
    WHERE `id` = '$forum'
    ");

    if ($forumRes['type'] == 1){
      $sql->query("UPDATE `forum_topics` SET `forum` = '".my_esc(trim($name))."' WHERE `refid` = '$forum' ");
    }

    header ('Refresh:1; URL=index.php?forum='.$forum);
    echo '<div class="msg">Форум изменен<br /><a href="index.php?forum='.$forum.'">Далее</a></div>';
    include H . 'engine/includes/foot.php';
    
  }
}

if (isset($error))
  echo Core::msg_show($error);

echo '<form name="form" action="index.php?act=editforum&amp;forum='.$forum.'" method="post">';
echo '<div class="rmenu">Название <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально 100 символов" />:<br /><input type="text" name="name" value="'.text($name).'" /><br />
<select name="type">'.($forum ? '<option value="0">Для форумов</option>' : '').'<option value="1"'.($type == 1 ? ' selected="selected"' : '').'>Для тем</option></select><br />';
echo 'Описание:<br /><textarea name="text">'.text($text).'</textarea><br />';
echo '<input type="submit" name="send" value="Сохранить" /></div></form>';