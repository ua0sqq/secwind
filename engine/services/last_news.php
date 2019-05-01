<?php
	
	$sql->query('SELECT `time`, `title`, `msg` FROM `news` order by `id` DESC LIMIT 1');
	
	if ($sql->num_rows() == 1)
	{
        Core::get('text.class');
	    $news = $sql->fetch();
		return '<br />'.htmlspecialchars($news['title']) .' ('.Core::time($news['time']).')<br />'.htmlspecialchars(strip_tags(mb_substr(text::output($news['msg']), 0, 150)));
	}
    return null;
