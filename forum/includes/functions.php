<?php


$forum = isset ($_GET['forum']) ? abs(intval($_GET['forum'])) : false;
$topic = isset ($_REQUEST['topic']) ? abs(intval($_REQUEST['topic'])) : false;
$post = isset ($_REQUEST['post']) ? abs(intval($_REQUEST['post'])) : false;
$file = isset ($_REQUEST['file']) ? abs(intval($_REQUEST['file'])) : false;

function forum_counter($num = 0){
    global $sql;
  if (Core::$user_id){
    $total = $sql->query("SELECT COUNT(*) FROM `forum_topics` LEFT JOIN `forum_readed` ON `forum_topics`.`id` = `forum_readed`.`topic` AND `forum_readed`.`user_id` = '".Core::$user_id."' WHERE (`forum_readed`.`topic` Is Null OR `forum_topics`.`time` > `forum_readed`.`time`) ")->result();
    if ($num == 1){
      return $total;
    }elseif($num == 2){
      if ($total)
        return ' <span class="err"><a href="index.php?act=last&amp;unread">+'.$total.'</a></span>';
      else
        return false;
    }else{
      return '<a href="index.php?act=last&amp;unread"><div class="menu_razd">Непрочитанные'.($total ? ' <span class="err">('.$total.')</span>' : '').'</div></a>
<a href="index.php?act=my&amp;journal"><div class="menu_razd">Журнал форума'.(journal_new() ? journal_new() : '').'</div></a>';
    }
  }
}

function quote ($text = ''){
  return preg_replace('#\[quote=(.+?)](.+?)\[/quote]#is', '<div class="p_m">$1 писал(а)</div><div class="post">$2</div>', $text);
}

function show_file($file = array()){
  $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
  $out = '<img src="/forum/images/attach.gif" alt="+" /><a href="index.php?act=download&amp;file='.$file['id'].'">'.$file['name'].'</a>';
  if ($ext == 'jar')
    $out .= ' | <a href="/forum/index.php?act=download&amp;file='.$file['id'].'&amp;jad">jad</a>';
  return $out .= ' ('.round(filesize('../forum/files/attach/'.$file['name']) / 1024, 2).' кб.) Скачали: '.$file['down'].' раз.';
}

function show_folder($folder = array()){
    global $admin, $sql;
  if (file_exists(('../forum/files/icons/'.$folder['id'].'.png')))
    $out = '<img src="../files/forum/icons/'.$folder['id'].'.png" alt="" />';
  else
    $out = '<img src="images/folder.png" alt="-" />';
  
  $out .= ' <a href="index.php?forum='.$folder['id'].'"><strong>'.text::output($folder['name']).'</strong></a>'.($folder['type'] ? ' ('.$folder['count'].')' : '');
  
  if ($admin){
    $out .= '  |  <a href="javascript:show_hide(\'adm'.$folder['id'].'\');">Адм.</a><span id="adm'.$folder['id'].'" style="display: none; font-size: x-small;"> <a href="index.php?act=moveforum&amp;forum='.$folder['id'].'&amp;up">Вверх</a> <a href="index.php?act=moveforum&amp;forum='.$folder['id'].'&amp;down">Вниз</a> <a href="index.php?act=editforum&amp;forum='.$folder['id'].'">Изменить</a> <a href="index.php?act=delforum&amp;forum='.$folder['id'].'">Удалить</a></span>';
  }
  
    if ($folder['text'])
      $out .= '<div>'.text::output($folder['text']).'</div>';
  
  return $out;
}

function show_topic($res, $sub = 0, $new = 0){
  global $sql;
  $out = '';
  /*
  if ($user_id && !$new){
    $readed = $sql->query("SELECT COUNT(*) FROM `forum_readed` WHERE `time` >= '".$res['time']."' AND `topic` = '".$res['id']."' AND `user_id` = '".$user_id."' LIMIT 1 ")->result();
$out .= $readed ? '' : '<img src="images/new.png" alt="+" title="Есть новые сообщения" /> ';
  }*/
  $out .= '<img src="/forum/images/topic.png" alt="-" /> ';
  if ($res['close'])
    $out .= '<img src="/forum/images/close.png" alt="#" title="Тема закрыта" /> ';
  if ($res['sticky'])
    $out .= '<img src="/forum/images/sticky.png" alt="^" title="Тема закреплена" /> ';
  if ($res['poll_name'])
    $out .= '<img src="/forum/images/poll.png" alt="*" title="Голосование" /> ';

  $out .= '<a href="/forum/index.php?topic='.$res['id'].'"><strong>'.text::output($res['name']).'</strong></a> ('.$res['count'].')';
/*
  if ($user_id && (!$readed || $new))
    $out .= ' <a href="/forum/index.php?topic='.$res['id'].'&amp;unread">Новые</a>';
*/

    $out .= ' <a href="/forum/index.php?topic='.$res['id'].'&amp;page=end" title="К последней странице">  &gt;&gt;</a>';

  if ($sub)
    $out .= '<div class="sub">'.$sub.'</div>';
  return $out;
}

function text($text){
  return htmlentities($text, ENT_QUOTES, 'UTF-8');
}

function title($string){
  $out = strtr($string, array('&quot;' => '', '&amp;' => '', '&lt;' => '', '&gt;' => '', '&#039;' => ''));
  $out = mb_strlen($string) > 50 ? mb_substr($out, 0, 50).'...' : $out;
  return text::output($out);
}

function translit($str, $direction = 0){
  $ru = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ь','ы','ъ','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ь','Ы','Ъ','Э','Ю','Я',' ','ґ','ї','є','Ґ','Ї','Є');
  $en = array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sh','','y','','e','u','ya','A','B','V','G','D','E','YO','ZH','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','H','C','CH','SH','SH','','Y','','E','U','YA','_','gg','ji','ye','GG','JI','YE');
  if ($direction == 0)
	  return str_replace($ru, $en, $str);
	else
	  return str_replace($en, $ru, $str);
}