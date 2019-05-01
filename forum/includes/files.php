<?php

# fixed

if ($topic){
  $set['title'] = 'Файлы темы: '.title($topicRes['name']);
}else{
  $set['title'] = 'Файлы форума';
}
include H . 'engine/includes/head.php';

$old = time() - (3 * 24 * 3600);

if ($topic){
  $sql2 = "AND `topic` = '$topic' ";
  $url = 'topic='.$topic.'&amp;';
  $go = 'topic='.$topic;
  $tree = '<a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topic.'">'.text::output($topicRes['name']).'</a>  /  Файлы темы';
}else{
  $sql2 = '';
  $url = '';
  $go = '';
  $tree = '<a href="index.php">Форум</a>  /  Файлы форума';
}

echo '<div class="fmenu">'.$tree.'</div>';

$total = $sql->query("SELECT COUNT(*) FROM `forum_files` WHERE `tempid` = 0 $sql2")->result();
$page = new page($total, $set['p_str']);
if ($total){
    $page->display('index.php?act=files&amp;'.$url);
  
  $sql->query("SELECT * FROM `forum_files` WHERE `tempid` = 0 $sql2 ORDER BY `time` DESC LIMIT ".$page->limit());
  
  while($res = $sql->fetch()){
    echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
    echo $res['time'] > $old ? '<span class="red">новый</span> ' : '';
    echo '<span class="gray">'.Core::time($res['time']).'</span> <a href="index.php?post='.$res['refid'].'" title="Ссылка на пост">#</a><br />';
    echo show_file($res);

    if (!$topic){
      $topicRes = $sql->query("SELECT * FROM `forum_topics` WHERE `id`='".$res['topic']."' LIMIT 1 ")->fetch();
      echo '<div class="sub">Тема: <a href="index.php?topic='.$topicRes['id'].'">'.text::output($topicRes['name']).'</a></div>';
    }
    echo '</div>';
    ++$i;
  }
  
  if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';

echo '<div class="fmenu">Файлов: '.$total.'</div>';
  $page->display('index.php?act=files&amp;'.$url);
}else{
  echo '<div class="p_m">Пусто</div>';
}

include H . 'engine/includes/foot.php';