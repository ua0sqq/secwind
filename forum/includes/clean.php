<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

# fixed

$set['title'] .= ' | Чистка форума';
include H . 'engine/includes/head.php';

if (!$admin){
  echo Core::msg_show('У вас недостаточно прав для просмотра этой страницы!<br /><a href="index.php">Форум</a>');
  include H . 'engine/includes/foot.php';
  
}

if (isset($_GET['files'])){
  echo '<div class="fmenu"><a href="index.php">Форум</a>  /  Удаление временных файлов</div>';

  if (isset($_GET['yes'])){
    $total = $sql->query("SELECT COUNT(*) FROM `forum_files` WHERE `tempid` > 0 AND `time` < '".(time() - 86400)."' ")->result();
    
    if ($total){
      $req = $sql->query("SELECT * FROM `forum_files` WHERE `tempid` > 0 AND `time` < '".(time() - 86400)."' ");
      while ($res = $sql->fetch()){
        unlink('../forum/files/attach/'.$res['name']);
      }

      $sql->query("DELETE FROM `forum_files` WHERE `tempid` > 0 AND `time` < '".(time() - 86400)."' ");
    }

    header ('Refresh:3; URL=index.php');
    echo '<div class="msg">Очищено '.$total.' файлов<br /><a href="index.php">Далее</a></div>';
  }else{
    $total = $sql->query("SELECT COUNT(*) FROM `forum_files` WHERE `tempid` > 0 ")->result();
    $page = new page($total, $set['p_str']);
    $i = 1;
    if ($total){
    $page->display('index.php?act=clean&amp;files&amp;');
        
      $sql->query("SELECT * FROM `forum_files` WHERE `tempid` > 0 ORDER BY `time` DESC LIMIT ".$page->limit());
      while ($res = $sql->fetch()){
        echo $i % 2 ? '<div class="p_t">' : '<div class="p_m">';
        echo ($res['time'] > (time() - 86400) ? '<span class="red">новый</span> ' : '').show_file($res);
        echo '</div>';
        ++$i;
      }
      echo '<div class="fmenu">Всего: '.$total.'</div>';
     $page->display('index.php?act=clean&amp;files&amp;');
      
      echo '<div class="post"><a href="index.php?act=clean&amp;files&amp;yes">Очистить</a></div>'; 
    }else{
      echo '<div class="p_m">Пусто</div>';
    }

  }  
}elseif(isset($_GET['user'])){

  echo '<div class="fmenu"><a href="index.php">Форум</a>  /  Удаление активности пользователя</div>';
   
  if (isset($_POST['send']) && !empty($_POST['user']) && !empty($_POST['del']) && $_POST['user'] != $user_id){
    $user = $sql->query("SELECT * FROM `users` WHERE `id` = '".intval($_POST['user'])."' LIMIT 1 ")->fetch();
      if ($_POST['del'] == 2){
        $files = $sql->query("SELECT COUNT(*) FROM `forum_files` WHERE `user_id` = '".$user['id']."' ")->result();
        if ($files){
          $sql->query("SELECT * FROM `forum_files` WHERE `user_id` = '".$user['id']."' ");
          while ($res = $sql->fetch()){
            @unlink('../forum/files/attach/'.$res['name']);
          }
          $sql->query("DELETE FROM `forum_files` WHERE `user_id`='".$user['id']."' ");
        }
        
        $posts = $sql->query("SELECT COUNT(*) FROM `forum_posts` WHERE `user_id` = '".$user['id']."' ")->result();
        if ($posts){
          $req = $sql->query("SELECT * FROM `forum_posts` WHERE `user_id` = '$user' ");
          while ($res = $sql->fetch()){
            $sql->query("UPDATE `forum_topics` SET
            `count`= count - 1
            WHERE `id`='".$res['refid']."' LIMIT 1 ", true);
            if ($res['rating'])
              $sql->query("DELETE FROM `forum_posts_rating` WHERE `refid` = '".$res['id']."' ", true);  
          }
          $sql->query("DELETE FROM `forum_posts` WHERE `user_id`='".$user['id']."' ", true);
        }
      }
      $sql->query("DELETE FROM `forum_readed` WHERE `user_id` = '".$user['id']."' ");
      $sql->query("DELETE FROM `forum_favourites` WHERE `user_id` = '".$user['id']."' ");
      $sql->query("DELETE FROM `forum_journal` WHERE `user_id` = '".$user['id']."' ");
           
      echo '<div class="msg">Активность пользователя на форуме очищена<br /><a href="index.php">Далее</a></div>';
  }else{
    echo '<div class="err">Нажав на кнопку вы удалите активность пользователя. Это может занять некоторое время!</div><form action="index.php?act=clean&amp;user" method="post"><div class="msg">ID пользователя: <input type="text" size="3" name="user" /> <input type="submit" name="send" value="Удалить" /><br /><label><input type="radio" name="del" value="1" />Журнал, закладки</label><br /><label><input type="radio" name="del" value="2" />Журнал, закладки, сообщения и файлы</label></div></form>';
  }
}else{

  echo '<div class="fmenu"><a href="index.php">Форум</a>  /  Чистка форума</div>';
  echo '<div class="menu"><a href="index.php?act=clean&amp;files">Удаление временных файлов</a><br />
<a href="index.php?act=clean&amp;user">Удаление активности пользователя</a></div>';
}