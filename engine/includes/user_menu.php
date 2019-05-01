<table width="100%">

<tr><td>
<a href='/pages/user.php'><div class="link">Посмотреть анкету</div></a>
</td><td>
<a href='/pages/anketa.php'><div class="link">Редактировать</div></a>
</td></tr>

<tr><td>
<a href='/pages/settings.php'><div class="link">Мои настройки</div></a>
</td><td>
<a href='/pages/secure.php'><div class="link">Сменить пароль</div></a>
</td></tr>

<tr><td>
<a href='/pages/rules.php'><div class="link">Правила</div></a>
</td><td>
<a href='/pages/mail.php'><div class="link">Почта <?php
$count_mail = $sql->query('SELECT COUNT(*) FROM `mail` WHERE `no` = "0" AND `user` = "' . $user['id'] . '"')->result();
if ($count_mail > 0)
echo $count_mail?></div></a>
</td></tr>

<tr><td>
<a href='/pages/users.php'><div class="link">Пользователи</div></a>
</td><td>
<a href='/pages/online.php'><div class="link">Онлайн</div></a>
</td></tr>

<tr><td>
<a href='/pages/smiles.php' class="link">Смайлы</a>
</td><td>
<a href='/pages/bbcodes.php' class="link">Бб коды</a>
</td></tr>

</table>

<?=$creator ? '<a href="/admin/panel.php"><div class="link">Админка</div></a>' : ''?>
<a href='/login.php?exit'><div class="link">Выход из под <?=$user['nick']?></div></a>

