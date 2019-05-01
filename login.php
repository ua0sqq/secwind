<?php
    $show_all = true; 
    include 'engine/includes/start.php';

	if (isset($_GET['pass']) && !$id) // temp
	    echo '<div class="msg">Пароль отправлен вам на E-mail</div>';
    if (isset($_GET['exit']))
    {
		if (!$user_id)Core::stop();

		$set['title'] = 'Выход из аккаунта';
		include H.'engine/includes/head.php';

        /*
		* Службы. Выход
		*/

        $sql->query('select `file` from `module_services` where `use_in` ="exit"');
		while($file = $sql->result())
		{
			include_once H . $file;
		}

		setcookie('user_id', '');
        setcookie('pass', '');
        session_destroy();
		echo '<div class="menu_razd"><a href="/">Главная</a></div>';
        //Core::stop();
		include H.'engine/includes/foot.php';
    }
	
	if (isset($_GET['pass']))
	{
	    if ($sql->query("SELECT COUNT(*) FROM `user` WHERE `id` = '".$id."' AND `pass` = '".md5($id . $_GET['pass'])."' LIMIT 1")->result() == 1)
		{
	        $user = Core::get_user($id, true);
		    $user_id = $_SESSION['user_id'] = $user['id'];
		    setcookie('user_id', $user['id'], $time+60*60*24*365);
		    setcookie('pass', md5($id . $_GET['pass']), $time+60*60*24*365);
		    $sql->query('UPDATE `user` SET `date_last` = '.$time.', `ip` = '.ip2long($_SERVER['REMOTE_ADDR']).', `ua` = "'.my_esc($_SERVER['HTTP_USER_AGENT'], true).'" WHERE `id` = '.$user['id']);
			$_SESSION['user_authed'] = 'GET';
	    }
		else
	        $error = 'Неправильный логин или пароль';
	}
	
	elseif (isset($_POST['nick'], $_POST['pass']))
	{
		if (
				isset($_SESSION['fail_auth']) && 
				$_SESSION['fail_auth'] > 3 && 
				(
					empty($_SESSION['captcha']) || 
					trim(strtolower($_POST['captcha'])) != $_SESSION['captcha']
				))
		{
			$error = 'Неверный проверочный код';
		}
		else
		{
			$tmp_id = $sql->query("SELECT `id` FROM `user` WHERE `nick` = '".my_esc($_POST['nick'])."' LIMIT 1")->result();
		
			if ($sql->query("SELECT COUNT(*) FROM `user` WHERE `nick` = '".my_esc($_POST['nick'])."' AND `pass` = '".md5($tmp_id . $_POST['pass'])."' LIMIT 1")->result() == 1) //$tmp_id && 
			{
				//$user['id'] = $sql->query("SELECT `id` FROM `user` WHERE `nick` = '".my_esc($_POST['nick'])."' LIMIT 1")->result();
				$user = Core::get_user($tmp_id, true);
				$user_id = $_SESSION['user_id'] = $tmp_id;
			
				setcookie('user_id', $user_id, $time+60*60*24*365);
				setcookie('pass', md5($user_id . $_POST['pass']), $time+60*60*24*365);
			
				$sql->query('UPDATE `user` SET `date_last` = '.$time.', `ip` = '.ip2long($_SERVER['REMOTE_ADDR']).', `ua` = "'.my_esc($_SERVER['HTTP_USER_AGENT'], true).'" WHERE `id` = '.$user_id);
				$_SESSION['user_authed'] = 'POST';
			}
			else
				$error = 'Неправильный логин или пароль';
		}
	}

    if ($user_id)
        Core::stop('/pages/menu.php');

    if (!empty($user['activation'])) // если аккаунт не активирован
	{
	    $error = 'Вам необходимо активировать Ваш аккаунт по ссылке, высланной на Email, указанный при регистрации';
	}

	$set['title'] = 'Вход на сайт';
	include H.'engine/includes/head.php';
	
	if (isset($error))
	{
		Core::msg_show($error);
		
		if (isset($_SESSION['fail_auth']))
			$_SESSION['fail_auth']++;
		else
			$_SESSION['fail_auth'] = 0;
	}

    ?>
    <form method='post'>
        Логин:<br /><input type='text' name='nick' maxlength='32' value="<?=Core::form('nick')?>"/><br />
        Пароль (<a href='/pages/pass.php'> Забыли? </a>):<br /><input type='password' name='pass' maxlength='32' /><br />
		<?=isset($_SESSION['fail_auth']) && $_SESSION['fail_auth'] > 3 ? '<img src="/pages/captcha.php"/><br /><input type="text" name="captcha"/><br />' : null?>
        <input type='submit' value='Войти' />
    </form>
    <a href='/pages/registration.php'>
        <div class='menu_razd'>
        &raquo;Регистрация
        </div>
    </a>
    <?php
    include H.'engine/includes/foot.php';