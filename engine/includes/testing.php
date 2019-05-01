<?

echo "Версия SecWind: $set[version] $set[status] <br />\n";



list ($php_ver1,$php_ver2,$php_ver3)=explode('.', strtok(strtok(phpversion(),'-'),' '), 3);

if ($php_ver1==5)
{
echo "<span class='on'>Версия PHP: $php_ver1.$php_ver2.$php_ver3 (OK)</span><br />\n";
}
else
{
echo "<span class='off'>Версия PHP: $php_ver1.$php_ver2.$php_ver3</span><br />\n";
$err[]="Тестирование на версии php $php_ver1.$php_ver2.$php_ver3 не осуществялось";
}


if (function_exists('imagecreatefromstring') && function_exists('gd_info'))
{
$gdinfo=gd_info();
echo "<span class='on'>GD: ".$gdinfo['GD Version']." OK</span><br />\n";
}
else
{
echo "<span class='off'>GD: Нет</span><br />\n";
$err[]='GD необходима для корректной работы движка';
}


if (function_exists('mysqli_connect'))
{
echo "<span class='on'>MySQL: OK</span><br />\n";
}
else
{
echo "<span class='off'>MySQL: Нет</span><br />\n";
$err[]='Без MySQL работа не возможна';
}
