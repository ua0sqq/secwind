<?php

    function save_settings($set)
	{
        include_once H.'engine/classes/ini.class.php';
        $ini = new ini(H.'engine/files/data/settings.ini');
		foreach($set as $key => $value)
        {
            $ini->write($key, '"'.$value.'"');
        }
        return $ini->updateFile();
	}

// рекурсивное удаление папки
function delete_dir($dir){
if (is_dir($dir)){$od=opendir($dir);
while ($rd=readdir($od)){
if ($rd == '.' || $rd == '..') continue;
if (is_dir("$dir/$rd")){
@chmod("$dir/$rd", 0777);
delete_dir("$dir/$rd");}
else{
@chmod("$dir/$rd", 0777);
@unlink("$dir/$rd");}}
closedir($od);
@chmod("$dir", 0777);
return @rmdir("$dir");}
else{
@chmod("$dir", 0777);
@unlink("$dir");}}