<?php
    include '../engine/includes/start.php';
	$banpage=true;
	$set['title']='Правила';

	include H . 'engine/includes/head.php';

	if (is_file(H.'engine/files/data/rules.txt'))
		echo nl2br(htmlspecialchars(file_get_contents(H.'engine/files/data/rules.txt')));
	elseif ($creator)
		echo 'file "engine/files/data/rules.txt" not found';
	
	echo ($user_id ?
		'<a href="/pages/menu.php"><div class="link">Кабинет</div></a>' :
		'<a href="/pages/registration.php"><div class="link">Регистрация</div></a>') .
		'<a href="/"><div class="link">Главная</div></a>';

	include '../engine/includes/foot.php';