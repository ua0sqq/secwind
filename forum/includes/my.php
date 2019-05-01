<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Моя страница';
include H . 'engine/includes/head.php';

if (!$user_id){
  echo Core::msg_show('Только для авторизованных!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (isset($_GET['addfavourite']) && $topic){
  $check = $sql->query("SELECT COUNT(*) FROM `forum_favourites` WHERE `topic` = '$topic' AND `user_id` = '$user_id' LIMIT 1 ")->result();
  if ($check){
    echo Core::msg_show('Данная тема уже в избранном!<br /><a href="index.php?topic='.$topic.'&amp;page=end">Назад</a>');
    include H . 'engine/includes/foot.php';
    
  }

  $check = $sql->query("SELECT COUNT(*) FROM `forum_favourites` WHERE `user_id` = '$user_id' ")->result();
  if ($check >= 20){
    echo Core::msg_show('Максимальное количество тем в избранном (20 шт.)!<br /><a href="index.php?topic='.$topic.'&amp;page='.$page.'">Назад</a>');
    include H . 'engine/includes/foot.php';
    
  }

  $sql->query("INSERT INTO `forum_favourites` SET
  `topic` = '$topic',
  `user_id` = '$user_id' ");

  echo '<div class="msg">Тема добавлена в избранное<br /><a href="index.php?act=my&amp;favourites">В избранное</a>  |  <a href="index.php?topic='.$topic.'&amp;page=end">В тему</a></div>';
  include H . 'engine/includes/foot.php';
  
}

if (isset($_GET['delfavourite']) && $topic){
  $sql->query("DELETE FROM `forum_favourites` WHERE `topic` = '$topic' AND `user_id` = '$user_id' LIMIT 1 ");
  echo '<div class="msg">Тема удалена из избранного<br /><a href="index.php?act=my&amp;favourites">В избранное</a>  |  <a href="index.php?topic='.$topic.'&amp;page=end">В тему</a></div>';
  include H . 'engine/includes/foot.php';
  
}

if (isset($_GET['cleanjournal'])){
  if (isset($_POST['yes'])){
    if (journal_delete()){
      header ('Refresh:1; URL=index.php');
      echo '<div class="msg">Журнал очищен<br /><a href="index.php">Форум</a></div>';
      include H . 'engine/includes/foot.php';
      
    }else{
      echo '<div class="rmenu">Возникла ошибка при очистке журнала!<br /><a href="index.php?act=my&amp;journal">Журнал</a></div>';
      include H . 'engine/includes/foot.php';
      
    }
  }elseif(isset($_POST['no'])){
    header ('Location: index.php?act=my&journal');
  }else{
    echo '<form action="index.php?act=my&amp;cleanjournal" method="post"><div class="rmenu">Вы действительно хотите очистить журнал?<br />';
    echo '<input type="submit" name="yes" value="Очистить" /> <input type="submit" name="no" value="Отмена" /></div></form>';
    include H . 'engine/includes/foot.php';
    
  }
}

if (isset($_GET['posts'])){
  $sqlTotal = 'posts';
  $sqls = "* FROM `forum_posts` WHERE `user_id` = '$user_id' ORDER BY `time` DESC";
  $url = 'posts';
  $hdr = 'Мои посты';
  $bottom = 'Постов: ';
  $goto = '<a href="index.php?act=my&amp;topics">Темы</a>  |  <a href="index.php?act=my&amp;files">Файлы</a>  |  <a href="index.php?act=my&amp;favourites">Избранное</a>  |  <a href="index.php?act=my&amp;journal">Журнал</a>';
}elseif(isset($_GET['files'])){
  $sqlTotal = 'files';
  $sqls = "* FROM `forum_files` WHERE `user_id` = '$user_id' ";
  $url = 'files';
  $hdr = 'Мои файлы';
  $bottom = 'Файлов: ';
  $goto = '<a href="index.php?act=my&amp;topics">Темы</a>  |  <a href="index.php?act=my&amp;posts">Посты</a>  |  <a href="index.php?act=my&amp;favourites">Избранное</a>  |  <a href="index.php?act=my&amp;journal">Журнал</a>';
}elseif(isset($_GET['favourites'])){
  $sqlTotal = 'favourites';
  $sqls = "`forum_favourites`.*, `forum_topics`.* FROM `forum_favourites` LEFT JOIN `forum_topics` ON `forum_favourites`.`topic` = `forum_topics`.`id` WHERE `forum_favourites`.`user_id` = '$user_id' ";
  $url = 'favourites';
  $hdr = 'Избранное';
  $bottom = 'Тем: ';
  $goto = '<a href="index.php?act=my&amp;topics">Темы</a>  |  <a href="index.php?act=my&amp;posts">Посты</a>  |  <a href="index.php?act=my&amp;files">Файлы</a>  |  <a href="index.php?act=my&amp;journal">Журнал</a>';
}elseif(isset($_GET['journal'])){
  $sqlTotal = 'journal';
  $sqls = "* FROM `forum_journal` WHERE `user_id` = '$user_id' ORDER BY `time` DESC";
  $url = 'journal';
  $hdr = 'Журнал';
  $bottom = 'Записей: ';
  $goto = '<a href="index.php?act=my&amp;topics">Темы</a>  |  <a href="index.php?act=my&amp;posts">Посты</a>  |  <a href="index.php?act=my&amp;files">Файлы</a>  |  <a href="index.php?act=my&amp;favourites">Избранное</a>';
}else{
  $sqlTotal = 'topics';
  $sqls = "* FROM `forum_topics` WHERE `user_id` = '$user_id' ORDER BY `time` DESC";
  $url = 'topics';
  $hdr = 'Мои темы';
  $bottom = 'Тем: ';
  $goto = '<a href="index.php?act=my&amp;posts">Посты</a>  |  <a href="index.php?act=my&amp;files">Файлы</a>  |  <a href="index.php?act=my&amp;favourites">Избранное</a>  |  <a href="index.php?act=my&amp;journal">Журнал</a>';
}


echo '<div class="fmenu"><a href="index.php">Форум</a> / '. $hdr.'</div>';

$total = $sql->query("SELECT COUNT(*) FROM `forum_$sqlTotal` WHERE `user_id` = '$user_id' ")->result();
$page = new page($total, $set['p_str']);

if ($total){

    $page->display('index.php?act=my&amp;'.$url.'&amp;');

  $sql->query("SELECT $sqls LIMIT ".$page->limit());
  if (isset($_GET['posts'])){
    while ($res = $sql->fetch()){
      echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';

      echo Core::time($res['time']).' <a href="index.php?post='.$res['id'].'">#</a> '.'<br />';
      echo text::output($res['text']);

      if ($res['files']){
        $file = mysqli_query($sql->db, "SELECT * FROM `forum_files` WHERE `refid` = '".$res['id']."' LIMIT ".$res['files']." ");
        echo '<div class="func">Файл(ы):<br />';
        while($fileRes = $sql->fetch($file)){
          echo show_file($fileRes).'<br />';
        }
        echo '</div>';
      }
      $topicRes = mysqli_fetch_assoc(mysqli_query($sql->db, "SELECT * FROM `forum_topics` WHERE `id`='".$res['refid']."' LIMIT 1 ")); 
      echo '<div class="sub">Тема: <a href="index.php?topic='.$topicRes['id'].'">'.$topicRes['name'].'</a></div>';
      echo '</div>';
      ++$i;
    }
    if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';
  }elseif(isset($_GET['files'])){
    while ($res = $sql->fetch()){
      echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
      echo '<span class="gray">'.Core::time($res['time']).'</span> <a href="index.php?post='.$res['refid'].'" title="Перейти к сообщению">#</a><br />';
      echo show_file($res);
      $topicRes = $sql->fetch(mysqli_query($sql->db, "SELECT * FROM `forum_topics` WHERE `id`='".$res['topic']."' LIMIT 1 "));
      echo '<div class="sub"><a href="index.php?topic='.$topicRes['id'].'">'.$topicRes['name'].'</a></div>';
      echo '</div>';
      ++$i;
    }
    if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';

  }elseif(isset($_GET['favourites'])){
    while ($res = $sql->fetch()){
      echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
      
      $sub = 'Автор: '.$res['user'];
      if ($res['count'] > 1){
        $lastPost = explode(':|:', $res['lastpost']);
        $sub .= ' <a href="index.php?post='.$lastPost[1].'">Последн.</a>: '.$lastPost[0];
      }
      $sub .= ' '.Core::time($res['time']).'<br />';
      $sub .= 'Подфорум: <a href="index.php?forum='.$res['refid'].'">'.$res['forum'].'</a>';
      $sub .= '<br /><a href="index.php?act=my&amp;delfavourite&amp;topic='.$res['id'].'">Удалить из избранного</a>';
      echo show_topic($res, $sub);
      echo '</div>';
      ++$i;
    }
    if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';

  }elseif(isset($_GET['journal'])){
    while ($res = $sql->fetch()){
      echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
      echo Core::time($res['time']).'<br />'.(!$res['readed'] ? '<img src="images/new.png" alt="+" title="Новая запись" />' : '').'<img src="images/topic.png" alt="-" /> '.$res['text'].'</div>';
      if (!$res['readed'])
        mysqli_query($sql->db, "UPDATE `forum_journal` SET `readed` = '1' WHERE `time` = '".$res['time']."' AND `user_id` = '$user_id' LIMIT 1 ", true);
      ++$i;
    }
    if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';

  }else{
    while ($res = $sql->fetch()){
      echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';

      $sub = '';
      $lastPost = explode(':|:', $res['lastpost']);
      $sub .= ' <a href="index.php?post='.$lastPost[1].'">Последн.</a>: '.$lastPost[0];
      $sub .= ' '.Core::time($res['time']).'<br />';

      $sub .= 'Подфорум: <a href="index.php?forum='.$res['refid'].'">'.$res['forum'].'</a>';
      echo show_topic($res, $sub);
      echo '</div>';
      ++$i;
    }
    if (!$i) echo '<div class="p_m">Неверные данные. Убедитесь в правильности ввода страницы!</div>';
  }
  echo '<div class="fmenu">'.$bottom.$total.'</div>';

   $page->display('index.php?act=my&amp;'.$url.'&amp;');
  
  if (isset($_GET['journal']) && $total)
    echo '<div class="menu_razd"><a href="index.php?act=my&amp;cleanjournal">Очистить журнал</a></div>';    
}else{
  echo '<div class="p_m">Пусто</div>';
}

echo '<div class="fmenu">Показать: '.$goto.'</div>';