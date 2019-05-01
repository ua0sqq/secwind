<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!


$cur_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($user_id){
  $lastPost = explode(':|:', $topicRes['lastpost']);
   $sql->query("SELECT * FROM `forum_readed` WHERE `topic` = '$topic' AND `user_id` = '$user_id' LIMIT 1 ");
  if ($sql->num_rows() > 0){
    $readedRes = $sql->fetch();
    if ($topicRes['time'] > $readedRes['time'])
      $sql->query("UPDATE `forum_readed` SET `time` = '".time()."', `lastpost` = '".$lastPost['1']."' WHERE `topic` = '$topic' AND `user_id` = '$user_id' ");
  }else{
    $sql->query("INSERT INTO `forum_readed` SET  `topic` = '$topic', `user_id` = '$user_id', `time` = '".time()."', `lastpost` = '".$lastPost['1']."' ");
  }
}
//$pollSet = $sql->query('select `poll_set` from `forum_topics` where `id` = '.$topic)->result();
echo forum_counter();

$tree = array('<a href="index.php">Форум</a>', '<a href="index.php?forum='.$topicRes['refid'].'">'.text::output($topicRes['forum']).'</a>', text::output($topicRes['name']));
echo '<div name="up" id="up" class="fmenu"><a href="#down">↓</a> ';
foreach ($tree as $menu)
{
    echo $menu . ' &nbsp; ';
}
echo '</div>';

if ($topicRes['close'])
  echo '<div class="rmenu">Тема закрыта!</div>';

if ($topicRes['poll_name']){
  if ($user_id)
    $pollCheck = $sql->query("SELECT COUNT(*) FROM `forum_polled` WHERE `refid` = '$topic' AND `user_id` = '$user_id' LIMIT 1 ")->result();


  echo '<div class="msg"><strong>'.text::output($topicRes['poll_name']).'</strong><br />';
  if ($pollSet['poll_close'] == 0 && !isset($_GET['results']) && $user_id && $pollCheck == 0 && !$topicRes['close']){
    echo '<form action="index.php?act=poll&amp;topic='.$topic.'&amp;page='.$cur_page.'" method="post">';
    while ($pollRes = mysqli_fetch_assoc($pollReq))
    {
      echo '<label><input type="'.($pollSet['poll_mod'] < 1 ? 'radio' : 'checkbox').'" name="var[]" value="'.$pollRes['id'].'" /> '.text::output($pollRes['name']).'</label><br />';          
    }
    echo '<input type="submit" name="poll" value="Отдать голос" /><br /><a href="index.php?topic='.$topic.'&amp;page='.$cur_page.'&amp;results"><strong>Результаты</strong></a></form>';  
  }else{
   
    while ($pollRes = mysqli_fetch_assoc($pollReq)){
      echo text::output($pollRes['name']).' ('.$pollRes['count'].')<br />';
      $poll_count = $pollSet['total_polls'] ? @round(100 / $pollSet['total_polls'] * $pollRes['count']) : 0;
      echo '<div style="background: #fff; border: 1px solid #ccc;"><div style="background:green; height: 5px; width: '.$poll_count.'%; min-width: 3px"></div></div>';
    }

    echo 'Проголосовало: '.($user_id ? '<a href="index.php?act=polled&amp;topic='.$topic.'">'.$pollSet['total_polled'].'</a>' : $pollSet['total_polled']);
    if ($user_id && !$pollCheck && !$pollSet['poll_close'])
      echo '<br /><a href="index.php?topic='.$topic.'&amp;page='.$cur_page.'"><strong>Отдать голос</strong></a>';
  }
  echo '</div>';
}

