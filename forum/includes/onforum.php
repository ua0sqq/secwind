<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!



$set['title'] .= ' | Онлайн';
include H . 'engine/includes/head.php';

if (!$user_id){
  echo Core::msg_show('Только для авторизованных!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

$onltime = time() - 300;
$intopic = $topic ? '&amp;topic='.$topic : '';


  $guests =  0;
  $url = '';
  $top = 'Пользователи ';
  $bottom = 'Пользователей: ';

if ($topic){

  echo '<div class="fmenu"><a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topicRes['id'].'">'.text::output($topicRes['name']).'</a>  / '.$top.'в теме</div>';

    $total = $sql->query("SELECT COUNT(*) FROM `user` WHERE `date_last` > $onltime AND `url` = 'forum,$topic' " )->result();
    $page = new page($total, $set['p_str']);
  
  if ($total){

    $page->display('index.php?act=onforum&amp;topic='.$topic.'&amp;'.$url);

    $sql->query("SELECT * FROM `user` WHERE `date_last` > $onltime AND `url` = 'forum,$topic' ORDER BY `name` ASC LIMIT ".$page->limit());
    while ($res = $sql->fetch()){
      if ($res['id'] == $user_id) echo '<div class="menu_razd">';
      else echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
      echo Core::user_show($res);
      echo '</div>';
      ++$i;
    }
    if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';
    echo '<div class="fmenu">'.$bottom.$total.'</div>';
     $page->display('index.php?act=onforum&amp;topic='.$topic.'&amp;'.$url);
    
  }else{
    echo '<div class="p_m">Пусто</div>';
  }
}else{

  echo '<div class="fmenu"><a href="index.php">Форум</a>  /  '. $top.'на форуме</div>';

  $total = $sql->query("SELECT COUNT(*) FROM `user` WHERE `date_last` > $onltime AND `url` LIKE 'forum%'" )->result();
  $page = new page($total, $set['p_str']);
  if ($total){

   $page->display('index.php?act=onforum&amp;'.$url);

    $sql->query("SELECT * FROM `user` WHERE `date_last` > $onltime AND `url` LIKE 'forum%' ORDER BY `name` ASC LIMIT ".$page->limit());
    while ($res = $sql->fetch()){
      if ($res['id'] == $user_id) echo '<div class="menu_razd">';
      else echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';

      $place = explode(',', $res['place']);
      if ($place['0'] == 'forum' && intval($place['1'])){
        $where = $sql->query("SELECT `name`, `count` FROM `forum_topics` WHERE `id` = '".$place['1']."' LIMIT 1 ")->result();

        $text = 'В теме: <b><a href="index.php?topic='.$place['1'].'">'.text::output($where['name']).'</a>';
        if ($where['count'] > 10)
          $text .= ' <a href="index.php?topic='.$place['1'].'&amp;page='.ceil($where['count'] / $set['p_str']).'">&gt;&gt;</a>';
        $text .= '</b>';
      }else{
        $text = '<b><a href="index.php">На форуме</a></b>';
      }

      if ($guests)
        $res['name'] = preg_match('/bot/', $res['browser']) ? 'Бот' : '';
      $array = array('post' => $text);
      echo Core::user_show($res, $array);
      echo '</div>';
      ++$i;
    }
    if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';

    echo '<div class="fmenu">'.$bottom.$total.'</div>';
    
    $page->display('index.php?act=onforum&amp;'.$url);
    
      
  }else{
    echo '<div class="p_m">Пусто</div>';
  }
}
