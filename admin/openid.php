<?php
    include '../engine/includes/start.php';
    if (is_file(H . 'engine/files/data/sw_login.ini'))
    {
        $sw_login =  parse_ini_file(H . 'engine/files/data/sw_login.ini');
    }
    else
    {
        include incDir. 'head.php';
        echo '<a href="/admin/server/settings.php">Введите свои данные от secwind.ru</a>';
        include incDir. 'foot.php';
    }
    
	if (isset($_GET['update_token']))
    {
        unset($_SESSION['sw_token']);
    }
    
    if (isset($_SESSION['sw_token'], $_SESSION['sw_token_time']) && $_SESSION['sw_token_time'] > time())
    {
        switch($act)
        {
            default:
            case 'report_bug':
                $set['title'] = 'Техническая поддержка';
                include incDir. 'head.php';
                
                Core::get('text.class', 'classes');
                
                if (isset($_GET['response']))
                {
                    if ($_GET['response'] == 'ok')
                    {
                        Core::msg_show('Сообщение отправлено', 'msg');
                    }
                    else
                    {
                    Core::msg_show('Отправка невозможна');
                    }
                }
                ?>
                    <form action="http://<?=Core::Secwind('support')?>/openid.php" method="get">
                    <input type="hidden" name="act" value="report_bug"/>
                    <input type="hidden" name="token" value="<?=$_SESSION['sw_token']?>"/>
                    <textarea name="report"></textarea><br />
                    <input type="submit" value="Отправить"/>
                    </form>
                    <?php
                    $journal = json_decode(file_get_contents('http://'.Core::Secwind('support').'/openid.php?act=report_journal&token='.$_SESSION['sw_token']), true);
                    $count = count($journal);
                    for ($i=0; $i < $count; $i++)
                    {
                        echo '<div class="link">'.text::output($journal[$i], array('smiles' => true, 'bbcode' => true, 'html' => false, 'br' => true)). '</div>'; 
                        if ($i == 15)
                        {
                            break;
                        }
                    }
                    ?>
                    <form action="http://<?=Core::Secwind('support')?>/openid.php" method="get">
                    <input type="hidden" name="act" value="report_bug"/>
                    <input type="hidden" name="token" value="<?=$_SESSION['sw_token']?>"/>
                    <textarea name="report"></textarea><br />
                    <input type="submit" value="Отправить"/>
                    </form>
                    <?php
            break;
        }
    }
    else
    {
        include incDir. 'head.php';
       
        $data = json_decode(file_get_contents('http://'.Core::Secwind('support').'/openid.php?id='.$sw_login['id'].'&password='.$sw_login['password'].'&site='.$_SERVER['SERVER_NAME']), true);
        
        if (!$data)
        {
           Core::msg_show('Сервер не доступен');
        }
        elseif ($data['status'] == 'ok')
        {
            $_SESSION['sw_token'] = $data['token'];
            $_SESSION['sw_token_time'] = $data['time'];
            Core::msg_show('Авторизация успешна', 'msg');
            echo '<a href="?act=report_bug&amp;token='.$data['token'].'" class="link">Техническая поддержка</a>';
        }
        else
        {
            Core::msg_show('Ошибка №'.$data['error'].': '.$data['message']);
        }
    }
    echo '<div class="link"><a href="index.php?act=about">SecWind</a></div>
	<div class="link"><a href="..">Админка</a></div>';
    include incDir. 'foot.php';