$total = $sql->query("SELECT COUNT(*) FROM `forum_posts` WHERE `refid` = '$topic' ")->result();
$page = new page($total, $set['p_str']);
if ($total){
  $req = mysqli_query($sql->db, "SELECT `forum_posts`.*, `forum_posts`.`id` AS `pid`, `forum_posts`.`user` AS `name`, `user`.`date_last`,`user`.`nick`, `user`.`id`, `user`.`pol` FROM `forum_posts` LEFT JOIN `user` ON `forum_posts`.`user_id` = `user`.`id` WHERE `forum_posts`.`refid` = '$topic' ORDER BY `forum_posts`.`time` ASC LIMIT ".$page->limit());

    $page->display('index.php?topic='.$topic.'&amp;');

  if ($topicRes['clip'] && $cur_page > 1){
    echo '<div class="user">';
    $firstPost =$sql->query("SELECT `forum_posts`.*, `forum_posts`.`id` AS `pid`, `user`.`id`, `user`.`pol`, `user`.`nick` FROM `forum_posts` LEFT JOIN `user` ON `forum_posts`.`user_id` = `user`.`id` WHERE `forum_posts`.`refid` = '$topic' ORDER BY `forum_posts`.`time` ASC LIMIT 1 ")->fetch();

    $postHeader = ' <span class="gray">'.Core::time($firstPost['time']).'</span> <a href="index.php?post='.$firstPost['pid'].'" title="Ссылка на пост">#1</a>';
    
    $postText = quote(text::output($firstPost['text']));

    if ($firstPost['files']){
      $postFile = $sql->query("SELECT * FROM `forum_files` WHERE `refid` = '".$firstPost['pid']."' LIMIT ".$firstPost['files']." ");
      $postText .= '<div class="func">Файл(ы):<br />';
      while ($postFileRes = $sql->fetch()){
        $postText .= show_file($postFileRes).'<br />';
      }
      $postText .= '</div>';
    }

    if ($firstPost['edit']){
      $edit = explode(':|:', $firstPost['edit']);
      $postText .= '<div style="font-size: x-small; color: gray">Изменил(а) '.$edit['0'].' '.Core::time($edit['1']).'</div>';
    }
    
    if ($firstPost['rating'] > 0) $color = 'C0FFC0';
    elseif ($firstPost['rating'] < 0) $color = 'F196A8';
    else $color = 'CCCCCC';
    if ($user_id && $firstPost['user_id'] != $user_id){
      $postText .= '<div style="font-size: x-small">Рейтинг: <a href="index.php?act=rating&amp;topic='.$topic.'&amp;post='.$firstPost['pid'].'&amp;plus"><img src="images/plus.png" alt="+" /></a> <span style="background:#'.$color.'">&nbsp;'.$firstPost['rating'].'&nbsp;</span> <a href="index.php?act=rating&amp;topic='.$topic.'&amp;post='.$firstPost['pid'].'&amp;minus"><img src="images/minus.png" alt="-" /></a></div>';
    }else{
      $postText .= '<div style="font-size: x-small">Рейтинг: <span style="background:#'.$color.'">&nbsp;'.$firstPost['rating'].'&nbsp;</span></div>';
    }
    
    $postSub = '';
    if ((($user_id == $firstPost['user_id']) && !$topicRes['close']) || $moder){
      $postSub .= '<a href="index.php?act=editpost&amp;topic='.$topic.'&amp;post='.$firstPost['pid'].'&amp;page='.$cur_page.'">Изменить</a>  |  <a href="index.php?act=delpost&amp;topic='.$topic.'&amp;post='.$firstPost['pid'].'&amp;page='.$cur_page.'">Удалить</a>';
    }
    $postText .= '<br />'.$postSub;
    $postArray = array('staus' => $postHeader, 'post' => $postText);
    echo Core::user_show($firstPost, $postArray);
    echo '</div>';
  }

  if ($moder)
    echo '<form action="index.php?act=massdelpost&amp;topic='.$topic.'&amp;page='.$cur_page.'" method="post">';
  
  $i = $page->start();
  
  while ($res = $sql->fetch($req)){
    ++$i;
    echo $i % 2 ? '<div name="p'.$res['pid'].'" id="p'.$res['pid'].'" class="p_m">' : '<div name="p'.$res['pid'].'" id="p'.$res['pid'].'" class="p_t">';
   
    $header = ' <span class="gray">'.Core::time($res['time']).'</span> <a href="index.php?post='.$res['pid'].'" title="Ссылка на пост">#'.$i.'</a>';

    $text = quote(text::output($res['text']));


    if ($res['files']){
      $file = $sql->query("SELECT * FROM `forum_files` WHERE `refid` = '".$res['pid']."' LIMIT ".$res['files']." ");
      $text .= '<div class="func">Файл(ы):<br />';
      while($fileRes = $sql->fetch()){
        $text .= show_file($fileRes).'<br />';
      }
      $text .= '</div>';
    }
    
    if ($res['edit']){
      $edit = explode(':|:', $res['edit']);
      $text .= '<div style="font-size: x-small; color: gray">Изменил(а) '.$edit['0'].' '.Core::time($edit['1']).'</div>';
    }
    
    if ($res['rating'] > 0) $color = 'C0FFC0';
    elseif ($res['rating'] < 0) $color = 'F196A8';
    else $color = 'CCCCCC';
    if ($user_id && $res['user_id'] != $user_id && !$topicRes['close']){
      $text .= '<div style="font-size: x-small">Рейтинг: <a href="index.php?act=rating&amp;topic='.$topic.'&amp;post='.$res['pid'].'&amp;plus"><img src="images/plus.png" alt="+" /></a> <span style="background:#'.$color.'">&nbsp;'.$res['rating'].'&nbsp;</span> <a href="index.php?act=rating&amp;topic='.$topic.'&amp;post='.$res['pid'].'&amp;minus"><img src="images/minus.png" alt="-" /></a></div>';
    }else{
      $text .= '<div style="font-size: x-small">Рейтинг: <span style="background:#'.$color.'">&nbsp;'.$res['rating'].'&nbsp;</span></div>';
    }
     
    $sub = '';
    if (($user_id && $user_id != $res['user_id'] && !$topicRes['close']) || ($user_id != $res['user_id'] && $moder)){
      $sub .= '<a href="index.php?act=addpost&amp;post='.$res['pid'].'&amp;topic='.$topic.'&amp;page='.$cur_page.'">Ответ</a> <a href="index.php?act=addpost&amp;quote&amp;post='.$res['pid'].'&amp;topic='.$topic.'&amp;page='.$cur_page.'">Цитата</a>';
    }

    if ((($user_id == $res['user_id'] && !$topicRes['close'] && $res['time'] > time() - 300) || ($user_id == $res['user_id'] && (!$i && (!$cur_page || $cur_page == 1)))) || ($moder)){
      $sub .= ' <a href="index.php?act=editpost&amp;topic='.$topic.'&amp;post='.$res['pid'].'&amp;page='.$cur_page.'">Изменить</a>  |  <a href="index.php?act=delpost&amp;topic='.$topic.'&amp;post='.$res['pid'].'&amp;page='.$cur_page.'">Удалить</a>';
    }
    if ($moder)
      $sub .= '<input type="checkbox" name="delch[]" value="'.$res['pid'].'" /> ';

$text .= $sub;

    $array = array('status' => $header, 'post' => $text);
    echo Core::user_show($res, $array);
    echo '</div>';
    
  }
  if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';

  if ($moder)
    echo '<div class="rmenu"><input type="submit" value="Удалить" /></div></form>';

}else{
  echo '<div class="p_m">Пусто</div>';
}

