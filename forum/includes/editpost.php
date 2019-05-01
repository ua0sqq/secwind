<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Редактирование';
include H . 'engine/includes/head.php';

$max_size = include H.'engine/includes/max_file_size.php';

if (!$post && !$topic){
  echo Core::msg_show('Отсутствует идентификатор сообщения или темы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (!$user_id){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php?topic='.$topic.'">Назад</a>');
  include H . 'engine/includes/foot.php';
  
}

$check = '';
$text = isset($_POST['text']) ? $_POST['text'] : $postRes['text'];

echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topic.'&amp;page=end">'.text::output($topicRes['name']).'</a>  /  Изменить пост</div>';

if (!$moder){
  if ($postRes['user_id'] != $user_id)
  $check = true;

  $firstPost = $sql->query("SELECT `id`, `user_id` FROM `forum_posts` WHERE `refid` = '".$postRes['refid']."' ORDER BY `time` ASC LIMIT 1 ")->fetch();
  if ($firstPost['id'] != $post && $postRes['time'] < time() - 300)
    $check = true;

  if ($topicRes['close'])
    $check = true;

  if ($check){
    echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php?topic='.$topic.'&amp;page=end#p'.$post.'">Назад</a>');
    include H . 'engine/includes/foot.php';
    
  }
}

if (isset($_POST['addfile'], $_FILES['file']['name'])){
  $postId = $post;
  $topicId = $topicRes['id'];
  $user_id = $postRes['user_id'];
  $tempId = '';
 // Core::get('class_upload', 'classes');

require_once ('includes/fileupload.php');

  $sql->query("UPDATE `forum_posts` SET
  `edit` = '$user[nick]:|:".time()."',
  `files`= `files` + 1
  WHERE `id` = '$post'
  ");
  $user_id = $user['id'];
}

elseif (isset($_POST['delfile']))
{
  $array = $_POST['delfile'];
  if (is_array($array)){
    foreach ($array as $file => $val){
      require_once ('includes/delfile.php');
    }
  
    if (!isset($error))
      $sql->query("UPDATE `forum_posts` SET `files` = files - 1 WHERE `id` = '$post' LIMIT 1 ");
  }else{
    $error = 'Возникла ошибка при удалении файла!';
  }
}

$fileReq = mysqli_query($sql->db, "SELECT * FROM `forum_files` WHERE `refid` = '".$postRes['id']."' LIMIT 11 ");
$total = mysqli_num_rows($fileReq);

if (isset($_POST['send'])){
  if (empty($text))
    $error = 'Не введен текст сообщения!<br />';

  if (!isset($error)){
    $sql->query("UPDATE `forum_posts` SET
    `text` = '".my_esc($text)."',
    `edit` = '$user[nick]:|:".time()."'
    WHERE `id` = '$post'
    ");

    header ('Refresh:1; URL=index.php?topic='.$topic.'&page=end#p'.$post);
    echo '<div class="msg">Сообщение изменено<br /><a href="index.php?topic='.$topic.'&amp;page=end#p'.$post.'">Далее</a></div>';
    include H . 'engine/includes/foot.php';
    unset ($_POST['addfile'], $_POST['delfile'], $_SESSION['filename']);
    
  }
}

if (isset($error))
  echo Core::msg_show($error);

if ($text && isset($_POST['preview'])){
  $textPreview = quote(text::output($text));
  echo '<div class="p_m"><strong>Предпросмотр:</strong><br />'.$textPreview.'</div>';
}

echo '<form id="form" name="form" action="index.php?act=editpost&amp;topic='.$topic.'&amp;post='.$post.'&amp;page=end" method="post" enctype="multipart/form-data">';
echo '<div class="rmenu">Текст:<br /><textarea name="text">'.text($text).'</textarea><br />';
echo '<input type="submit" name="send" value="Сохранить" /> <input type="submit" name="preview" value="Предпросм." /></div>';

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
  echo '<div class="msg">';
  echo 'Файл <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Максимально '.text::size_data($max_size).'." />:<br /><input type="file" name="file" />';
  echo '<br /><input type="submit" name="addfile" value="Прикрепить" /></div>';
}
echo '</form>';