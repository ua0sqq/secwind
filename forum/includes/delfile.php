<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$sql->query("SELECT * FROM `forum_files` WHERE `id` = '$file' LIMIT 1 ");
if (!$sql->num_rows())
  $error =  'Такого файла не существует, либо он был удален!';
else
{
  $fileRes = $sql->fetch();
  unlink('../forum/files/attach/'.$fileRes['name']);
  $sql->query("DELETE FROM `forum_files` WHERE `id` = '$file' LIMIT 1 ");
}