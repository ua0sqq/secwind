<?php
    include '../engine/includes/start.php';

    if (!$creator)
        Core::stop();

    $set['title']='Экспресс-панель';

    include incDir.'head.php';

	if (empty($_SESSION['entry_admin']))
	{
		$_SESSION['entry_admin'] = 1;
		$query = mysqli_query($sql->db, 'select `file` from `module_services` where `use_in`="enrty_admin"');
		while($file = $sql->result($query))
		{
			include_once H . $file;
		}
	}
    
    
    function WordCut($words)
    {
        $word = explode(' ', (string) $words);
        if (count($word) > 1)
        {
            $tmp = $word[0] . ' ' . $word[1] . (!empty($word[2]) ? ' ' . $word[2] : '');
            if (mb_strlen($tmp) > 11)
            {
                return $word[0] . '.. ';
            }
        }
        return $words;
    }

    //Прошу прощения за следующие html / php - быдлокоды, я далеко не знаток css, html. Буду благодарен за подсказки на счет упрощения верстки :)
    
    if (isset($_GET['move']))
    {
        $sql->query('SELECT `name`, `pos` FROM `speed_dial` WHERE `id` = '.$id);
        
        if ($sql->num_rows() == 0)
        {
            Core::msg_show('Пункт с id '.$id.' не найден');
            include incDir.'foot.php';
        }
        
        $menu = $sql->fetch();
        $new_pos = $_GET['move'] == 'up' ? $menu['pos'] - 1 : $menu['pos'] + 1;
        
        $sql->query('UPDATE `speed_dial` set `pos` = '.$menu['pos'].' WHERE `pos` = '.$new_pos);
        $sql->query('UPDATE `speed_dial` set `pos` = '.$new_pos.' WHERE `id` = '.$id);
        Core::msg_show('Пункт '.$menu['name'].' перемещен', 'msg');
    }
    
    switch($act)
    {
        case 'add':
        
        if (isset($_POST['name']))
        {
            $icon = Core::form('icon');
            $host = parse_url(Core::form('link'), PHP_URL_HOST);
        
            if ($host != $_SERVER['SERVER_NAME'])
            {
                $icon = 'http://www.google.com/s2/favicons?domain=' . $host;
            }
            
            
            $sql->query("INSERT INTO `speed_dial` SET 
                `name` = '".Core::form('name')."',
                `link` = '".Core::form('link')."',
                `icon` = '".$icon."',
                `new_line` = '".(!empty($_POST['new_line']) ? 1 : 0)."',
                `pos` = '".($sql->query('SELECT max(`pos`) FROM `speed_dial`')->result() + 1)."'");
            Core::msg_show('Пункт добавлен', 'msg');
        }
        else
        {
            ?>
            <form action="?act=add" method="post">
            Название:<br />
            <input type="text" name="name" value=""/><br />
            Ссылка: (оставить пустым для разделителя)<br />
            <input type="text" name="link" value="http://"/><br />
            Иконка:<br />
            <input type="text" name="icon" value=""/><br />
            С новой строки:<br />
            <select name="new_line">
                <option value="0">Нет</option>
                <option value="1">Да</option>
            </select><br />
            <input type="submit" value="Добавить"/>
            </form>
            <?php
        }
        echo '<a href="./panel.php" class="link">Админка</a>';
        break;
        
        case 'edit':
        $sql->query('SELECT * FROM `speed_dial` WHERE `id` = '.$id);
        
        if ($sql->num_rows() == 0)
        {
            Core::msg_show('Пункт с id '.$id.' не найден');
            include incDir.'foot.php';
        }
        
        $menu = $sql->fetch();
        
        if (isset($_POST['name']))
        {
            $sql->query("UPDATE `speed_dial` SET 
                `name` = '".Core::form('name')."',
                `link` = '".Core::form('link')."',
                `icon` = '".Core::form('icon')."',
                `new_line` = '".(!empty($_POST['new_line']) ? 1 : 0)."'
                WHERE `id` = $id");
            Core::msg_show('Пункт отредактирован', 'msg');
        }
        else
        {
            ?>
            <form action="?act=edit&amp;id=<?=$id?>" method="post">
            Название:<br />
            <input type="text" name="name" value="<?=$menu['name']?>"/><br />
            Ссылка: (оставить пустым для разделителя)<br />
            <input type="text" name="link" value="<?=$menu['link']?>"/><br />
            Иконка:<br />
            <input type="text" name="icon" value="<?=$menu['icon']?>"/><br />
            С новой строки:<br />
            <select name="new_line">
                <option value="0">Нет</option>
                <?=$menu['new_line'] == '1' ? '<option value="1" selected="selected">' : '<option value="1">'?>Да</option>
            </select><br />
            <input type="submit" value="Изменить"/>
            </form>
            <?php
        }
        echo '<a href="./panel.php" class="link">Админка</a>';
        
        break;
        
        case 'editing':
        
        echo '<div class="menu_razd">Редактирование экспресс-панели</div>';

        $pos = $sql->query('SELECT max(`pos`) from `speed_dial`')->result();
        $sql->query('SELECT `id`, `name`, `pos` FROM `speed_dial` ORDER BY `pos`');
    
        while ($menu = $sql->fetch())
        {
            echo 
                '<div class="post">'.$menu['pos'] . ') '.$menu['name'], '<br />',
                ($menu['pos'] > 1 ? '<a href="?act=editing&amp;move=up&amp;id='.$menu['id'].'">[ &uarr; ]</a>&nbsp;&nbsp;' : ''),
                '[ <a href="?act=edit&amp;id='.$menu['id'].'">изменить</a> ] | [ <a href="?act=delete&amp;id='.$menu['id'].'">удалить</a> ]',
                ($menu['pos'] < $pos ? '&nbsp;&nbsp;<a href="?act=editing&amp;move=down&amp;id='.$menu['id'].'">[ &darr; ]</a>' : ''),
                '</div>';
        }
        
        echo '<a href="./panel.php" class="link">Админка</a>';
        
        break;
        
        case 'delete':
        $sql->query('SELECT `name`, `pos` FROM `speed_dial` WHERE `id` = '.$id);
        
        if ($sql->num_rows() == 0)
        {
            Core::msg_show('Пункт с id '.$id.' не найден');
            include incDir.'foot.php';
        }
        
        $menu = $sql->fetch();
        
        if (isset($_GET['confirm']))
        {
            if (empty($_SESSION['main_menu_confirm_delete']) || $_SESSION['main_menu_confirm_delete'] != $_GET['confirm'])
            {
                Core::msg_show('Удаление не подтверждено, попробуйте еще раз');
            }
            else
            {
                $sql->query('DELETE FROM `speed_dial` WHERE `id` = '.$id);
                $sql->query('UPDATE `speed_dial` SET `pos` = `pos` - 1 WHERE `pos` > '.$menu['pos']);
                Core::msg_show('Пункт удален', 'msg');
            }
        }
        else
        {
            $_SESSION['main_menu_confirm_delete'] = uniqid();
            echo '
                Вы действительно хотите удалить этот пункт ('.$menu['name'].')?<br />
                <a href="?act=delete&amp;id='.$id.'&amp;confirm='.$_SESSION['main_menu_confirm_delete'].'">Да, удалить</a>';
        }
        
        echo '<a href="?" class="link">Вернутся в &quot;админку&quot;</a>';
        break;
    
        default:
         
        echo '<div class="menu_razd">Экспресс-панель</div><table><tr>';

        $sql->query('SELECT * FROM `speed_dial` ORDER BY `pos`');
    
        while ($menu = $sql->fetch())
        {
            if (empty($menu['link']))
            {
                echo '</tr></table><div class="menu_razd">'.$menu['name'].'</div><table><tr>';
            }
            else
            {
                if ($menu['new_line'] == 1)
                    echo '</tr><tr>';
                echo '<td><a title="'.$menu['name'].'" href="'.$menu['link'].'"><img width="48px" src="'.$menu['icon'].'"/></a><br /><span class="status">'.WordCut($menu['name']).'</span></td>';
            }
        }
        
        echo '</tr></table><a href="." class="link">Расширенный вид админки</a>';
        
        break;
        
    }
    
    echo '<a href="/" class="link">Главная</a>';
    
    include incDir.'foot.php';