<?php

# Forum by seg0ro (http://mobilarts.ru)

include '../engine/includes/start.php';
require_once ('includes/functions.php');
include_once 'includes/journal.php';
$set['title'] = 'Форум';
$i = 1;
Core::get(array('page.class', 'text.class'), 'classes');

if ($forum){
  $sql->query("SELECT * FROM `forum_forums` WHERE `id` = '$forum' LIMIT 1 ");
  if (!$sql->num_rows()){
    include H . 'engine/includes/head.php';
    echo Core::msg_show('Такого форума не существует!<br /><a href="index.php">Форум</a>');
    include H . 'engine/includes/foot.php';
    
  }
  $forumRes = $sql->fetch();

  if ($forumRes['refid'])
    $forumRefid = $sql->query("SELECT `name` FROM `forum_forums` WHERE `id` = '".$forumRes['refid']."' LIMIT 1 ")->fetch();
    $set['title'] = htmlspecialchars($forumRes['name']);
}

if($topic){
  $topicReq = $sql->query("SELECT * FROM `forum_topics` WHERE `id` = '$topic' LIMIT 1 ");
  if (!$sql->num_rows()){
    include H . 'engine/includes/head.php';
    echo Core::msg_show('Такой темы не существует!<br /><a href="index.php">Форум</a>');
    include H . 'engine/includes/foot.php';
    
  }
  $topicRes = $sql->fetch();

  if (!empty($topicRes['poll_name'])){
    $pollReq = mysqli_query($sql->db, "SELECT * FROM `forum_polls` WHERE `refid` = '$topic' ORDER BY `id` ASC LIMIT 20 ");
    $pollSet = unserialize($topicRes['poll_set']);
  }

  $set['title'] = htmlspecialchars($topicRes['name']);

  if($post){
    $sql->query("SELECT * FROM `forum_posts` WHERE `id` = '$post' LIMIT 1 ");
    if (!$sql->num_rows()){
      include H . 'engine/includes/head.php';
      echo Core::msg_show('Такого поста не существует!<br /><a href="index.php">Форум</a>');
      include H . 'engine/includes/foot.php';
      
    }
    $postRes = $sql->fetch();

    $set['title'] = htmlspecialchars($topicRes['name']).' | Пост: '.$post;
  }else{
    $topicRefid = $sql->query("SELECT `id`, `refid`, `name` FROM `forum_forums` WHERE `id` = '".$topicRes['refid']."' LIMIT 1 ")->result();
  }

  if ($user_id && $topicRes['curator'] == $user_id)
    $rights = 3;

}

if($file){
  $fileReq = mysqli_query($sql->db, "SELECT * FROM `forum_files` WHERE `id` = '$file' LIMIT 1 ");
  if (!mysqli_num_rows($fileReq)){
    include H . 'engine/includes/head.php';
    echo Core::msg_show('Такого файла не существует!<br /><a href="index.php">Форум</a>');
    include H . 'engine/includes/foot.php';
    
  }
  $fileRes = mysqli_fetch_assoc($fileReq);

  $fileRefid = $sql->query("SELECT `id`, `name`, `close` FROM `forum_topics` WHERE `id` = '".$fileRes['topic']."' LIMIT 1 ")->fetch();

  $set['title'] = title($fileRefid['name']).' | Файл: '.$file;
}

$arrayIncludes = array('addforum', 'addpost', 'addtopic', 'clean', 'delfile', 'delforum', 'delpoll', 'delpost', 'deltopic', 'download', 'editforum', 'editpost', 'edittopic', 'files', 'last', 'loadtopic', 'massdelpost', 'movetopic', 'moveforum', 'my', 'onforum', 'poll', 'polled', 'rating', 'search');