if (($user_id && !$topicRes['close']) || $moder){
  echo '<div class="msg"><form id="form" name="form" action="index.php?act=addpost&amp;topic='.$topic.'&amp;page='.$cur_page.'" method="post">';

    echo 'Быстрый ответ <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="Для предпросмотра или прикрепления файлов нажмите на кнопку: Расшир. форма" />:<br /><textarea name="text"></textarea>';
    echo '<br /><input type="submit" name="send" value="Отправить" /> <input type="submit" name="preview" value="Расшир. форма" />';

  echo '</form></div>';
}

if ($total){
  echo '<div  name="down" id="down" class="fmenu"><a href="#up">↑</a> Сообщений: '.$total.'</div>';

    $page->display('index.php?topic='.$topic.'&amp;');

}

$onltime = time() - 300;
$online_u = $sql->query("SELECT COUNT(*) FROM `user` WHERE `date_last` > $onltime AND `url` = 'forum,$topic' ")->result();

if ($topicRes['curator']){
  $curator = $sql->query("SELECT `lastdate`, `sex`, `datereg`, `id`, `name` FROM `users` WHERE `id` = '".$topicRes['curator']."' LIMIT 1 ")->fetch();
  $arrayCurator = array('header' => '', 'body' => '', 'sub' => '', 'iphide' => 1);
  core::$user_set['avatar'] = 0;
  echo '<p>Куратор темы: '.functions::display_user($curator, $arrayCurator).'</p>';
}

echo '<p><a href="index.php?act=search&amp;topic='.$topic.'">Поиск</a><br />
<a href="index.php?act=onforum&amp;topic='.$topic.'">Кто в теме</a> ('.$online_u.')<br />
<a href="rss.php?topic='.$topic.'">Rss-канал</a><br />
<a href="index.php?act=loadtopic&amp;topic='.$topic.'&amp;">Скачать тему</a><br />
';

if ($user_id){
  $favourites = $sql->query("SELECT COUNT(*) FROM `forum_favourites` WHERE `topic` = '$topic' AND `user_id` = '$user_id' LIMIT 1 ")->result();
  echo $favourites ? '<a href="index.php?act=my&amp;delfavourite&amp;topic='.$topic.'&amp;page='.$cur_page.'">Из избранного</a><br />' : '<a href="index.php?act=my&amp;addfavourite&amp;topic='.$topic.'&amp;page='.$cur_page.'">В избранное</a><br />';
}

echo '<a href="index.php?act=files&amp;topic='.$topic.'">Файлы темы</a></p>';

if ($moder){
  echo '<div class="rmenu"><a href="index.php?act=edittopic&amp;topic='.$topic.'&amp;page='.$cur_page.'">Изменить</a> &nbsp; ';
  echo $moder ? ' <a href="index.php?act=deltopic&amp;topic='.$topic.'">Удалить</a> &nbsp; ' : '';
  echo ' <a href="index.php?act=movetopic&amp;topic='.$topic.'&amp;page='.$cur_page.'">Перенести</a>';
  echo '</div>';
}