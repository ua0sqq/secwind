<?php
# Script by seg0ro http://mobilarts.ru
# Not for sale!!!

  /**
  Функция добавления в таблицу журнала записи
  $toId - id пользователя, кому предназначена запись
  $string - текст записи
  */


   function journal_add($toId = 0, $string = ''){
    global $sql;
    $check = $sql->query("SELECT COUNT(*) FROM `user` WHERE `id` = '$toId' ")->result();
    if ($check && $string){
      $total = $sql->query("SELECT COUNT(*) FROM `forum_journal` ")->result();
      if ($total < 100){
        $sql->query("INSERT INTO `forum_journal` SET `time` = '".time()."', `user_id` = '$toId', `text` = '".my_esc($string)."' ");
      }else{
        $lastEntry = $sql->query("SELECT `time` FROM `forum_journal` WHERE `user_id` = '$toId' ORDER BY `time` ASC LIMIT 1 ")->fetch();
        $sql->query("UPDATE `forum_journal` SET `time` = '".time()."', `text` = '".my_esc($string)."' WHERE `user_id` = '$toId' AND `time` = '".$lastEntry['time']."' LIMIT 1 ");
      }
    }
  }
  
  /**
  Функция удаления записи из таблицы журнала
  $time = 0 очищает весь журнал пользователя
  $time = n удаляет запись с временной меткой n
  Возвращает true или false
  */
   function journal_delete($time = 0){
    global $sql;
    if ($time){
      $check = $sql->query("SELECT COUNT(*) FROM `forum_journal` WHERE `time` = '$time' AND `user_id` = '".Core::$user_id."' ")->result();
      if ($check)
        $sql->query("DELETE FROM `forum_journal` WHERE `time` = '$time' AND `user_id` = '".Core::$user_id."' LIMIT 1 ");
      else
        return false;  
    }else{
      $check = $sql->query("SELECT COUNT(*) FROM `forum_journal` WHERE `user_id` = '".Core::$user_id."' ")->result();
      if ($check)
        $sql->query("DELETE FROM `forum_journal` WHERE `user_id` = '".Core::$user_id."' ");
      else
        return false;
    }
    return true;
  }
  
  /**
  Функция выводит cсылку на журнал с указанием новых записей
  */
   function journal_new(){
    global $sql, $user_id;
    $sql->query("SELECT COUNT(*) FROM `forum_journal` WHERE `user_id` = '".$user_id."' AND `readed` = '0'");
    $totalNew = $sql->result();
    if ($totalNew)
      return ' <span class="red" title="Новая запись в журнале">('.$totalNew.')</span>';
    else
      return false;  
  }