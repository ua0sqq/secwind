<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Новая тема';
include H . 'engine/includes/head.php';
$max_size = include H.'engine/includes/max_file_size.php';

if (!$forum){
  echo Core::msg_show('Отсутствует идентификатор форума!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$user_id){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (empty($_SESSION['tempid']))
  $_SESSION['tempid'] = rand(1111, 9999).$user_id;

if (empty($_SESSION['topic_tempid']))
  $_SESSION['topic_tempid'] = rand(1111, 9999);

$tree = array('<a href="index.php">Форум</a>', '<a href="index.php?forum='.$forumRes['id'].'">'.text::output($forumRes['name']).'</a>', 'Новая тема');
echo '<div class="fmenu">';
  foreach ($tree as $menu)
    {
        echo $menu . '  &nbsp;  ';
    }
  echo '</div>';



$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$text = isset($_POST['text']) ? trim($_POST['text']) : '';
$clip = isset($_POST['clip']) ? 1 : 0;
$close = isset($_POST['close']) ? 1 : 0;
$sticky = isset($_POST['sticky']) ? 1 : 0;
$pollName = isset($_POST['poll_name']) ? trim($_POST['poll_name']) : '';
$poll = 0;
$error = null;

if (isset($_POST['addfile'], $_FILES['file'])){
  $postId = 0;
  $topicId = $_SESSION['topic_tempid'];
  $tempId = $_SESSION['tempid'];
  require_once ('includes/fileupload.php');
}


if (isset($_POST['delfile'])){
  $array = $_POST['delfile'];
  if (is_array($array)){
    foreach ($array as $file => $val){
      require_once ('includes/delfile.php');
    }
  }else{
    $error = 'Возникла ошибка при удалении файла!';
  }
}

$fileReq = mysqli_query($sql->db, "SELECT * FROM `forum_files` WHERE `tempid` = '".intval($_SESSION['tempid'])."' LIMIT 11 ");
$total = $sql->num_rows($fileReq);

if (isset($_POST['send'])){

  if (!$name)
    $error .= 'Не введено название темы!<br />';

  if ($name && mb_strlen($name) > 100)
    $error .= 'Слишком длинное название темы!<br />';

  if (!$text)
    $error .= 'Не введен текст сообщения!<br />';

  $check = $sql->query("SELECT COUNT(*) FROM `forum_topics` WHERE `name` = '".my_esc($name)."' AND `refid` = '$forum' LIMIT 1 ")->result();

  if ($check)
    $error .= 'Тема с таким названием уже существует!<br />';

  if (!empty($pollName) && (empty($_POST[0]) || empty($_POST[1])))
    $error .= 'Не введены как минимум два варианта голосования<br />';
    
  if (empty($pollName) && !empty($_POST[0]))
    $error .= 'Вы не ввели заголовок голосования<br />';

  if (!$error){

 $sql->query("INSERT INTO `forum_topics` SET
    `refid` = '$forum',
    `time` = '".time()."',
    `name` = '".my_esc($name)."',
    `forum` = '".my_esc($forumRes['name'])."',
    `user_id` = '$user_id',
    `user` = '".$user['nick']."',
    `lastpost` = '$user[nick]:|:$user[id]',
    `count` = '1',
    `close` = '$close',
    `sticky` = '$sticky',
    `clip` = '$clip'
    ");
    $tid = mysqli_insert_id($sql->db);

$sql->query("INSERT INTO `forum_posts` SET
    `refid` = '$tid',
    `time` = '".time()."',
    `user_id` = '$user_id',
    `user` = '".$user['nick']."',
    `text` = '".my_esc($text)."',
    `files` = '$total'
    ");
    $pid = mysqli_insert_id($sql->db);

 

    if ($total){
      $sql->query("UPDATE `forum_files` SET
      `refid` = '$pid',
      `topic` = '$tid',
      `tempid` = ''
      WHERE `tempid` = '".intval($_SESSION['tempid'])."' ");
    }

    $sql->query("UPDATE `forum_forums` SET
    `count` = count + 1,
    `last_topic` = '$tid:|:".my_esc($name).":|:".time()."'
    WHERE `id` = '$forum' LIMIT 1 ");
    
    if (!empty($pollName) && !empty($_POST['count_vars'])){
      $pollName = mb_substr(trim($pollName), 0, 200);
      $pollSet = array('poll_close' => 0, 'poll_mod' => intval($_POST['poll_mod']), 'total_polls' => 0, 'total_polled' => 0);
      $sql->query("UPDATE `forum_topics` SET
      `poll_name` = '".my_esc($pollName)."',
      `poll_set` = '".serialize($pollSet)."'
      WHERE `id` = '$tid'
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
        `refid` = '$tid',
        `name` = '".my_esc($vars)."'
        ");
      }
      $poll = 1;  
    }    
    
    
    unset ($_SESSION['topic_tempid'], $_SESSION['tempid'], $_SESSION['filename']);
        
    header ('Refresh:1; URL=index.php?topic='.$tid);
    echo '<div class="post">Тема создана<br />Сообщение добавлено<br />';
    if ($total)
      echo 'Файлы загружены<br />';
    if ($poll)
      echo 'Голосование создано<br />';  
    echo '<a href="index.php?topic='.$tid.'">Далее</a></div>';
    include H . 'engine/includes/foot.php';
    
  }
}

if ($error)
  echo Core::msg_show($error);

if ($text && isset($_POST['preview'])){
  $textPreview = quote(text::output($text));
  echo '<div class="post"><strong>Предпросмотр:</strong><br />'.$textPreview.'</div>';
}

echo '<form id="form" name="form" action="index.php?act=addtopic&amp;forum='.$forum.'" method="post" enctype="multipart/form-data">';
echo '<div class="p_m">Название <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально 100 символов" />:<br /><input type="text" name="name" value="'.htmlspecialchars($name).'" /></div>';
echo '<div class="msg">Текст:<br /><textarea name="text">'.htmlspecialchars($text).'</textarea><br /><input type="submit" name="send" value="Отправить" /> <input type="submit" name="preview" value="Предпросм." /></div>';

if ($total){
echo '<div class="fmenu">Файлы: '.$total.' из 10</div>';
  while ($fileRes = $sql->fetch($fileReq)){
    echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
    echo show_file($fileRes);
    echo '<div class="sub"><input type="submit" name="delfile['.$fileRes['id'].']" onclick="return confirm(\'Вы действительно хотите удалить файл?\');" value="Удалить" /></div></div>';
    ++$i;
  }
}
if ($total < 10){
  echo '<div class="msg">Файл <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально '.text::size_data($max_size).'" />:<br /><input type="file" name="file" />';
  echo '<br /><input type="submit" name="addfile" value="Прикрепить" /></div>';
}
echo '<div class="p_m"><label><input type="checkbox" name="clip" value="1" '.($clip ? 'checked="checked"' : '').' />&nbsp;Закрепить 1-й пост</label><br />';
if ($moder){
  echo '<label><input type="checkbox" name="close" value="1" '.($close ? 'checked="checked"' : '').' />&nbsp;Закрыть</label><br />';
  echo '<label><input type="checkbox" name="sticky" value="1" '.($sticky ? 'checked="checked"' : '').' />&nbsp;Закрепить</label></div>';
  
  echo '<div class="fmenu">Голосование</div>';
  echo '<div class="p_m">Название <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально 200 символов" />:<br /><input type="text" name="poll_name" value="'.text($pollName).'" /><br />';
  if (isset($_POST['add']))
    ++$_POST['count_vars'];
  elseif (isset($_POST['del']))
    --$_POST['count_vars'];
  if (empty($_POST['count_vars']) || $_POST['count_vars'] < 2)
    $_POST['count_vars'] = 2;
  elseif ($_POST['count_vars'] > 20)
    $_POST['count_vars'] = 20;
    $_POST['poll_mod'] = isset($_POST['poll_mod']) ? 1 : 0;
  for ($vars = 0; $vars < $_POST['count_vars']; $vars++){
    echo 'Вариант '.($vars + 1).' <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально 100 символов" />:<br /><input type="text" name="'.$vars.'" value="'. (isset($_POST[$vars]) ? htmlspecialchars($_POST[$vars]) : null ).'" /><br />';
}
  echo '<input type="hidden" name="count_vars" value="'.intval($_POST['count_vars']).'" />';
  echo $_POST['count_vars'] < 20 ? '<br /><input type="submit" name="add" value=" + " />' : '';
  echo $_POST['count_vars'] > 2 ? '<input type="submit" name="del" value=" - " /><br />' : '<br />';
  echo '<label><input type="radio" value="0" name="poll_mod" '.($_POST['poll_mod'] == 0 ? 'checked="checked" ' : '').'/> Можно выбирать только один вариант</label><br />
<label><input type="radio" value="1" name="poll_mod" '.($_POST['poll_mod'] == 1 ? 'checked="checked" ' : '').'/> Можно выбирать несколько вариантов</label>';
}
echo '</div></form>';