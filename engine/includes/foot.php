<?php	
	if (file_exists(H.'style/themes/'.$show_theme.'/foot.php'))
	{
		include H.'style/themes/'.$show_theme.'/foot.php';
	}
	else
	{
		echo '</div> <!-- Div "body" -->';
	}
    
    $sql->query('SELECT `name`, `link`, `img`, `new_line` FROM `ads` WHERE `pos` = "bottom" AND `time` > '.$time.' AND `main` = "'. (int) MAIN.'"');
  
    if ($sql->num_rows())
    {
        echo '<div class="fmenu">';
        while($ads = $sql->fetch())
        {
            if (!empty($ads['new_line']))
            {
                echo '<br />';
            }   
			
            if (!empty($ads['img']))
            {
                ?>
                <a href="<?=$ads['link']?>"><img src="<?=$ads['img']?>" alt="" title="<?=$ads['name']?>"/></a>
                <?php
            }
            else
            {
                ?>
                <a href="<?=$ads['link']?>"><?=$ads['name']?></a>
                <?php
            }
        }
        echo '</div>';
    }
	?>
	<div class='foot'>
	<a style="float:right" rel="license" href="http://creativecommons.org/licenses/by-nd/3.0/deed.ru" title="Произведение «SecWind» созданное Patsifist,
		публикуется на условиях лицензии Creative Commons «Attribution-NoDerivs» 3.0 Непортированная">
		<img alt="Лицензия Creative Commons" style="border-width:0" src="/style/cc_license.png"/>
	</a>
    <?php
	
		$other_theme = $show_theme == 'default' ? 'Web' : 'Wap';
		
		echo '<a href="/?set_theme='.$other_theme.'">'.$other_theme.'</a> версия<br />';
	
        echo $result = 
			'Генерация: ' . ($gen = round(microtime(1) - START, 4)) . ' сек (mysql: '.round($sql->timer, 4).')<br />
            Память: ' . ($memory = round((memory_get_usage() - CPU) / 1024)) . ' kb <br />
	        Запросов: ' . $sql->queries;

        if ($gen > 3 || // если генерация больее трех секунд
            ($memory > 400 && $_SERVER['PHP_SELF'] != '/pages/anketa.php') || // или если используемая память более 300 кб
            $sql->queries > 15) // или запросов больше десяти, отправляем уведомление создателю
        {
            error_catch(null, null, null, null, $result, 'loading');
        }
        ?>
	</div>

	</body>
	</html>
    <?php
	if ($user_id)
	unset($user);
    unset($ads, $gen, $memory, $sql, $set, $user_id, $moder, $admin, $creator);
	exit;