<?php
    include '../../engine/includes/start.php';

    if (!$creator)
        Core::stop();

	$set['title'] = 'Главное меню';
    include incDir.'head.php';

    
    if (isset($_GET['move']))
    {
        $sql->query('SELECT `name`, `pos` FROM `main_menu` WHERE `id` = '.$id);
        
        if ($sql->num_rows() == 0)
        {
            Core::msg_show('Пункт с id '.$id.' не найден');
            include incDir.'foot.php';
        }
        
        $menu = $sql->fetch();
        $new_pos = $_GET['move'] == 'up' ? $menu['pos'] - 1 : $menu['pos'] + 1;
        
        $sql->query('UPDATE `main_menu` set `pos` = '.$menu['pos'].' WHERE `pos` = '.$new_pos);
        $sql->query('UPDATE `main_menu` set `pos` = '.$new_pos.' WHERE `id` = '.$id);
        Core::msg_show('Пункт '.$menu['name'].' перемещен', 'msg');
    }
    
    switch($act)
    {
        case 'edit':
        
        $sql->query('SELECT * FROM `main_menu` WHERE `id` = '.$id);
        
        if ($sql->num_rows() == 0)
        {
            Core::msg_show('Пункт с id '.$id.' не найден');
            include incDir.'foot.php';
        }
        
        $menu = $sql->fetch();
        
        if (isset($_POST['name']))
        {
            $sql->query("UPDATE `main_menu` SET
                `name` = '".Core::form('name')."',
                `link` = '".Core::form('link')."',
                `file` = '".Core::form('file')."',
                `icon` = '".Core::form('icon')."'
                WHERE `id` = $id");
            Core::msg_show('Пункт отредактирован', 'msg');
        }
        else
        {
            ?>
            <div class="menu_razd">Редактирование пункта <?=$menu['name']?></div>
            <form method="post">
                Название: (обязательно)<br />
                <input type="text" name="name" value="<?=$menu['name']?>"/><br />
                Ссылка:<br />
                <input type="text" name="link" value="<?=$menu['link']?>"/><br />
                Файл:<br />
                <input type="text" name="file" value="<?=$menu['file']?>"/><br />
                Иконка:<br />
                <input type="text" name="icon" value="<?=$menu['icon']?>"/><br />
                <input type="submit" value="Изменить"/>
            </form>
            <?php
        }
        
        echo '<a href="?" class="link">Вернутся в &quot;Главное меню&quot;</a>';
        
        break;
        
        case 'delete':
        
        $sql->query('SELECT `name`, `pos` FROM `main_menu` WHERE `id` = '.$id);
        
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
                $sql->query('DELETE FROM `main_menu` WHERE `id` = '.$id);
                $sql->query('UPDATE `main_menu` SET `pos` = `pos` - 1 WHERE `pos` > '.$menu['pos']);
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
        
        echo '<a href="?" class="link">Вернутся в &quot;Главное меню&quot;</a>';
        
        break;
        
        case 'add':
        
        if (isset($_POST['name']))
        {
            $pos = $sql->query('SELECT max(`pos`) from `main_menu`')->result() + 1; // получаем позицию нового пункта
            $sql->query("INSERT INTO `main_menu` SET
                `name` = '".Core::form('name')."',
                `link` = '".Core::form('link')."',
                `file` = '".Core::form('file')."',
                `icon` = '".Core::form('icon')."',
                `pos` = '$pos'");
            Core::msg_show('Пункт добавлен', 'msg');
        }
        else
        {
            ?>
            <div class="menu_razd">Добавление пункта</div>
            <form method="post">
                Название: (обязательно)<br />
                <input type="text" name="name"/><br />
                Ссылка: (оставить пустым для разделителя)<br />
                <input type="text" name="link"/><br />
                Файл:<br />
                <input type="text" name="file"/><br />
                Иконка:<br />
                <input type="text" name="icon" value="/style/icons/"/><br />
                <input type="submit" value="Добавить"/>
            </form>
            <?php
        }
        
        echo '<a href="?" class="link">Вернутся в &quot;Главное меню&quot;</a>';
        
        break;
        
        default:
        
        $pos = $sql->query('SELECT max(`pos`) from `main_menu`')->result();
        $sql->query('SELECT * FROM `main_menu` ORDER BY `pos`');
        
        if ($sql->num_rows() == 0)
        {
            Core::msg_show('Нет добавленных пунктов');
        }
        else
        {
            
            while($menu = $sql->fetch())
            {
                echo 
                    '<div class="post">',
                    $menu['pos'] . ') ',
                    (!empty($menu['icon']) ? '<img style="float:right" src="' . str_replace('{theme}', $set['theme'], $menu['icon']) .'"/>  ' : ''),
                    $menu['name'] . '<br />',
                    (!empty($menu['link']) ? 'Ссылка: ' . $menu['link'] . '<br />' : ''),
                    (!empty($menu['file']) ? 'Файл: ' . $menu['file'] . '<br />' : ''),
                    ($menu['pos'] > 1 ? '<a href="?move=up&amp;id='.$menu['id'].'">[ &uarr; ]</a>&nbsp;&nbsp;' : ''),
                    '[ <a href="?act=edit&amp;id='.$menu['id'].'">изменить</a> ] | [ <a href="?act=delete&amp;id='.$menu['id'].'">удалить</a> ]',
                    ($menu['pos'] < $pos ? '&nbsp;&nbsp;<a href="?move=down&amp;id='.$menu['id'].'">[ &darr; ]</a>' : ''),
                    '</div>';
            }
        }
        
        echo '<a href="?act=add" class="link">Добавить</a>';
        
        break;
    }
   ?>
	<a href='/admin/?act=design' class="link">Персонализция</a>
    <a href='/admin/' class="link">Админка</a>
    <?php
    include incDir.'foot.php';