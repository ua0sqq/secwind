<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!



$set['title'] .= ' | Редактирование';
include H . 'engine/includes/head.php';

if (!$topic){
  Core::msg_show('Отсутствует идентификатор темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$moder){
  Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

$name = isset($_POST['name']) ? trim($_POST['name']) : $topicRes['name'];
$pollName = isset($_POST['poll_name']) ? trim($_POST['poll_name']) : $topicRes['poll_name'];

echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topicRes['id'].'&amp;page=1">'.text::output($topicRes['name']).'</a>  /  Изменить тему</div>';


if (isset($_POST['send'])){
  if (empty($name))
    $error .= 'Не введено название!<br />';

  if (!isset($error)){

    $sql->query("UPDATE `forum_topics` SET
    `name` = '".my_esc($name)."',
    `close` = '".(isset($_POST['close']) ? 1 : 0)."',
    `sticky` = '".(isset($_POST['sticky']) ? 1 : 0)."',
    `clip` = '".(isset($_POST['clip']) ? 1 : 0)."'
    WHERE `id` = '$topic'
    ");
    
    if ($topicRes['name'] != $name){
      $sql->query("INSERT INTO `forum_posts` SET
      `refid` = '$topic',
      `time` = '".time()."',
      `user_id` = '2',
      `user` = 'Система',
      `text` = 'Тема переименована (cтарое название [b]".$topicRes['name']."[/b])',
      `files` = '0'");
      $pid = mysqli_insert_id($sql->db);
      
      if ($topicRes['user_id'] != $user_id)
        journal_add($topicRes['user_id'], 'Ваша <a href="index.php?post='.$pid.'">тема</a> переименована (cтарое название <strong>'.$topicRes['name'].'</strong>)');
      
      $sql->query("UPDATE `forum_topics` SET
      `time` = '".time()."',
      `lastpost` = '$user[nick]:|:$pid',
      `count` = count + 1 WHERE `id` = '$topic' LIMIT 1 ");
      
      $sql->query("UPDATE `forum_forums` SET `last_topic` = '$topic:|:$name:|:".time()."' WHERE `id` = '".$topicRes['refid']."' LIMIT 1 ");

    }
        
    if ($moder){
      $user2 = $sql->query("SELECT `id`, `group_access` FROM `user` WHERE `id` = '".abs(intval($_POST['curator']))."' LIMIT 1 ")->fetch();
      if ($user2['group_access'] == 1 && intval($_POST['curator']) != $user_id)
        $sql->query("UPDATE `forum_topics` SET `curator` = '".abs(intval($_POST['curator']))."' WHERE `id` = '$topic' ");
    }
    
    header ('Refresh:1; URL=index.php?topic='.$topic.'&page=end');
    echo '<div class="post">Тема изменена<br /><a href="index.php?topic='.$topic.'&amp;page=end">Далее</a></div>';
    include H . 'engine/includes/foot.php';
    
  }
}

if (isset($_GET['closepoll'])){
  $pollSet = array('poll_mod' => $pollSet['poll_mod'], 'total_polls' => $pollSet['total_polls'], 'total_polled' => $pollSet['total_polled'], 'poll_close' => 1);
  $sql->query("UPDATE `forum_topics` SET
  `poll_set` = '".serialize($pollSet)."'
  WHERE `id`='$topic'
  ");
  $pollSet['poll_close'] = 1;
}

if (isset($_GET['openpoll'])){
  $pollSet = array('poll_mod' => $pollSet['poll_mod'], 'total_polls' => $pollSet['total_polls'], 'total_polled' => $pollSet['total_polled'], 'poll_close' => 0);
  $sql->query("UPDATE `forum_topics` SET
  `poll_set` = '".serialize($pollSet)."'
  WHERE `id` = '$topic'
  ");
  $pollSet['poll_close'] = 0;
}

if (isset($_POST['poll'])){
  if (empty($_POST['poll_name']))
    $error .= 'Вы не ввели имя голосования!<br />';
    
  if (!$topicRes['poll_name'] && (empty($_POST[0]) || empty($_POST[1])))
    $error .= 'Не введены как минимум два варианта голосования!<br />';
  
  if (!$error){
    if ($topicRes['poll_name']){
      $pollSet = array('poll_mod' => $pollSet['poll_mod'], 'total_polls' => $pollSet['total_polls'], 'total_polled' => $pollSet['total_polled'], 'poll_close' => $pollSet['poll_close']);
      $sql->query("UPDATE `forum_topics` SET
      `poll_name` = '$pollName',
      `poll_set` = '".serialize($pollSet)."'
      WHERE `id` = '$topic'
      ");
      while ($pollRes = $sql->fetch()){
        if (!empty ($_POST[$pollRes['id']])){
          $text = mb_substr(trim($_POST[$pollRes['id']]), 0, 100);
          $sql->query("UPDATE `forum_polls` SET
          `name` = '".my_esc($text)."'
          WHERE `id` = '".$pollRes['id']."'
          ");
        }
      }
      
      header ('Refresh:1; URL=index.php?topic='.$topic.'&page=end');
      echo '<div class="msg">Голосование изменено<br /><a href="index.php?topic='.$topic.'&amp;page=end">Далее</a></div>';
      include H . 'engine/includes/foot.php';
      
    }else{
      $polName = mb_substr($pollName, 0, 200);
      $pollSet = array('poll_mod' => intval($_POST['poll_mod']), 'total_polls' => 0, 'total_polled' => 0, 'poll_close' => 0);
      $sql->query("UPDATE `forum_topics` SET
      `poll_name` = '".my_esc($pollName)."',
      `poll_set` = '".serialize($pollSet)."'
      WHERE `id` = '$topic'
      ");
        
      if ($_POST['count_vars'] > 20)
        $_POST['count_vars'] = 20;
      elseif ($_POST['count_vars'] < 2)
        $_POST['count_vars'] = 2;
      for ($var = 0; $var < $_POST['count_vars']; $var++){
        $vars = mb_substr(trim($_POST[$var]), 0, 100);
        if (empty($vars)){
          continue;
        }
        $sql->query("INSERT INTO `forum_polls` SET
        `refid` = '$topic',
        `name` = '".my_esc($vars)."'
        ");
      }
      header ('Refresh:1; URL=index.php?topic='.$topic.'&page='.$page);
      echo '<div class="msg">Голосование создано<br /><a href="index.php?topic='.$topic.'&amp;page='.$page.'">Далее</a></div>';
      include H . 'engine/includes/foot.php';
      
    }
  }
}

if (isset($error))
  echo Core::msg_show($error);

echo '<form id="form" action="index.php?act=edittopic&amp;topic='.$topic.'&amp;page=1" method="post">';
echo '<div class="p_m">Название <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально 100 символов" />:<br /><input type="text" name="name" value="'.text($name).'" /></div>';
echo '<div class="rmenu">';
echo '<label><input type="checkbox" '.($topicRes['clip'] ? 'checked="checked" ' : '').'name="clip" value="1" />&nbsp;Закрепить 1-й пост</label><br />';
echo '<label><input type="checkbox" '.($topicRes['close'] ? 'checked="checked" ' : '').'name="close" value="1" />&nbsp;Закрыть</label><br />';
echo '<label><input type="checkbox" '.($topicRes['sticky'] ? 'checked="checked" ' : '').'name="sticky" value="1" />&nbsp;Закрепить</label><br />';
  
if ($moder)
  echo 'ID куратора: <input type="text" size="5" name="curator" value="'.$topicRes['curator'].'" /><br />';
  
echo '<input type="submit" name="send" value="Сохранить" /></div>';

if ($topicRes['poll_name']){
  echo '<div class="fmenu">Голосование <a href="index.php?act=delpoll&amp;topic='.$topic.'">удалить</a> <a href="index.php?act=edittopic&amp;topic='.$topic.'&amp;page=1&amp;'.($pollSet['poll_close'] ? 'openpoll">открыть' : 'closepoll">закрыть').'</a></div>';
  echo '<div class="p_m">Название <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально 200 символов" />:<br /><input type="text" name="poll_name" value="'.text($pollName).'" /><br />';
  while ($pollRes = $sql->fetch()){
    echo 'Вариант '.($i + 1).' <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально 100 символов" />:<br /><input type="text" name="'.$pollRes['id'].'" value="'.text($pollRes['name']).'" /><br />';          
    ++$i;
  }
}else{
  echo '<div class="fmenu">Голосование</div>';
  echo '<div class="p_m">Название <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально 200 символов" />:<br /><input type="text" name="poll_name" value="'.(isset($_POST['poll_name']) ? htmlspecialchars($_POST['poll_name']) : null).'" /><br />';

  if (isset($_POST['add']))
    ++$_POST['count_vars'];
  elseif (isset($_POST['del']))
    --$_POST['count_vars'];
  if (empty($_POST['count_vars']) || $_POST['count_vars'] < 2)
    $_POST['count_vars'] = 2;
  elseif ($_POST['count_vars'] > 20)
    $_POST['count_vars'] = 20;
  for ($vars = 0; $vars < $_POST['count_vars']; $vars++){
    echo 'Вариант '.($vars + 1).' <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально 100 символов" />:<br /><input type="text" name="'.$vars.'" value="'.(isset($_POST[$vars]) ? htmlspecialchars($_POST[$vars]) : null).'" /><br />';
  }
  echo '<input type="hidden" name="count_vars" value="'.intval($_POST['count_vars']).'" />';
  echo $_POST['count_vars'] < 20 ? '<br /><input type="submit" name="add" value=" + " />' : '';
  echo $_POST['count_vars'] > 2 ? '<input type="submit" name="del" value=" - " /><br />' : '<br />';
  echo '<label><input type="radio" value="0" name="poll_mod" '.(isset($_POST['poll_mod']) ? 'checked="checked" ' : '').'/> Можно выбирать только один вариант</label><br />
<label><input type="radio" value="1" name="poll_mod" '.(isset($_POST['poll_mod']) ? 'checked="checked" ' : '').'/> Можно выбирать несколько вариантов</label><br />';
}

echo '<input type="submit" name="poll" value="Сохранить" /></div></form>';