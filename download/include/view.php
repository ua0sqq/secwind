<?php

$sql->query("SELECT * FROM `down_files` WHERE `id` = '$id' AND (`type` = 2 OR `type` = 3)  LIMIT 1");
$res_down = $sql->fetch();
if ($sql->num_rows() == 0 || !is_file($res_down['dir'] . '/' . $res_down['name'])) {
    $set['title'] = 'Загрузки ';
include H.'engine/includes/head.php';

    echo 'Файл не найден<br /><a href="index.php">К категориям</a>';
    include H.'engine/includes/foot.php';
}

//$title_pages = text::output(mb_substr($res_down['rus_name'], 0, 30));
//$textl = mb_strlen($res_down['rus_name']) > 30 ? $title_pages . '...' : $title_pages;

$tree = array();
$dirid = $res_down['refid'];
while ($dirid != '0' && $dirid != "") {
    $sql->query("SELECT `rus_name`, `refid` FROM `down_files` WHERE `type` = 1 AND `id` = '$dirid' LIMIT 1");
    $res = $sql->fetch();
    $tree[] = '<a href="index.php?id=' . $dirid . '">' . text::output($res['rus_name']) . '</a>';
    $dirid = $res['refid'];
}
krsort($tree);
//$way = null;
//foreach ($tree as $value) {
  //  $way .= $value . ', ';
//}
//$way .= $res_down['name'];

//$set['meta_keywords']= strip_tags($way) .' Tadochi';
//$set['meta_description']= htmlspecialchars(file_get_contents(H.'download/about/' . $id . '.txt'));

$set['title'] = 'Загрузки / '.htmlspecialchars($res_down['name']);
include H.'engine/includes/head.php';
$cache = new cache(H.'engine/files/tmp/download[file='.$id.';page='.(isset($_GET['page']) ? intval($_GET['page']) : 1).'].swc');
if (!$cache->life(60) || (isset($_GET['plus']) || isset($_GET['minus'])))
{
ob_start();
if ($res_down['type'] == 3) {
    echo '<div class="err">Файл находится на модерации</div>';
    if (!$admin) {
        include H.'engine/includes/foot.php';
    }

}
    $f1 = strrpos($res_down['name'], ".");
        $f2 = substr($res_down['name'], $f1 + 1, 999);
        $format_file = strtolower($f2);
		


echo '<div class="fmenu">' . text::output($res_down['rus_name']) . '</div>';
$text_info = '';
$screen = array();
if (is_dir($screenroot . '/' . $id)) {
    $screen = glob('../download/screen/' . $id . '/*');
}
if ($format_file == 'jpg' || $format_file == 'jpeg' || $format_file == 'gif' || $format_file == 'png') {
    $info_file = getimagesize($res_down['dir'] . '/' . $res_down['name']);
    echo '<div class="post"><img src="' . htmlspecialchars($res_down['dir'] . '/' . $res_down['name']) . '" width="50%" alt="preview" /></div>';
    $text_info = '<b>Разрешение: </b>' . $info_file[0] . 'x' . $info_file[1] . ' px<br />';
}
else
    if (($format_file == '3gp' || $format_file == 'avi' || $format_file == 'mp4') && extension_loaded('ffmpeg') && !$screen) {
        //TODO: написать позже, когда скрипт будет на хосте
        //$info_file = new ffmpeg_movie($res_down['dir']. '/' . $res_down['name']);
    } elseif (($format_file == 'thm' || $format_file == 'nth') && !$screen && $set_down['theme_screen']) {
        /*  $file_id = $id;
        require_once ("include/screen_theme.php");
        $screen = 'screen/' . $id . '.gif';  */
    } elseif ($format_file == 'mp3') {
        require_once ("include/class_mp3.php");
        $mp3 = new MP3_Id();
        $res_downult = $mp3->read($res_down['dir'] . '/' . $res_down['name']);
        $res_downult = $mp3->study();
        $text_info = '<b>Исполнитель</b>: ' . text::output($mp3->artists) . '<br /><b>Альбом</b>: ' . text::output($mp3->album) . '<br /><b>Год выхода</b>: ' . $mp3->year . '<br />
  <b>Композиция:</b> ' . text::output($mp3->name) . '<br/><b>Каналы:</b> ' . text::output($mp3->getTag('mode')) . '<br/><b>Битрейт:</b> ' . ($mp3->getTag('bitrate') > 0 ? $mp3->getTag('bitrate') : 'Не удалось распознать кодек') . '<br />';
    }
if ($screen) {
    echo '<div class="p_m"><b>Скриншот';
	
    $total = count($screen);
	$page = new page($total, 1);
	$start=$page->start();//1*$page->page()-1;
    if ($total > 1) {
        $end = $start + 1;
        if ($end > $total)
            $end = $total;
        echo ' (' . $end . '/' . $total . '):</b><br />';
        for ($i = $start; $i < $end; $i++) {
            echo '<img src="' . htmlentities($screen[$i], ENT_QUOTES, 'utf-8') . '" alt="screen" />';
        }
        $page->display('index.php?act=view&amp;id=' . $id . '&amp;');

    }
    else {
        echo ':</b><br /><img src="' . htmlentities($screen[0], ENT_QUOTES, 'utf-8') . '" alt="screen"/>';
    }
    echo '</div>';
}
$user2 = $sql->query("SELECT `nick`, `id` FROM `user` WHERE `id`='" . $res_down['user_id'] . "' LIMIT 1")->fetch();
if ($user2['id'])
    $user_up = '<a href="/pages/user.php?id=' . $user2['id'] . '">' . $user2['nick'] . '</a>';
else
    $user_up = '[удален]';
echo '<div class="post"><b>Имя на сервере:</b> ' . htmlspecialchars($res_down['name']) . '<br /><b>Скачали:</b> ' . $res_down['field'] . ' раз(а)
<br /><b>Выгрузил:</b> ' . $user_up . '<br />' . $text_info;
if (is_file('about/' . $id . '.txt'))
    echo '<b>Описание:</b> ' . text::output(file_get_contents('about/' . $id . '.txt'));
echo '<div class="sub"></div>';
$file_rate = explode('|', $res_down['rate']);
if ((isset($_GET['plus']) || isset($_GET['minus'])) && !isset($_SESSION['rate_file_' . $id])) {
    if (isset($_GET['plus']))
        $file_rate[0] = $file_rate[0] + 1;
    else
        $file_rate[1] = $file_rate[1] + 1;
    $sql->query("UPDATE `down_files` SET `rate`='" . $file_rate[0] . '|' . $file_rate[1] . "' WHERE `id`='$id'");
    echo '<b><span class="green">Ваш голос принят</span></b><br />';
    $_SESSION['rate_file_' . $id] = 1;
}
$sum = ($file_rate[1] + $file_rate[0]) ? round(100 / ($file_rate[1] + $file_rate[0]) * $file_rate[0]) : 50;
echo '<b>Рейтинг файла</b>' . (!isset($_SESSION['rate_file_' . $id]) ? '(<a href="index.php?act=view&amp;id=' . $id . '&amp;plus">+</a>/<a href="index.php?act=view&amp;id=' . $id . '&amp;minus">-</a>)' : '(+/-)') . ': <b><span class="green">' . $file_rate[0] .
    '</span>/<span class="red">' . $file_rate[1] . '</span></b>
<br /><img src="vote_img.php?img=' . $sum . '" alt="Рейтинг" /><br /></div><div class="p_m"><table  width="100%"><tr><td width="16" valign="top"><img src="' . $filesroot . '/images/' . (file_exists($filesroot . '/images/' . $format_file .
    '.png') ? $format_file . '.png' : 'file.gif') . '" alt="file" /></td><td><a href="index.php?act=load_file&amp;id=' . $id . '">' . $res_down['text'] . '</a> (' . text::size_data(filesize($res_down['dir'] . '/' . $res_down['name'])) . ') <div class="sub">Добавлен: ' .
    Core::time($res_down['time']);
