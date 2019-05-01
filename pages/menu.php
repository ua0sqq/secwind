<?php
    include '../engine/includes/start.php';
	if (!$user_id)
	    Core::stop();
	
	$set['title']='Личный кабинет';
	
	include H.'engine/includes/head.php';

	if (isset($_SESSION['user_authed']))
	{
		$res = mysqli_query($sql->db, 'select `file` from `module_services` where `use_in` ="auth"');
		while($file = $sql->result($res))
		{
			include_once H . $file;
		}
	}

    ?>
    <a href="/"><div class="link">Главная</div></a>
    <?php
	include H.'engine/includes/user_menu.php';
	include H.'engine/includes/foot.php';