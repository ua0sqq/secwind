<?php


$set['title'] .= ' | Поиск';
include H . 'engine/includes/head.php';

if (!$user_id){
  echo Core::msg_show('Только для авторизованных!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

$search = isset($_GET['text']) ? strtr($_GET['text'], array('_' => '\\_','%' => '\\%')) : false;
$searchT = isset($_GET['t']) ? 1 : 0;
$searchU = isset($_GET['u']) && !$searchT ? 1 : 0;

if ($topic){
  $tree = '<a href="index.php">Форум</a>  /  <a href="index.php?topic='.$topic.'">'.text::output($topicRes['name']).'</a>  /  Поиск в теме';
  $num = 2;
}else{
  $tree = '<a href="index.php">Форум</a>  /  Поиск';
  $num = 1;
}

if ($searchU){
  $tree .= '  /  Поиск постов пользователя';
  if ($search){
    $sql->query("SELECT * FROM `user` WHERE `nick` LIKE '".my_esc($search)."' LIMIT 1 ");
    if (!$sql->num_rows()){
      $error = 'Такого пользователя не существует!<br />';
    }
    $userRes = $sql->fetch();
  }
}

echo '<div class="fmenu">'.$tree.'</div>';

if ($search && !$searchU && (mb_strlen($search) < 4 || mb_strlen($search) > 64))
  $error .= 'Некорректная длинна запроса (для тем и постов от 4 символов)!';
  
if ($search && $searchU && (mb_strlen($search) < 3 || mb_strlen($search) > 32))
  $error .= 'Некорректная длинна запроса (для ника от 3 символов)!';  

if (isset($error))
  echo Core::msg_show($error);

echo '<form action="index.php?act=search'.($topic ? '&amp;topic='.$topic : '').'" method="get"><div class="msg">';
echo '<input type="hidden" name="act" value="search" />';
if ($topic)
  echo '<input type="hidden" name="topic" value="'.$topic.'" />';
echo 'Запрос <img style="cursor: pointer;" onclick="alert(this.title);" src="images/question.png" alt="?" title="От 4 до 64 символов" />:<br /><input type="text" value="'.text($search).'" name="text" /><input type="submit" value="Поиск" /><br />';
if ($moder)
  echo '<label><input name="u" type="checkbox" value="1" '.($searchU ? 'checked="checked"' : '').' /> Поиск постов пользователя</label><br />';
if (!$topic)
  echo '<label><input name="t" type="checkbox" value="1" '.($searchT ? 'checked="checked"' : '').' /> Искать в названии темы</label>';
echo '</div></form>';

if ($search && !isset($error)){
  if ($searchT){
    $total = $sql->query("SELECT COUNT(*) FROM `forum_topics` WHERE `name` LIKE '%".my_esc($search)."%' ")->result();
  }elseif($searchU){
    $total = $sql->query("SELECT COUNT(*) FROM `forum_posts` WHERE `user_id` = '".$userRes['id']."'".($topic ? " AND `refid` = '$topic'" : "")." ")->result();
  }else{
    $total = $sql->query("SELECT COUNT(*) FROM `forum_posts` WHERE MATCH (`text`) AGAINST ('".my_esc($search)."' IN BOOLEAN MODE)".($topic ? " AND `forum_posts`.`refid` = '$topic'" : "")." ")->result();
  }
   $page = new page($total, $set['p_str']);
  if ($total){
    $page->display('index.php?act=search&amp;'.($topic ? 'topic='.$topic.'&amp;' : '').'text='.$search.'&amp;'.($searchT ? 't=1&amp;' : '').($searchU ? 'u=1&amp;' : ''));;
    
    if ($searchT){
      $sql->query("SELECT * FROM `forum_topics` WHERE `name` LIKE '%".my_esc($search)."%' ORDER BY `time` DESC LIMIT ".$page->limit());
    }elseif($searchU){
       $sql->query("SELECT * FROM `forum_posts` WHERE `user_id` = '".$userRes['id']."'".($topic ? " AND `refid` = '$topic'" : "")." ORDER BY `time` DESC LIMIT ".$page->limit());
    }else{
      $sql->query("SELECT `forum_posts`.*, `forum_posts`.`id` AS `pid`, `user`.`id`, `user`.`nick`, `user`.`pol`, `user`.`group_access` FROM `forum_posts` LEFT JOIN `user` ON `forum_posts`.`user_id`=`user`.`id`  WHERE MATCH (`text`) AGAINST ('".my_esc($search)."' IN BOOLEAN MODE) ".($topic ? " AND `forum_posts`.`refid` = '".$topic."'" : "")." ORDER BY `time` DESC LIMIT ".$page->limit());
    }
    
    if ($searchT && !$topic){
      while ($res = $sql->fetch()){
        echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';

        $sub = 'Автор: '.$res['user'];
        if ($res['count'] > 1){
          $lastPost = explode(':|:', $res['lastpost']);
          $sub .= ' <a href="index.php?post='.$lastPost[1].'">Последн.</a>: '.$lastPost[0];
        }
        $sub .= ' '.Core::time($res['time']).'<br />';

        $sub .= 'Подфорум: <a href="index.php?forum='.$res['refid'].'">'.$res['forum'].'</a>';
        echo show_topic($res, $sub);
        echo '</div>';
        ++$i;
      }
      if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';
    }elseif($searchU){
      
      while ($res = $sql->fetch()){
        echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
        
        $header = Core::time($res['time']).' <a href="index.php?post='.$res['id'].'">#</a>';
        
        $text = text::output($res['text']);
  
        if ($res['files']){
          $file = mysqli_query($sql->db, "SELECT * FROM `forum_files` WHERE `refid`='".$res['id']."' LIMIT ".$res['files']." ");
          $text .= '<div class="func">Файл(ы):<br />';
          while($fileRes = mysqli_fetch_assoc($file)){
            $text .= show_file($fileRes).'<br />';
          }
          $text .= '</div>';
        }
        
        if ($res['edit']){
          $edit = explode(':|:', $res['edit']);
          $text .= '<div style="font-size: x-small; color: gray">Изменил(а) '.$edit['0'].' '.Core::time($edit['1']).'</div>';
        }
        
        $sub = false;
        if (!$topic){
          $topicRes = mysqli_fetch_assoc(mysqli_query($sql->db, "SELECT * FROM `forum_topics` WHERE `id`='".$res['refid']."' LIMIT 1 "));
          $sub = 'Тема: <a href="index.php?topic='.$topicRes['id'].'">'.$topicRes['name'].'</a>';  
        }
        $text .= '<br />'.$sub;
        $array = array('status' => $header, 'post' => $text);
        echo Core::user_show($userRes, $array);
        echo '</div>';
        ++$i;
      }
      if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';
    }else{
      while ($res = $sql->fetch()){
        echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';

        $header = ' '.Core::time($res['time']).' <a href="index.php?post='.$res['pid'].'">#</a>';

        $text = text::output($res['text']);
        
        if ($res['files']){
          $file = mysqli_query($sql->db, "SELECT * FROM `forum_files` WHERE `refid`='".$res['fid']."' LIMIT ".$res['files']." ");
          if (mysqli_num_rows($file)){
            $text .= '<div class="func">Файл(ы):<br />';
            while($fileRes = mysqli_fetch_assoc($file)){
              $text .= show_file($fileRes).'<br />';
            }
            $text .= '</div>';
          }
        }

        if ($res['edit']){
          $edit = explode(':|:', $res['edit']);
          $text .= '<div style="font-size: x-small; color: gray">Изменил(а) '.$edit['0'].' '.Core::time($edit['1']).'</div>';
        }

        $sub = false;
        if (!$topic){
          $topicRes = mysqli_fetch_assoc(mysqli_query($sql->db, "SELECT * FROM `forum_topics` WHERE `id` = '".$res['refid']."' LIMIT 1 "));
          $sub = 'Тема: <a href="index.php?topic='.$topicRes['id'].'">'.text::output($topicRes['name']).'</a>';
        }
        $text .= '<br />'.$sub;
        $array = array('status' => $header, 'post' => $text);
        echo Core::user_show($res, $array);
        echo '</div>';
        ++$i;
      }
      if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';
    }
    echo '<div class="fmenu">Найдено: '.$total.'</div>';

    $page->display('index.php?act=search&amp;'.($topic ? 'topic='.$topic.'&amp;' : '').'text='.$search.'&amp;'.($searchT ? 't=1&amp;' : '').($searchU ? 'u=1&amp;' : ''));
  }else{
    echo '<div class="err">Ничего не надено</div>';
  }

}

if ($search)
  echo '<p><a href="index.php?act=search'.($topic ? '&amp;topic='.$topic : '').'">Новый поиск</a></p>';

include H . 'engine/includes/foot.php';