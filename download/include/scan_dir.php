<?php
$set['title'] = 'Обновление';
include H.'engine/includes/head.php';
$i_three = $i_two = $i = 0;
if ($admin) {
    @set_time_limit(99999);
    switch ($do) {
        case 'clean':
            $req = mysqli_query($sql->db, "SELECT `id`, `dir`, `name`, `type` FROM `down_files`");
            while ($result = $sql->fetch($req)) {
                if (!file_exists($result['dir'] . '/' . $result['name'])) {
                    if ($result['type'] == 1) {
                        $req = mysqli_query($sql->db, "SELECT `id` FROM `down_files` WHERE `refid` = '" . $result['id'] . "'");
                        while ($res = mysqli_fetch_assoc($req)) {
                            $sql->multi("DELETE FROM `down_comms` WHERE `refid`='" . $res['id'] . "';DELETE FROM `down_more` WHERE `refid` = '" . $res['id'] . "'", true);
                        }
                        $sql->query("DELETE FROM `down_files` WHERE `id` = '" . $result['id'] . "' OR  `refid` = '" . $result['id'] . "'", true);
                    }
                    else {
                        $req = mysqli_query($sql->db, "SELECT `id` FROM `down_more` WHERE `refid` = '" . $result['id'] . "'");
                        while ($res = $sql->fetch($req)) {
                            @unlink($result['dir'] . '/' . $res['name']);
                        }
                        $sql->multi("DELETE FROM `down_more` WHERE `refid` = '" . $result['id'] . "';DELETE FROM `down_comms` WHERE `refid`='" . $result['id'] . "';DELETE FROM `down_files` WHERE `id` = '" . $result['id'] . "' LIMIT 1");

                    }
                }
            }
            $sql->multi("OPTIMIZE TABLE `down_files`;OPTIMIZE TABLE `down_comms`;OPTIMIZE TABLE `down_more`;");
            ?>
            <div class="fmenu">Очистка БД от мусора</div>
            <div class="msg">База данных успешно обновлена</div>
            <div class="menu"><a href="index.php?act=recount">Пересчитать файлы ЗЦ</a></div>
            <div class="p_m"><a href="index.php?id=<?=$id?>">Вернуться</a></div>
            <?php
			break;
        default:
            if ($id) {
                $sql->query("SELECT `dir`, `name`, `rus_name` FROM `down_files` WHERE `type` = 1 AND `id` = '$id' LIMIT 1");
                $res_down_cat = $sql->fetch();
                $scan_dir = $res_down_cat['dir'] . '/' . $res_down_cat['name'];
                if (!is_dir($scan_dir)){//$sql->result() == 0) {
					echo 'Каталога "'.$res_down_cat['rus_name'].'" не существует<br /><a href="index.php">К категориям</a>';
                    include H.'engine/includes/foot.php';
                }
            }
            else {
                $scan_dir = $loadroot;
            }
            echo '<div class="fmenu">Обновление ' . ($id ? ' | ' . text::output($res_down_cat['rus_name']) : '') . '</div>';
            if (isset($_GET['yes'])) {
                $array_dowm = $array_id = $array_more = array();
                $sql->query("SELECT `dir`, `name`, `id` FROM `down_files`");
                while ($result = $sql->fetch()) {
                    $array_dowm[] = $result['dir'] . '/' . $result['name'];
                    $array_id[$result['dir'] . '/' . $result['name']] = $result['id'];
                }
                $sql->query("SELECT `name` FROM `down_more`");
                while ($result_more = $sql->fetch()) {
                    $array_more[] = $result_more['name'];
                }
                $array_scan = array();
                function scan_dir($dir = '')
                {
                    static $array_scan;
                    $arr_dir = glob($dir . '/*');
                    foreach ($arr_dir as $val) {
                        if (is_dir($val)) {
                            $array_scan[] = $val;
                            scan_dir($val);
                        }
                        else
                            if (basename($val) != 'index.php')
                                $array_scan[] = $val;
                    }
                    return $array_scan;
                }
                $arr_scan_dir = @scan_dir($scan_dir);
                if ($arr_scan_dir) {
                    $i_three = $i_two = $i = 0;
                    foreach ($arr_scan_dir as $val) {
                        if (!in_array($val, $array_dowm)) {
                            if (is_dir($val)) {
                                $name = my_esc(basename($val));
                                $dir = my_esc(dirname($val));
                                $refid = (int)@$array_id[$dir];
                                $sql->query("INSERT INTO `down_files` SET `refid`='$refid', `dir`='$dir', `time`='$time', `name`='$name', `type` = '1', `field`='0', `rus_name`='$name'");
                                $array_id[$val] = mysqli_insert_id($sql->db);
                                ++$i;
                            }
                            else {
                                $name = basename($val);
                                if (preg_match("/^file([0-9]+)_/", $name)) {
                                    if (!in_array($name, $array_more)) {
                                        $refid = (int)str_replace('file', '', $name);
                                        $name_link = check(mb_substr(str_replace('file' . $refid . '_', 'Скачать ', $name), 0, 200));
                                        $name = my_esc($name);
                                        $size = filesize($val);
                                        $sql->query("INSERT INTO `down_more` SET `refid`='$refid', `time`='$time',`name`='$name', `rus_name` = '$name_link',`size`='$fsize'");
                                        ++$i_two;
                                    }
                                }
                                else {
                                    $name = my_esc($name);
                                    $dir = my_esc(dirname($val));
                                    $refid = (int)$array_id[$dir];
                                    $sql->query("INSERT INTO `down_files` SET `refid`='$refid', `dir`='$dir', `time`='$time',`name`='$name', `text` = 'Скачать файл',`rus_name`='$name', `type` = '2',`user_id`='$user_id'");
                                    ++$i_three;
                                }
                            }
                        }
                    }
                }
                $sql->multi("OPTIMIZE TABLE `down_files`;OPTIMIZE TABLE `down_more`");
                echo '<div class="post">Добавлено:<br />Категорий: ' . $i . '<br />Файлов: ' . $i_three . '<br />Доп. Файлов: ' . $i_two . '</div>';
                echo '<div class="menu"><a href="index.php?act=scan_dir&amp;do=clean&amp;id=' . $id . '">Очистка БД от мусора</a> (рекомендуется)<br />';
                echo '<a href="index.php?act=recount">Пересчитать файлы ЗЦ</a></div>';
            }
            else
                echo '<div class="p_t">Обновить? - <a href="index.php?act=scan_dir&amp;yes&amp;id=' . $id . '">Да</a></div>';
            echo '<div class="p_m"><a href="index.php?id=' . $id . '">Вернуться</a></div>';
    }
    if (file_exists(H.'engine/files/tmp/download[dir='.$id.'].swc'))
        unlink(H.'engine/files/tmp/download[dir='.$id.'].swc');
}
else
    echo 'Доступ закрыт<br /><a href="index.php">К категориям</a>';