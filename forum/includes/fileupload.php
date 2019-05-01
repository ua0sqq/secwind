<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!


require_once H.'engine/classes/class_upload.php';

$fileName = $_FILES['file']['name'];

$fileSize = $_FILES['file']['size'];

$ext = pathinfo($fileName, PATHINFO_EXTENSION);

if ($fileSize > $max_size)
  $error .= 'Вес файла превышает '.text::size_data($max_size).' !<br />';

if (mb_strlen($fileName) > 50)
  $error .= 'Длинна имени файла превышает 50 символов!<br />';

if (preg_match("/[^\da-zA-Z_\-.]+/", $fileName))
  $error .= 'В имени файла присутствуют запрещенные символы!<br />';

$check = 0;
if (!empty($_SESSION['filename'])){
  $check = $_SESSION['filename'] == $_FILES['file']['name'] ? 1 : 0;
}

if ($check)
  $error .= 'Вы уже загружали этот файл!<br />';

if (!isset($error)){

  $handle = new Upload($_FILES['file'], 'ru_RU');
  if ($handle->uploaded){
    $handle->file_new_name_body = translit(str_replace('.'.$ext, '', $_FILES['file']['name']));
    $handle->Process('../forum/files/attach/');
    if ($handle->processed){
      $sql->query("INSERT INTO `forum_files` SET
      `refid` = '$postId',
      `topic` = '$topicId',
      `time` = '".time()."',
      `user_id` = '$user_id',
      `name` = '".my_esc($handle->file_dst_name)."',
      `tempid` = '$tempId'
      ");
      $_SESSION['filename'] = $_FILES['file']['name']; 
    }else{
      $error .= $handle->error;
    }
  }
}