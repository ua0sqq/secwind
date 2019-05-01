<?php

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