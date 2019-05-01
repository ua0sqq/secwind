<?php

if ($id)
{

    $arc = $sql->query("SELECT `name`, `author_id` FROM `mod_lib` WHERE `id` = " . $id . " AND `type` = 'arc' AND `mod` = '0'")->fetch();

    if ($arc !== FALSE)
    {

        echo '<div class="fmenu">' . 'Библиотека' . ' | ' . htmlentities($arc['name'], ENT_QUOTES, 'UTF-8') . '</div>';

        if (!empty($_POST['text']))
        {
            $sql->query("insert into `mod_lib_comments` set `text` = '". my_esc($_POST['text']) . "', `time` = ".time().", `sub_id` = ".$id.", `user_id` = ".$user_id);
        }

        Core::get('page.class', 'classes');

        $total = $sql->query('SELECT count(*) from `mod_lib_comments` where `sub_id` = '.$id)->result();

        $i = 1;

        $page = new page($total, $set['p_str']);

        $sql->query("SELECT * FROM `mod_lib_comments` WHERE `sub_id` = ".$id." ORDER BY `id` DESC LIMIT ".$page->limit());
        
        while ($coms = $sql->fetch())
        {
            $data = array('status' => $coms['time'], 'is_time' => true, 'post' => $coms['text']);
            echo '<div class="'.($i++ % 2 ? 'p_m' : 'p_t').'">'.Core::user_show(Core::get_user($coms['user_id']), $data).'</div>';
        }

        $page->display('?act=comments&amp;id='.$id.'&amp;');

        if (!$total)
            echo '<div class="post">Нет комментариев</div>';

        ?>
        <form method="post" action="?act=comments&amp;id=<?=$id?>">
            <textarea name="text"></textarea><br />
        <input type="submit" value="Отправить"/>
        </form>
        <?php

        echo '<div class="post"><a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Назад</a><br /><a href="index.php">В библиотеку</a></div>';
    }
    else
    {

        $error = 'Статья не найдена';

    }

}
else
{

    $error = 'Ошибка принятых данных';

}