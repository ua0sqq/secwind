</div><div class="right"> <!-- right --><ul>
	<?php
	if (!$user_id)
	{
		?>
		<div style="text-align:center">
		<li class="title"><b>Вход на сайт</b></li>
		<div class="new1"><form method="post" action="/login.php">
		Логин:<br />
		<input type="text" name="nick" maxlength="20" size="15" /><br />
		Пароль: (<a href='/pages/pass.php'> Забыли? </a>)<br />	
		<input type="password" name="pass" maxlength="20" size="15" /><br />
		<input type="submit" value="Войти"/>
		</form></div>
		
		<div class="link"><a href="/pages/registration.php">Регистрация</a></div></div></ul></div>
		<?php
	}
	else
	{
		echo'<li class="title"><b>Привет, ' . $user['nick'] . '</b>!</li>';
		include incDir . 'user_menu.php';
		echo '</ul></div>';
	}