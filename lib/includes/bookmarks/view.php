<?php

Core::get(array('page.class', 'cache.class'), 'classes');

$cache = new cache(H . 'engine/files/tmp/lib[my_bm='.$user_id.';page='.(isset($_GET['page']) ? intval($_GET['page']) : 1).'].swc');

if ($user_id)
{

    if (!$cache->life())
    {

    ob_start();
    echo '<div class="fmenu">' . 'Библиотека' . ' | Мои закладки</div>';

    
    $total = $sql->query("SELECT COUNT(*) FROM `mod_lib_counters` WHERE `uid` = '" . $user_id . "' AND `type` = '2'")->result();
    $page = new page($total, $set['p_str']);

    if ($total)
    {

        $query = $sql->query(
            "SELECT `mod_lib`.`name`, `mod_lib`.`announce`, `mod_lib`.`id` FROM `mod_lib` " .
            "LEFT JOIN `mod_lib_counters` ON `mod_lib`.`id` = `mod_lib_counters`.`aid` " .
            "WHERE `mod_lib_counters`.`uid` = '" . $user_id . "' AND `mod_lib_counters`.`type` = '2' " .
            "ORDER BY `mod_lib`.`name` ASC LIMIT " . $page->limit());
        $i = 0;

        while ($arc = $sql->fetch())
        {

            echo '<div class="' . ($i % 2 ? 'p_m' : 'p_t') . '">' .
                 '<img src="' . ICONSDIR . 'arc.png" alt="" />&#160;' .
                 '<a href="?act=articles&amp;mod=view&amp;id=' . $arc['id'] . '">' .
                 htmlentities($arc['name'], ENT_QUOTES, 'UTF-8') . '</a>' .
                 '<br />' . htmlentities($arc['announce'], ENT_QUOTES, 'UTF-8') .
                 '<div class="sub"><a href="?act=bookmarks&amp;mod=remove&amp;id=' . $arc['id'] . '&amp;bookmarks">Удалить</a></div></div>';
            $i++;

        }

    }
    else
    {

        echo '<div class="menu">Список пуст</div>';

    }


    $page->display('?act=bookmarks&amp;mod=view&amp;');
  

    echo '<div class="menu"><a href="index.php">В библиотеку</a><br />' .
         '<a href="?act=bookmarks&amp;mod=clean">Удалить все</a></div>';
    $cache->write();

    }
    echo $cache->read();
}
else
{

    $error = 'Доступ запрещен';

}