if ($format_file == 'jar')
    echo ', <a href="index.php?act=jad_file&amp;id=' . $id . '">JAD файл</a>';
elseif ($format_file == 'txt') {
    echo ', Скачать в <a href="index.php?act=txt_in_zip&amp;id=' . $id . '">ZIP</a> / <a href="index.php?act=txt_in_jar&amp;id=' . $id . '">JAR</a>';
}
else
    if ($format_file == 'zip')
        echo ', <a href="index.php?act=open_zip&amp;id=' . $id . '">Открыть архив</a>';
echo '</div></td></tr></table></div>';
$sql->query("SELECT * FROM `down_more` WHERE `refid` = '$id'");
$k = 1;
if ($sql->num_rows()) {
    while ($res_file_more = $sql->fetch()) {
        echo ($k % 2) ? '<div class="p_m">' : '<div class="p_t">';
        $format = explode('.', $res_file_more['name']);
        $format_file = strtolower($format[count($format) - 1]);
        echo '<table  width="100%"><tr><td width="16" valign="top"><img src="' . $filesroot . '/images/' . (file_exists($filesroot . '/images/' . $format_file . '.png') ? $format_file . '.png' : 'file.gif') . '" alt="file" />
               </td><td><a href="index.php?act=load_file&amp;id=' . $id . '&amp;more=' . $res_file_more['id'] . '">' . $res_file_more['rus_name'] . '</a> (' . size_file($res_file_more['size']) . ')
             <div class="sub"> Добавлен: ' . Core::time($res_file_more['time']);
        if ($format_file == 'jar')
            echo ', <a href="index.php?act=jad_file&amp;id=' . $id . '&amp;more=' . $res_file_more['id'] . '">JAD файл</a>';
        elseif ($format_file == 'txt') {
            echo ', Скачать в <a href="index.php?act=txt_in_zip&amp;id=' . $id . '&amp;more=' . $res_file_more['id'] . '">ZIP</a> / <a href="index.php?act=txt_in_jar&amp;id=' . $id . '&amp;more=' . $res_file_more['id'] . '">JAR</a>';
        }
        else
            if ($format_file == 'zip')
                echo ', <a href="index.php?act=open_zip&amp;id=' . $id . '&amp;more=' . $res_file_more['id'] . '">Открыть архив</a>';
        echo '</div></td></tr></table></div>';
        ++$k;
    }
}
echo '<div class="menu"><a href="index.php?act=comms&amp;id=' . $res_down['id'] . '">Комментарии</a> (' . $res_down['total'] . ')</div>';
$cdir = array_pop($tree);
echo '<div class="p_m"><a href="index.php?">Загрузки</a> &raquo; ';
foreach ($tree as $value) {
    echo $value . ' &raquo; ';
}
echo '<a href="index.php?id=' . $res_down['refid'] . '">' . strip_tags($cdir) . '</a></div>';
$cache->write();
}
echo $cache->read();
if ($admin) {
    echo '<div class="menu">';
    echo '<a href="index.php?act=edit_about&amp;id=' . $id . '">Изменить описание</a><br /><a href="index.php?act=edit_screen&amp;id=' . $id . '">Управление скриншотами</a><br />';
    echo '<a href="index.php?act=file_more&amp;id=' . $id . '">Дополнительные файлы</a><br /><a href="index.php?act=del_file&amp;id=' . $id . '">Удалить файл</a></div>';
}