if (in_array($act, $arrayIncludes) && file_exists('includes/'.$act.'.php')){
  require_once ('includes/'.$act.'.php');
}else{
  
  include H . 'engine/includes/head.php';

    
  if ($topic){
    if (isset($_GET['unread'])){
      $resReaded = $sql->query("SELECT `time`, `lastpost` FROM `forum_readed` WHERE `topic` = '$topic' AND `user_id` = '$user_id' LIMIT 1 ")->fetch();
      if ($resReaded['time'] && $resReaded['lastpost'] > 0){
        $gopage = ceil($sql->query("SELECT COUNT(*) FROM `forum_posts` WHERE `refid` = '$topic' AND `id` <= '".$resReaded['lastpost']."' ")->result() / $set['p_str']);
        header ('Location: index.php?topic='.$topic.'&page='.$gopage.'#p'.$resReaded['lastpost']);
      }else{
        header ('Location: index.php?topic='.$topic);
      }
      include H . 'engine/includes/foot.php';
      
    }
    
    require_once('includes/showtopic.php');
    include H . 'engine/includes/foot.php';
    
  }
  
  if ($post){
    $sql->query("SELECT `refid` FROM `forum_posts` WHERE `id` = '$post' LIMIT 1 ");
    if (!$sql->num_rows()){
      include H . 'engine/includes/head.php';
      echo Core::msg_show('Такого поста не существует!<br /><a href="index.php">Форум</a>');
      include H . 'engine/includes/foot.php';
      
    }
    $postRes = $sql->fetch(); 
  
    $go = ceil(($sql->query("SELECT COUNT(*) FROM `forum_posts` WHERE `refid` = '".$postRes['refid']."' AND `id` <= '$post' ")->result()) / $set['p_str']);

    header('Location: index.php?topic='.$postRes['refid'].'&page='.$go.'#p'.$post);

    include H . 'engine/includes/foot.php';
    
  }
  if ($admin)
    echo '
<script language="JavaScript" type="text/javascript">function show_hide(elem) {
  obj = document.getElementById(elem);
  if(obj.style.display == "none") obj.style.display = "inline";
  else obj.style.display = "none";
}</script>
';
  
  if ($forum){
    if ($forumRes['refid'])
      $tree = array('<a href="index.php">Форум</a>', '<a href="index.php?forum='.$forumRes['refid'].'">'.text::output($forumRefid['name']).'</a>', text::output($forumRes['name']));
    else
      $tree = array('<a href="index.php">Форум</a>', text::output($forumRes['name']));
  }else{
    $tree = array('Форум');
    
    $total = $sql->query("SELECT * FROM `forum_forums` WHERE `refid` < '1' ORDER BY `realid` ASC ")->num_rows();
    
  }
  
  echo forum_counter();
  echo '<div class="fmenu">';
  foreach ($tree as $menu)
    {
        echo $menu . '  &nbsp;  ';
    }
  echo '</div>';
  
  if ($user_id && $forum && $forumRes['type'] == 1)
      echo '<div class="msg"><a href="index.php?act=addtopic&amp;forum='.$forum.'">Новая тема</a></div>';
  
  
  if ($forum){
    if ($forumRes['type'] == 1){
      $total = $sql->query("SELECT COUNT(*) FROM `forum_topics` WHERE `refid` = '$forum' ")->result();
        $page = new page($total, $set['p_str']);
    
      if ($total){
        $sql->query("SELECT * FROM `forum_topics` WHERE `refid` = '$forum' ORDER BY `time` DESC LIMIT ".$page->limit()); // `sticky` DESC,

        if ($page->k_page() > 5)
            $page->display('index.php?forum='.$forum.'&amp;');
    
        while ($res = $sql->fetch()){

          echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">'; 
          $sub = 'Автор: '.$res['user'];
            if ($res['count'] > 1){
              $lastPost = explode(':|:', $res['lastpost']);
              $sub .= ' <a href="index.php?post='.$lastPost[1].'">Посл.</a>: '.$lastPost[0];
            }
            $sub .= ' '.Core::time($res['time']).'<br />';
          echo show_topic($res, $sub);
          echo '</div>';
          ++$i;
        }
        
        echo '<div class="fmenu">Тем: '.$total.'</div>';
       
        $page->display('index.php?forum='.$forum.'&amp;');
        
      }else{
        echo '<div class="p_m">Пусто</div>';
      }  
      $adm = '<a href="index.php?act=editforum&amp;forum='.$forum.'">Изменить форум</a>';  
    }else{
      $req = $sql->query("SELECT * FROM `forum_forums` WHERE `refid` = '$forum' ORDER BY `realid` ASC ");
      $total = $sql->num_rows();
      if ($total){
        while ($res = $sql->fetch()){
          echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">'; 
          echo show_folder($res);  
          echo '</div>';
          ++$i;
        }
        
        echo '<div class="fmenu">Подфорумов: '.$total.'</div>';  
      }else{
        echo '<div class="post">Пусто</div>';
      }
      
      $adm = '<a href="index.php?act=addforum&amp;forum='.$forum.'">Создать подфорум</a><br /><a href="index.php?act=editforum&amp;forum='.$forum.'">Изменить форум</a>';
    }
  
  }
    else
    {
    $total = $sql->query("SELECT COUNT(*) FROM `forum_forums` WHERE `refid` = '$forum'")->result();
    $page = new page($total, $set['p_str']);
    $sql->query("SELECT * FROM `forum_forums` WHERE `refid` = '$forum' ORDER BY `realid`  DESC LIMIT ". $page->limit());
    if($total){
      while ($res = $sql->fetch()){
        echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">'; 
        echo show_folder($res);  //echo '<br />'.var_dump($res);
        echo '</div>';
        ++$i;
      }
      
      $topics = $sql->query("SELECT COUNT(*) FROM `forum_topics` ")->result();
      $posts = $sql->query("SELECT COUNT(*) FROM `forum_posts` ")->result();
      echo '<div class="fmenu">Тем: '.$topics.', Сообщений: '.$posts.'</div>';  
    }else{
      echo '<div class="p_m">Пусто</div>';
    }
    
    $adm = '<a href="index.php?act=addforum">Создать форум</a><br /><a href="index.php?act=clean">Чистка форума</a>';
  }
  
  echo '<div class="menu"><a href="index.php?act=search">Поиск</a><br /><a href="index.php?act=files">Файлы форума</a></div>';

  if ($forum && $forumRes['type'] == 1){
    echo '<p><img src="images/new.png" alt="+" title="Есть новые сообщения" /> Есть новые сообщения <small>(только для зарегистрированых)</small><br />
<img src="images/sticky.png" alt="^" title="Тема закреплена" /> Прикреплённая тема<br />
<img src="images/close.png" alt="#" title="Тема закрыта" /> Закрытая тема<br />
<img src="images/poll.png" alt="*" title="Голосование" /> В теме есть голосование</p>';  
  }
    
  if ($admin)
    echo '<div class="menu">'.$adm.'</div>';
}

include H . 'engine/includes/foot.php';