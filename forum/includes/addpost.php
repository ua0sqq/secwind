<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Новое сообщение';
include H . 'engine/includes/head.php';
$cur_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if (!$topic){
  echo Core::msg_show('Отсутствует идентификатор темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}
$quote = isset($_GET['quote']) ? '&amp;quote' : '';
$text = isset($_POST['text']) ? trim($_POST['text']) : '';
$quoteText = '';
$max_size = include H.'engine/includes/max_file_size.php';

if (!$user_id || ($topicRes['close'] && (!$moder))){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (empty($_SESSION['tempid']))
  $_SESSION['tempid'] = mt_rand(1111, 9999).$user_id;


echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topicRes['id'].'&amp;page='.$cur_page.'">'.text::output($topicRes['name']).'</a>  /  Новое сообщение</div>';


if ($post){
  $res = $postRes;

  if (isset($_GET['quote'])){
    $quoteText = isset($_POST['quote']) ? trim($_POST['quote']) : trim(preg_replace('#\[quote=(.*?)\](.*?)\[/quote\]#si', '', $postRes['text']));
  }else{
    $text = isset($_POST['text']) ? trim($_POST['text']) : '[b]'.$postRes['user'].'[/b], ';
  }
}

if (isset($_POST['addfile']) && $_FILES['file']['name']){
  $postId = 0;
  $topicId = $topic;
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

  if (!$text || ($post && $text == '[b]'.$postRes['user'].'[/b],'))
    $error .= 'Не введен текст сообщения!<br />';
    
  if ($quote && !$quoteText)
    $error .= 'Не введен текст цитаты!<br />';  

  if (!isset($error)){
    
    $quoteText = $quote ? '[quote='.$postRes['user'].']'.$quoteText.'[/quote]' : '';
    
    $sql->query("SELECT * FROM `forum_posts` WHERE `user_id` = '$user_id' AND `refid` = '$topic' ORDER BY `time` DESC LIMIT 1 ");
    if ($sql->num_rows() > 0){
      $checkText = $sql->fetch();
      if ($checkText['text'] == $quoteText.$text) {
        echo Core::msg_show('Такое сообщение уже было!<br /><a href="index.php?topic='.$topic.'&amp;page='.$cur_page.'">Назад</a>');
        include H . 'engine/includes/foot.php';
        
      }
    }

    $sql->query("INSERT INTO `forum_posts` SET
    `refid` = '$topic',
    `time` = '$time',
    `user_id` = '$user_id',
    `user` = '$user[nick]',
    `text` = '".my_esc($quoteText.$text)."',
    `files` = '$total'");
    $pid = mysqli_insert_id($sql->db);
    
    if ($post)
      journal_add($postRes['user_id'], 'Вам ответили в теме: <a href="index.php?post='.$pid.'">'.$topicRes['name'].'</a>');
    
    if ($total){
      $sql->query("UPDATE `forum_files` SET
      `refid` = '$pid',
      `tempid` = ''
      WHERE `tempid` = '".intval($_SESSION['tempid'])."' ");
    }

    $sql->query("UPDATE `forum_topics` SET
    `time` = '$time',
    `lastpost` = '$user[nick]:|:$pid',
    `count` = count + 1 WHERE `id` = '$topic' LIMIT 1 ");

    $sql->query("UPDATE `forum_forums` SET `last_topic` = '$topic:|:".$topicRes['name'].":|:$time' WHERE `id` = '".$topicRefid['id']."' LIMIT 1 ");

    unset ($_SESSION['tempid'], $_SESSION['filename']);
    header ('Location: index.php?topic='.$topic.'&page=end#p'.$pid);
    
  }
}

if (isset($error))
  echo Core::msg_show($error);

if ($text && isset($_POST['preview'])){
  $quotePreview = $quote ? '[quote='.$postRes['user'].']'.$quoteText.'[/quote]' : '';
  $textPreview = quote(text::output($quotePreview.$text));

  echo '<div class="p_m"><strong>Предпросмотр:</strong><br />'.$textPreview.'</div>';
}

echo '<form id="form" name="form" action="index.php?act=addpost&amp;topic='.$topic.($post ? '&amp;post='.$post : '').$quote.'&amp;page='.$cur_page.'" method="post" enctype="multipart/form-data">';
echo '<div class="msg">'.($quote ? 'Цитата ('.$postRes['user'].'):<br /><textarea name="quote">'.htmlspecialchars($quoteText).'</textarea><br />' : '').'Текст:<br /><textarea name="text">'.htmlspecialchars($text).'</textarea><br /><input type="submit" name="send" value="Отправить" /> <input type="submit" name="preview" value="Предпросм." /></div>';

echo '<div class="fmenu">Файлы: '.$total.' из 10</div>';
if ($total){
  while ($fileRes = mysqli_fetch_assoc($fileReq)){
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
echo '</form>';