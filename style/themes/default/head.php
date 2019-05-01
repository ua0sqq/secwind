<a href="/">
    <div class = "logo">
	    <img src='/style/themes/<?=$show_theme?>/logo.png' alt='' />
    </div>
</a>
<div class="title">
<?=$set['title']?>
</div>
<div class="aut">
<?php
if ($user_id)
{
    $count_mail = $sql->query('SELECT COUNT(*) FROM `mail` WHERE `no` = "0" AND `user` = "' . $user['id'] . '"')->result();
    if ($count_mail > 0)
    echo '<a href="/pages/mail.php">Почта '. $count_mail . '</a> | ';
    echo '<a href="/pages/menu.php">Кабинет</a> | <a href="/login.php?exit">Выход</a>';
}
else
{
    echo '<a href="/login.php">Войти на сайт</a>';
}
?>
</div>
<div class="body">