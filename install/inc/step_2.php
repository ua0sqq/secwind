<?php

    if ($step == 3)
	{
	    $_SESSION['install_step']++;
		exit(header('Location: index.php'));
	}
	elseif (isset($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['db']))
	{
	    $db = @mysqli_connect($_POST['host'], $_POST['user'],$_POST['pass'], $_POST['db']);
		if (!$db)
		{
		    $err='Невозможно подключиться к серверу '.htmlspecialchars(mysqli_connect_error());
		}
		else
		{
		    $_SESSION['db'] = $_POST['db'];
			$_SESSION['host'] = $_POST['host'];
			$_SESSION['user'] = $_POST['user'];
			$_SESSION['pass'] = $_POST['pass'];
			
			mysqli_query($db, 'SET names utf8');
			
			mysqli_multi_query($db, file_get_contents(H . 'engine/files/data/table.sql'));
			echo '<div class="msg">Подключение к базе данных успешно выполнено</div>';
			?> 
			<form>
			    <input name="step" value="<?=$_SESSION['install_step']+1?>" type="hidden" />
				<input <?=isset($err)?'value="SecWind не готов к установке" disabled="disabled"':'value="Продолжить"'?> type="submit"/>
				</form>
			<?
			include 'inc/foot.php';
			exit;
	    }
	
    }

	if (isset($err))
	{
		echo '<div class="err">'.$err.'</div>';
	}
	?>
		<form method="post" action="index.php">
		Хост:<br />
		<input name="host" value="<?=$_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? 'localhost' : $_SERVER['SERVER_NAME']?>" type="text" /><br />
		Пользователь:<br />
		<input name="user" value="root" type="text" /><br />
		Пароль:<br />
		<input name="pass" value="" type="text" /><br />
		Имя базы:<br />
		<input name="db" value="Tadochi" type="text"/><br />
		<input value="Далее" type="submit" />
		</form>
	<?
	include 'inc/foot.php';