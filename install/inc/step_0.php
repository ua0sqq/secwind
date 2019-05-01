<?php
	if ($step == 1)
	{
	    $_SESSION['install_step']++;
		exit(header('Location: index.php'));
	}

	if (!isset($_GET['start']))
	{
		?>
		<table><tr><td><img src="sw.png"/></td><td>
		SecWind быстрый, удобный инновационный wap движок.
		В отличии от других cms, SecWind позволяет вам не влезая в код, ftp, и phpmyadmin устанавливать, удалять модули. 
		А также SecWind предоставляет удобную админку, включающие такие разделы как: Безопасность, Модули, MySQL менеджер, с кучей утилит внутри<br />
		<hr />
		Автор: Patsifist</table>
		<form><input type="submit" name="start" value="Установить" style="width:100%;height:30px"/><form>
		<?php
	}
	else
	{
		echo file_get_contents('../engine/files/data/agreement.txt');
		?>
		<form>
	    <input name = 'step' value = '<?=$_SESSION['install_step']+1?>' type = 'hidden'/>
	    <input value = 'Принимаю' type = 'submit' />
		</form>
		<hr />
		<?php
	}
	include_once 'inc/foot.php';