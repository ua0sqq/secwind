<?php

if ($user_id && $id && ($do == 1 || $do == 2))
{

    $voter = $sql->query(
            "SELECT COUNT(*) FROM `mod_lib_counters` " .
            "WHERE `aid` = '" . $id . "' AND `uid` = '" . $user_id . "' AND `type` = '1'"
        )->result();

    if ($voter)
    {
        $error = 'Вы уже голосовали';
    }
    else
    {
        $arc = $sql->query("SELECT `rate_plus`, `rate_minus` FROM `mod_lib` WHERE `id` = '" . $id . "' AND `type` = 'arc'")->fetch();
        if ($arc === FALSE)
        {
            $error = 'Статья не найдена';
        }
        else
        {
            $vote = $do == 1 ? ($arc['rate_minus'] + 1) : ($arc['rate_plus'] + 1);
            $sql->query("UPDATE `mod_lib` SET `" . ($do == 1 ? "rate_minus" : "rate_plus") . "` = '" . $vote . "' WHERE `id` = '" . $id . "'");
            $sql->query("INSERT INTO `mod_lib_counters` SET `aid` = '" . $id . "', `uid` = " . $user_id . ", `type` = '1'");
            echo '<div class="msg">Ваш голос принят &#160;<a href="?act=articles&amp;mod=view&amp;id=' . $id .'">Продолжить</a></div>';
        }

    }

}
else
{

    $error = 'Ошибка принятых данных';

}

if (!empty($error))
{
    $error .= '<br /><a href="?act=articles&amp;mod=view&amp;id=' . $id .'">Продолжить</a>';
}