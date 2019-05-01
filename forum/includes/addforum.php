<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed



$set['title'] .= ' | Добавление форума';
include H . 'engine/includes/head.php';

if (!$admin)
{
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
}

if ($forum){
  $tree = array('<a href="index.php">Форум</a>', '<a href="index.php?forum='.$forumRes['id'].'">'.text::output($forumRes['name']).'</a>', 'Новый подфорум');
}else{
  $tree = array('<a href="index.php">Форум</a>', 'Новый форум');
}

echo '<div class="fmenu">';
  foreach ($tree as $menu)
    {
        echo $menu .'  &nbsp; ';
    }
  echo '</div>';

if (isset($_POST['send'])){

  if (empty($_POST['name']))
    $error .= 'Не введено название!<br />';

  if (mb_strlen($_POST['name']) > 100)
    $error .= 'Слишком длинное название!<br />';

  $sql2 = $forum ? " WHERE  `refid` = '".$forumRes['id']."'" : "";

  $realId = $sql->query("SELECT `realid` FROM `forum_forums`$sql2 ORDER BY `realid` DESC ")->fetch();

  if (!isset($error)){
    $sql->query("INSERT INTO `forum_forums` SET
    `realid` = '".($realId['realid'] + 1)."',
    `refid` = '$forum',
    `type` = '".intval($_POST['type'])."',
    `name` = '".my_esc(trim($_POST['name']))."',
    `text` = '".my_esc(trim($_POST['text']))."'
    ");
    $fid = mysqli_insert_id($sql->db);

    if ($forum){
      $sql->query("UPDATE `forum_forums` SET
      `count`= count + 1 WHERE `id` = '$forum' LIMIT 1 ");
    }

    header ('Refresh:1; URL=index.php?forum='.$fid);
    echo '<div class="msg">Форум создан<br /><a href="index.php?forum='.$fid.'">Далее</a></div>';
    include H . 'engine/includes/foot.php';
    
  }
}

if (isset($error))
  echo Core::msg_show($error);

echo '<form id="form" name="form" action="index.php?act=addforum&amp;forum='.$forum.'" method="post">';
echo '<div class="p_m">Название <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально 100 символов" />:<br /><input type="text" name="name" value="'.(isset($_POST['name']) ? htmlspecialchars($_POST['name']) : null) .'" /><br />';
echo '<select name="type">'.($forum ? '' : '<option value="0">Для форумов</option>').'<option value="1">Для тем</option></select></div><div class="msg">';
echo 'Описание:<br /><textarea name="text">'.(isset($_POST['text']) ? htmlspecialchars($_POST['text']) : null ).'</textarea><br />';
echo '<input type="submit" name="send" value="Отправить" /></div></form>';