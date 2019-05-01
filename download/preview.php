<?php

if (!isset($_GET['img']))
    exit;
function format($name)
{
    $f1 = strrpos($name, ".");
    $f2 = substr($name, $f1 + 1, 999);
    $fname = strtolower($f2);
    return $fname;
}
$type = isset($_GET['type']) ? abs(intval($_GET['type'])) : 0;
$image = isset($_GET['img']) ? htmlspecialchars(urldecode($_GET['img'])) : null;
if ($image && file_exists($image)) {
    $att_ext = strtolower(format($image));
    $pic_ext = array('gif', 'jpg', 'jpeg', 'png');
    if (in_array($att_ext, $pic_ext)) {
        $info_file = GetImageSize($image);
        $w_or = $info_file[0];
        $h_or = $info_file[1];
        $type_file = $info_file['mime'];
        switch ($type) {
            case 1:
                $w = 40;
                $h = 40;
                break;
            default:
                if ($w_or > 240) {
                    $w = 240;
                    $h = ceil($h_or / ($w_or / 240));
                }
                else {
                    $w = $w_or;
                    $h = $h_or;
                }
        }
        switch ($att_ext) {
            case "gif":
                $image_file = ImageCreateFromGIF($image);
                break;
            case "jpg":
                $image_file = ImageCreateFromJPEG($image);
                break;
            case "jpeg":
                $image_file = ImageCreateFromJPEG($image);
                break;
            case "png":
                $image_file = ImageCreateFromPNG($image);
                break;
        }
        $two_image = imagecreatetruecolor($w, $h);
        imagecopyresampled($two_image, $image_file, 0, 0, 0, 0, $w, $h, $w_or, $h_or);
        ob_start();
        imageJpeg($two_image, null, 60);
        ImageDestroy($image_file);
        imagedestroy($two_image);
        header("Content-Type: image/jpeg");
        header('Content-Disposition: inline; filename=preview.jpg');
        header('Content-Length: ' . ob_get_length());
        ob_end_flush();
    }
}

?>