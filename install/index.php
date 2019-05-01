<?php
    $start = microtime(true);

	if (file_exists('../engine/files/data/settings.ini'))
        exit('Движок уже установлен');

	define('H', $_SERVER['DOCUMENT_ROOT'].'/');

	$step = isset($_GET['step']) ? ($_GET['step'] > 4 ? 4 : $_GET['step']) : 0;
	

	ob_start();
	session_start();
	include 'inc/head.php';

	if (!isset($_SESSION['install_step']))$_SESSION['install_step']=0;

	include 'inc/step_'.$_SESSION['install_step'].'.php';