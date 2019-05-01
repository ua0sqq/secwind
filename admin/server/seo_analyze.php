<?php

	include '../../engine/includes/start.php';

	if (!$creator)
        Core::stop();
	
	$set['title'] = 'Информация о сервере';

	include incDir . 'head.php';

    if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
    {
        Core::msg_show('На локальном сервере Сео анализ не возможен');
    }
    else
    {

    $ci_url = 'http://bar-navig.yandex.ru/u?ver=2&show=32&url=http://www.'.$_SERVER['SERVER_NAME'].'/'; 
    $ci_data = implode("", file($ci_url));
    preg_match("/value=\"(.\d*)\"/", $ci_data, $ci); 


    $xml = file_get_contents('http://bar-navig.yandex.ru/u?ver=2&url=http://'.$_SERVER['SERVER_NAME'].'&show=1&post=1'); 
    preg_match('/<tcy rang=\"\d\" value=\"(\d+)\"\/>/Usi', $xml, $res); 

    function GetPosition($keyword, $max_pos = 10)
	{
		if(isset($keyword)) 
		{
   			 $make_url = 'http://www.google.com/search?hl=en&q=' . urlencode($keyword) . '&start=';
			 $index=0; // counting start from here
			 $found=false; // set this flag to true when position found
   			 for ($page = 0; $page < $max_pos; $page++) 
			 {
     			if($found==true) // break the loop when position found
	 			break;
	 			$readPage = fopen($make_url . $page  . 0 ,'r');
     			$contains = '';
      			if ($readPage) 
				{
        			while (!feof($readPage)) 
					{
            			$buffer = fgets($readPage, 4096);
            			$contains .= $buffer;
        			}
        			fclose($readPage);
      		     }
				$results = array();
				preg_match_all('/a href="([^"]+)" class=l.+?>.+?<\/a>/',$contains,$results);
				foreach ($results[1] as $link) 
				{
				$link = preg_replace('(^http://|/$)','',$link);
				$index=$index+1;
				if (strlen(stristr($link,$this->url))>0) 
				{
				$found=true;
				break;
				}
				}
			}	
			if($found==true)
			return $index;
			else
			return -1;
        }
   	return -1;	
   }
    
    @set_time_limit(25);

    ?>
        <div class="menu_razd">SEO анализ сайта</div>

        <div class="p_m">
        Google PR: <?=$res[1]?><br />
        Yandex ТИЦ: <?=is_numeric($ci[1]) ? $ci[1] : 0?>
        </div>

        <div class="p_t">Позиции в Google по мета ключам:<br />
        <?php
            $keys = explode(',', $set['meta_keywords']);
            foreach($keys as $key)
            {
                echo $key . GetPosition($key).'<br />';
            }
        ?>
        </div>
    <?php
    }
	echo '
		<div class="menu_razd">См. также</div>
		<div class="link"><a href="..?act=server">Сервер</a></div>
		<div class="link"><a href="..">Админка</a></div>';
	include incDir . 'foot.php';