<?php

$vote = abs(intval($_GET['img']));
if ($vote > 100)
    $vote = 100;
header("Content-type: image/gif");
$vote_img = imageCreateFromGIF("images/vote.gif");
$color = imagecolorallocate($vote_img, 0, 255, 0);
$color2 = imagecolorallocate($vote_img, 255, 153, 153);
$color3 = imagecolorallocate($vote_img, 255, 102, 102);
$color4 = imagecolorallocate($vote_img, 255, 51, 51);
$color5 = imagecolorallocate($vote_img, 255, 102, 102);
$color6 = imagecolorallocate($vote_img, 0, 0, 0);
imagefilledrectangle($vote_img, 0, 0, $vote, 5, $color);
ImageGIF($vote_img);