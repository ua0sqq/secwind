<?php
	
	include_once '../../engine/includes/start.php';
	$set['title']='Создатель тем оформления';
	include_once incDir . 'head.php';

	if (!$creator)
        Core::stop();


	if (isset($_POST['create_style']))
	{
		$count_dop_form = abs(intval($_POST['count_dop_form']));
		
		if ( empty($count_dop_form))
		{
			echo 'Ошибка! Нет необходимых данных!<br /><a href="theme_creator.php?">Повторить</a>';
			include_once '../sys/inc/tfoot.php';
		}

		$text=null;
		for ( $i = 0; $i < $count_dop_form; $i++ )
				{
					$name 		= htmlspecialchars(trim($_POST[$i .'_name']));
					$color		= htmlspecialchars(trim($_POST[$i .'_color']));
					$background	= htmlspecialchars(trim($_POST[$i .'_background']));
					
					if ( empty($name) or (empty($color) and empty($background) and empty($main_code)) )
					{
						continue;
					}
					
					$text .= 
						$name . PHP_EOL . 
						'{' . PHP_EOL .
						(($color) ? '    color: '. $color .';' . PHP_EOL : '') .
						(($background) ? '    background: '. $background .';' : '')
						.PHP_EOL . '}' .
						PHP_EOL . PHP_EOL;
				}

				
				file_put_contents(tmpDir . 'style.css', $text);
				Core::get('downloadfile', 'includes');
				DownloadFile(tmpDir . 'style.css', 'style.css');
		        /*
		        mkdir("../style/themes/". $style_name ."/icons");
		        $dr = opendir("icons/");
		        while ( $img = readdir($dr) )
		        {
		        	if ($img != "." && $img != ".." && $img != "Thumbs.db")
					{
						copy("icons/". $img, "../style/themes/". $style_name . "/icons/". $img);					
					}		        		        		        		        
		        }
		        $fn = fopen("../style/themes/". $style_name ."/them.name", "w");
		        fputs($fn, $style_name);
		        fclose($fn);
				 msg('Стиль успешно создан!');
				 */
				
												//@unlink($p);
		} else
		{

				echo '<form method="post">';
				$_POST['array_div_class'] 	= 
					array('.body',
						'.logo',
						'.news',
						'.foot',
						'.rekl',
						'.status',
						'.aut',
						'.title',
						'.post',
						'.err',
						'.msg',
						'.menu',
						'.p_t',
						'.p_m',
						'.link',
						'.menu_razd');

				$_POST['count_dop_form'] = !empty($_POST['count_dop_form']) ? intval($_POST['count_dop_form']) : count($_POST['array_div_class']);
				//echo count($_POST['array_div_class']),' | ', count($_POST['array_div_class']);
				if (isset($_POST['add_form']))
				{
					++$_POST['count_dop_form'];
				} elseif(isset($_POST['del_form']))
				{
					--$_POST['count_dop_form'];
				}

				for ($i = 0; $i < $_POST['count_dop_form']; $i++)
				{
					echo '<div class="post">';
					echo 'Класс:<br /><input type="text" name="'. $i .'_name" value="'. (!empty($_POST[$i .'_name']) ? $_POST[$i .'_name'] : $_POST['array_div_class'][$i]) .'"/><br />';
			echo 'Цвет текста:<br /><input type="text" name="'. $i .'_color" value="color: #' .'"/><br />';
			echo 'Фон:<br /><input type="text" name="'. $i .'_background" value="background-color: ' .'"/><br />';
	        echo 'CSS-код:<br /><textarea cols="20" rows="3" name="'.$i.'_main" >'. (!empty($_POST[$i .'_main']) ? $_POST[$i .'_main'] : null) .'</textarea><br />';
						//theme_creator($i, $_POST);
					
					echo '</div>';
				}

				
				echo '<input type="hidden" name="count_dop_form" value="'. $_POST['count_dop_form'] .'"/>';
				echo '<input type="submit" name="add_form" value="Доб. форму" />'. (!empty($_POST['count_dop_form']) ? '<input type="submit" name="del_form" value="Уд. посл."/>' : '');
				echo '<input type="submit" name="create_style" value="Создать тему"/></form>';
		}

	echo '<div class="menu_razd">См. также</div>
		<div class="link"><a href="..">Админка</a></div>
		<div class="link"><a href="..?act=design">Дизайн</a></div>';
include_once incDir . 'foot.php';