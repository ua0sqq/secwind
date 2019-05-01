<?php
    include '../engine/includes/start.php';

    if (!$creator)
        Core::stop();

    Core::get('page.class', 'classes');

    $type = isset($_GET['type']) ? my_esc($_GET['type'], true) :false; // От школоты
    $sort = isset ($_GET['sort']) ? $_GET['sort'] : null;

    switch($type)
    {
        case 'php':
            $set['title'] = 'Ошибки php';
                break;

        case 'mysql':
            $set['title'] = 'Ошибки MySQL';
                break;

        case 'loading':
            $set['title'] = 'Анализ нагрузок сайта';
                break;

        default:
            $set['title']='Ошибки сервера';
                break;
    }

    require incDir.'head.php';

    if (isset($_GET['do']))
    {
        if (isset($_GET['yes']))
        {
            $sql->query('DELETE FROM `errors` WHERE `type` = "'.$type.'"');
            echo '<div class="menu_razd">Таблица логов очищена</div>';
        }
        else
        {
            echo '<div class="post">Вы действительно хотите очистить таблицу логов?<br />';
            echo '<a href="?do=clean&amp;yes&amp;type='.$type.'">Очистить</a> | <a href="?type='.$type.'">Отмена</a></div>';
        }
    }

    echo '<div class="menu_razd">Сортировка: ';
    switch ($sort)
    {
        case 'request' :
            $sort = 'request';
            echo '<a href="?sort=date&amp;type='.$type.'">Дата</a> | Запрос</div>';
            $order = '`url` DESC';
        break;
        default :
            $sort = 'date';
            echo 'Дата | <a href="?sort=request&amp;type='.$type.'">Запрос</a></div>';
            $order = '`time` ASC';
        break;
    }

    $total = $sql->query('SELECT count(*) FROM `errors` WHERE `type` = "'.$type.'"')->result();
    $page = new page($total, $set['p_str']);
    if ($total)
    {
        $req = $sql->query("SELECT `errors`.*,`user`.`id` as `user_id`,`user`.`nick` FROM `errors` LEFT JOIN `user` ON `errors`.`user` = `user`.`id` WHERE `type` = '$type' ORDER BY $order LIMIT ".$page->limit());
        $i = 1;
        while ($res = $sql->fetch())
        {
            echo ($i ++ % 2 ? '<div class="p_m">' : '<div class="p_t">') .
                '<a href="/info.php?id='.$res['user_id'].'">'.$res['nick'].'</a> (' . Core::time($res['time']). ')<br />'.
                'URL: <a href="' . $res['url'] . '">' . $res['url'] . '</a><br />'.
                'IP: '.$res['ip'].'<br />'.
                'Описание: '.$res['desc'].'</div>';
        }
    }
    else
    {
        echo '<div class="post">Список пуст</div>';
    }

    $page->display('?type='.$type.'&amp;');
    ?>
    <div class="menu_razd"><a href="?do=clean&amp;type=<?=$type?>">Очистить список</a></div>
	<a href='/admin/?act=security'><div class="link">Безопасность</div></a>
    <a href='/admin/'><div class="link">В админку</div></a>
    <?php

    $page = $res = null;

    unset($page, $type, $res, $total, $sort, $order); 
    require incDir.'foot.php';