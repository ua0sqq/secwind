<?php
/**
 * Script para la generaci�n de CAPTCHAS
 *
 * @author  Jose Rodriguez <jose.rodriguez@exec.cl>
 * @license GPLv3
 * @link    http://code.google.com/p/cool-php-captcha
 * @package captcha
 * @version 0.3
 *
 */
 
session_name('Secwind');
session_start();

include '../engine/classes/captcha.php';

$captcha = new SimpleCaptcha();
$captcha->CreateImage();

