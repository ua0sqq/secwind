<?php

$theme = $res_down['dir'] . '/' . $res_down['name'];
$file_name = $file_id . '.gif';
$name = 'screen/' . $file_name;
if ($format_file == 'nth')
{
    include H.'sys/inc/zip.php';
    $nth = &new PclZip($theme);
    $content = $nth->extract(PCLZIP_OPT_BY_NAME, 'theme_descriptor.xml', PCLZIP_OPT_EXTRACT_AS_STRING);
    if (!$content)
    {
        $content = $nth->extract(PCLZIP_OPT_BY_PREG, '\.xml$', PCLZIP_OPT_EXTRACT_AS_STRING);
    }
    $teg = simplexml_load_string($content[0]['content'])->wallpaper['src'] or $teg = simplexml_load_string($content[0]['content'])->wallpaper['main_display_graphics'];
    $image = $nth->extract(PCLZIP_OPT_BY_NAME, trim($teg), PCLZIP_OPT_EXTRACT_AS_STRING);
    $im = array_reverse(explode('.', $teg));
    $im = 'imageCreateFrom' . str_ireplace('jpg', 'jpeg', trim($im[0]));
    $upload = file_put_contents($name, $image[0]['content']);
} elseif ($format_file == 'thm')
{
    include 'class_tar.php';
    $thm = &new Archive_Tar($theme);
    if (!$file = $thm->extractInString('Theme.xml') or !$file = $thm->extractInString(pathinfo($theme, PATHINFO_FILENAME) . '.xml'))
    {
        $list = $thm->listContent();
        $all = sizeof($list);
        for ($i = 0; $i < $all; ++$i)
        {
            if (pathinfo($list[$i]['filename'], PATHINFO_EXTENSION) == 'xml')
            {
                $file = $thm->extractInString($list[$i]['filename']);
                break;
            }
        }

    }
    if (!$file)
    {
        preg_match('/<\?\s*xml\s*version\s*=\s*"1\.0"\s*\?>(.*)<\/.+>/isU', file_get_contents($theme), $arr);
        $file = trim($arr[0]);
    }
    $load = trim((string )simplexml_load_string($file)->Standby_image['Source']);
    if (strtolower(strrchr($load, '.')) == '.swf')
        $load = '';
    if (!$load)
        $load = trim((string )simplexml_load_string($file)->Desktop_image['Source']);
    if (strtolower(strrchr($load, '.')) == '.swf')
        $load = '';
    if (!$load)
        $load = trim((string )simplexml_load_string($file)->Desktop_image['Source']);
    if (strtolower(strrchr($load, '.')) == '.swf')
        $load = '';
    if (!$load)
    {
        include H.'engine/includes/foot.php';
        exit;
    }
    $image = $thm->extractInString($load);
    $im = array_reverse(explode('.', $load));
    $im = 'imageCreateFrom' . str_ireplace('jpg', 'jpeg', trim($im[0]));
    $upload = file_put_contents($name, $image);
}

?>