<?php


   /**
	* Определение темы.
	* Сначала проверяем печеньки, если нет таких печенек, берем пользовательское или системное
	*/

	$show_theme = isset($_COOKIE['set_theme']) && ($_COOKIE['set_theme'] == 'default' || $_COOKIE['set_theme'] == 'web')
		? $_COOKIE['set_theme'] :
		(!empty($user['set_them']) ? $user['set_them'] : $set['theme']);

	if (stristr($_SERVER['HTTP_USER_AGENT'], 'msie') && stristr($_SERVER['HTTP_USER_AGENT'], 'windows'))
	{
		header('Content-type: text/html; charset=UTF-8');
	}
	else
	{
		header('Content-type: application/xhtml+xml; charset=UTF-8');
	}
?>

<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
<title><?=$set['title']?></title>
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="stylesheet" href="/style/themes/<?=$show_theme?>/style.css" type="text/css" />
<meta name="keywords" content="<?=$set['meta_keywords']?>" />
<meta name="description" content="<?=$set['meta_description']?>" />
<?php
	// Обратите внимание - данные не фильтруются, значения массива заранее должны филтроваться
	foreach($meta_og as $key => $value)
	{
		echo '<meta property="og:'.$key.'" content="'.$value.'" />';
	}
?>
</head>
<body>

<?php
if (file_exists(H.'style/themes/'.$show_theme.'/head.php'))
{
	include_once H.'style/themes/'.$show_theme.'/head.php';
}
else
{
	echo  '<div class="title"> ' .$set['title'] . '</div><div class="aut"> ';
    if ($user_id)
    {
        $count_mail = $sql->query('SELECT COUNT(*) FROM `mail` WHERE `no` = "0" AND `user` = "' . $user['id'] . '"')->result();
       // if ($count_mail > 0)
        echo '<a href="/pages/mail.php">Почта '. $count_mail . '</a> | ';
        echo '<a href="/pages/menu.php">Кабинет</a> | <a href="/login.php?exit">Выход</a>';
    }
    else
    {
        echo '<a href="/login.php">Войти на сайт</a>';
    }
    
	echo '</div><div class="body">';
}

	$sql->query('SELECT `name`, `link`, `img`, `new_line` FROM `ads` WHERE `pos` = "top" AND `time` > '.$time.' AND `main` = "'. (int) MAIN.'"');
	while($ads = $sql->fetch())
	{
		if (!empty($ads['img']))
		{
			if (!empty($ads['new_line']))
			{
				echo '<br />';
			}
			?>
			<a href="<?=$ads['link']?>"><img src="<?=$ads['img']?>" alt="" title="<?=$ads['name']?>"/></a>
			<?php
		}
		else
		{
			?>
			<div class="rekl"><a href="<?=$ads['link']?>"><?=$ads['name']?></a></div>
			<?php
		}
	}
	$sql->free();