<script type="text/javascript" src="/style/themes/web/ajax.js"></script>
<div class="footer"><a href="/"><img src="/style/themes/web/logo.png" alt=""/></a></div>
<div class="main"><div class="head">
<div class="navigation">
 <ul>
<li><a href="/">Главная</a> </li>
<li id = "myMail"></li>
<li><a href="/pages/news.php">Новости</a> </li>
<li><a href="/pages/guestbook.php">Мини-чат</a> </li>
<li><a href="/forum/">Форум</a></li>
<li><a href="/download/">Загрузки</a></li>
<li><a href="/lib/">Библиотека</a></li>
</ul></div><!-- navigation -->
<div class="heft">
<?=$user_id ? '<a href="/pages/menu.php">Кабинет</a> | <a href="/login.php?exit">Выход</a>' : '<a href="/login.php">Войти на сайт</a>'?>
</div><!-- heft -->
</div><!-- head -->
<div class="lefts"><div class="foot"><?=$set['title']?></div>
<script type="text/javascript">
process();
</script>