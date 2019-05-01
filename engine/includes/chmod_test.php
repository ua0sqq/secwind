<?php

$install = !file_exists(H.'engine/files/data/settings.ini');

chmod(H.'style/users/avatar/',0777);
chmod(H.'engine/files/data/',0777);

chmod(H.'engine/files/modules/archives/',0777);
chmod(H.'engine/files/modules/configs/',0777);
chmod(H.'engine/files/backup/files/',0777);
chmod(H.'engine/files/backup/mysql/',0777);
chmod(H.'engine/files/users/cache/',0777);
chmod(H.'engine/services/',0777);

chmod(H.'engine/files/tmp/',0777);
chmod(H.'style/themes/',0777);
chmod(H.'style/smiles/',0777);
if ($install)
{
chmod(H.'install/',0777);
chmod(H.'lib/files/attach/',0777);
chmod(H.'lib/files/cache/',0777);
chmod(H.'lib/files/download/html/',0777);
chmod(H.'lib/files/download/txt/',0777);
chmod(H.'lib/files/upload/',0777);
chmod(H.'download/files/',0777);
chmod(H.'download/about/',0777);
chmod(H.'download/screen/',0777);
chmod(H.'download/time_files/created_java/files', 0777);
chmod(H.'download/time_files/created_zip/', 0777);
chmod(H.'download/time_files/open_zip/', 0777);
chmod(H.'forum/files/attach/', 0777);
}
else
chmod(H.'engine/files/data/settings.ini', 0777);

function permissions($filez){
return decoct(@fileperms("$filez")) % 1000;
}


function test_chmod($df,$chmod)
{
global $err,$user;
if (isset($user) && $user['level']==10)
$show_df=preg_replace('#^'.preg_quote(H).'#', '/', $df);
else $show_df=$df;


@list($f_chmod1,$f_chmod2,$f_chmod3)=str_split(permissions($df));
list($n_chmod1,$n_chmod2,$n_chmod3)=str_split($chmod);
//list($m_chmod1,$m_chmod2,$m_chmod3)=str_split($max_chmod);

if ($f_chmod1<$n_chmod1 || $f_chmod2<$n_chmod2 || $f_chmod3<$n_chmod3)
{
$err[]="Установите CHMOD $n_chmod1$n_chmod2$n_chmod3 на $show_df";
echo "<div class='err'>$show_df : [$f_chmod1$f_chmod2$f_chmod3] - >$n_chmod1$n_chmod2$n_chmod3</div>\n";
}
else
{
echo "<div class='menu_razd'>$show_df ($n_chmod1$n_chmod2$n_chmod3) : $f_chmod1$f_chmod2$f_chmod3 (OK)</div>\n";
}
}


if ($install)
{
test_chmod(H.'install/',777);
test_chmod(H.'lib/files/attach/',777);
test_chmod(H.'lib/files/cache/', 777);
test_chmod(H.'lib/files/download/html/',777);
test_chmod(H.'lib/files/download/txt/',777);
test_chmod(H.'lib/files/upload/', 777);
test_chmod(H.'download/files/', 777);
test_chmod(H.'download/about/', 777);
test_chmod(H.'download/screen/', 777);
test_chmod(H.'download/time_files/created_java/files', 777);
test_chmod(H.'download/time_files/created_zip/', 777);
test_chmod(H.'download/time_files/open_zip/', 777);
test_chmod(H.'forum/files/attach/', 777);
}

test_chmod(H.'style/users/avatar/',777);
test_chmod(H.'engine/files/data/',777);
test_chmod(H.'engine/services/', 777);

test_chmod(H.'engine/files/modules/archives/',777);
test_chmod(H.'engine/files/modules/configs/',777);
test_chmod(H.'engine/files/backup/files/',777);
test_chmod(H.'engine/files/backup/mysql/',777);
test_chmod(H.'engine/files/users/cache/',777);

test_chmod(H.'engine/files/tmp/',777);
test_chmod(H.'style/themes/',777);
test_chmod(H.'style/smiles/',777);

if (file_exists(H.'engine/files/data/settings.ini'))test_chmod(H.'engine/files/data/settings.ini',666);
