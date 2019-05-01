<?php

	$db = mysqli_connect($_SESSION['host'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['db']);
	mysqli_query($db, 'SET names utf8'); 
	if ($step == 4)
	{
	    $tmp_set['title'] = strtolower($_SERVER['HTTP_HOST']).' | Главная';
		$tmp_set['mysql_host'] = $_SESSION['host'];
		$tmp_set['mysql_user'] = $_SESSION['user'];
		$tmp_set['mysql_pass'] = $_SESSION['pass'];
		$tmp_set['mysql_db_name'] = $_SESSION['db'];
        $set = parse_ini_file(H.'engine/files/data/for_settings.ini');
		
		include 'inc/functions.php';
		
		if (save_settings(array_merge($set, $tmp_set)))
		{
		    unset($_SESSION['install_step'],$_SESSION['host'],$_SESSION['user'],$_SESSION['pass'],$_SESSION['db']);
			
			if ($_SERVER['SERVER_ADDR'] != '127.0.0.1')
			    delete_dir(H.'install/');

			file_put_contents(H . 'engine/files/data/secwind.db', serialize(array('version' => $set['version'], 'date_create' => time(), 'author' => 'Tadochi aka Patsifist', 'author_info' => array('icq' => 1314191, 'email' => 'Tadochi@spaces.ru'), 'support' => 'SecWind.ru')));
			unlink(H.'engine/files/data/for_settings.ini');
            unlink(H.'engine/files/data/table.sql');
			
			exit(header('Location: /index.php'));
		}
		else
		    $msg = 'Невозможно сохранить настройки системы';
	}
	elseif (isset($_POST['reg']))
	{
	    if (empty($_POST['nick']))
		    $err[] = 'Введите ник';
		elseif (!preg_match("#^([A-zА-я0-9\-\_\ ])+$#ui", $_POST['nick']))
		    $err[] = 'В нике присутствуют запрещенные символы';
		else
		{
		    $nickLen = mb_strlen($_POST['nick']);
			if ($nickLen < 3)
			    $err[] = 'Ник короче 3-х символов';
			elseif ($nickLen > 16)
			    $err[] = 'Ник длиннее 16-ти символов';
			elseif (@mysqli_num_rows(mysqli_query($db, "SELECT COUNT(*) FROM `user` WHERE `nick` = '".mysqli_real_escape_string($db, $_POST['nick'])."' LIMIT 1")) > 1)
			    $err[] = 'Выбранный ник уже занят другим пользователем';
			else
			    $nick = $_POST['nick'];
		}
		
		if (empty($_POST['password']))
		    $err[]='Введите пароль';
		else
		{
		    $pass_Len = mb_strlen($_POST['password']);
			if ($pass_Len < 6)
			    $err[] = 'Пароль короче 6-ти символов';
			elseif ($pass_Len > 16)
			    $err[]='Пароль длиннее 16-ти символов';
			elseif (empty($_POST['password_retry']))
			    $err[]='Введите подтверждение пароля';
			elseif ($_POST['password'] !== $_POST['password_retry'])
			    $err[]='Пароли не совпадают';
			else $password=$_POST['password'];
		}
		
		$pol = $_POST['pol'] == 0 ? 0 : 1; 
		
		if (!isset($err))
		{
		    $status = mysqli_fetch_assoc(mysqli_query($db, 'SHOW TABLE STATUS LIKE "user"')); 
			$password = md5($status['Auto_increment'] . $password); 
			mysqli_query($db, "
			    INSERT INTO `user`
				(`nick`, `pass`, `date_reg`, `date_aut`, `date_last`, `pol`, `group_access`, `balls`) VALUES 
				('".$nick."', '".$password."', '".time()."', '".time()."', '".time()."', '".$pol."', '10', '82366622244044')");
			echo mysqli_error($db);
			$user = mysqli_fetch_assoc(mysqli_query($db, "SELECT `id` FROM `user` WHERE `nick` = '$nick' LIMIT 1"));

			$_SESSION['user_id'] = $user['id'];
			setcookie('user_id', $user['id'], time()+60*60*24*365, '/');
			setcookie('pass', $password, time()+60*60*24*365, '/');
			
			echo '<div class="msg">Регистрация администратора прошла успешно</div>';
			
			if (isset($msg))
			{
			    echo '<div class="msg">'.$value.'</div>';
		    }
		    ?>
		    <form>
		        <input name="step" value="5" type="hidden" />
			    <input value="Завершить установку" type="submit" />
		    </form>* после установки обязательно удалите папку /install/<br />
		    <?
		}
			
		if (isset($err))
		{
		    foreach ($err as $err)
			{
			    echo '<div class="err">'.$err.'</div>';
			}
			?>
			<form>
			    <input value="Повторить" type="submit" />
		    </form>
			<?
		}
	}
	else
	{
        ?>
		<form method='post'>
		    Логин (3-16 символов):<br />
			<input type='text' name='nick' value="admin" maxlength = "16" /><br />
			Пароль (6-16 символов):<br />
			<input type='password' name='password' maxlength='16' /><br />
			Подтверждение пароля:<br />
			<input type='password' name='password_retry' maxlength='16' /><br />
			Ваш пол:<br />
			<select name='pol'>
			    <option value='1' selected='selected'>Мужской</option>
			    <option value='0'>Женский</option>
			</select><br />
			* Все поля обязательны к заполнению<br />
			<input type='submit' name='reg' value='Регистрация' /><br />
		</form>
		<?
	}
	include 'inc/foot.php';