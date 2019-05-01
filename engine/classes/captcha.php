<?php

class SimpleCaptcha {

    
    public $width  = 150;
    public $height = 60;
    public $minWordLength = 3;
    public $maxWordLength = 6;
    public $session_var = 'captcha';
    public $backgroundColor = array(255, 255, 255);
    public $colors = array(
        array(27,78,181),         array(22,163,35),         array(214,36,7),      );

    public $shadowColor = null; 
    public $lineWidth = 0;
    public $Yperiod    = 12;
    public $Yamplitude = 14;
    public $Xperiod    = 11;
    public $Xamplitude = 5;
    public $maxRotation = 7;
    public $scale = 2;
    public $blur = false;
    public $debug = false;
    public $im;
	public $font_config = array('spacing' => 1, 'minSize' => 17, 'maxSize' => 19);

    public function __construct($config = array()) {
    }

    public function CreateImage() {
        $ini = microtime(true);

        
        $this->ImageAllocate();
        
        
        $text = $this->GetRandomCaptchaText();
        $this->WriteText($text);

        $_SESSION[$this->session_var] = $text;

        
        if (!empty($this->lineWidth)) {
            $this->WriteLine();
        }
        $this->WaveImage();
        if ($this->blur && function_exists('imagefilter')) {
            imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $this->ReduceImage();


        if ($this->debug) {
            imagestring($this->im, 1, 1, $this->height-8,
                "$text ".round((microtime(true)-$ini), 4)."ms",
                $this->GdFgColor
            );
        }


        
        $this->WriteImage();
    }


    
    protected function ImageAllocate() {
                if (!empty($this->im)) {
            imagedestroy($this->im);
        }

        $this->im = imagecreatetruecolor($this->width*$this->scale, $this->height*$this->scale);

                $this->GdBgColor = imagecolorallocate($this->im,
            $this->backgroundColor[0],
            $this->backgroundColor[1],
            $this->backgroundColor[2]
        );
		//imagecolortransparent($this->im, $this->GdBgColor);
        imagefilledrectangle($this->im, 0, 0, $this->width*$this->scale, $this->height*$this->scale, $this->GdBgColor);

                $color           = $this->colors[mt_rand(0, sizeof($this->colors)-1)];
        $this->GdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);

                if (!empty($this->shadowColor) && is_array($this->shadowColor) && sizeof($this->shadowColor) >= 3) {
            $this->GdShadowColor = imagecolorallocate($this->im,
                $this->shadowColor[0],
                $this->shadowColor[1],
                $this->shadowColor[2]
            );
        }
    }

    
    protected function GetRandomCaptchaText($length = null) {
        if (empty($length)) {
            $length = rand($this->minWordLength, $this->maxWordLength);
        }

        $words  = 'bcdfghjlmnpqrstvwyz';
        $vocals = 'aeoui';

        $text  = null;
        $vocal = rand(0, 1);
        while (strlen($text) < $length)
		{
            if ($vocal) {
                $text .= substr($vocals, mt_rand(0, 4), 1); //$vocals{mt_rand(0, 4)}; //
				$vocal = false;
            } else {
                $text .= substr($words, mt_rand(0, 22), 1); //$words{mt_rand(0, 22)};
				$vocal = true;
            }
        }
        return $text;
    }


    
    protected function WriteLine() {

        $x1 = $this->width*$this->scale*.15;
        $x2 = $this->textFinalX;
        $y1 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
        $y2 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
        $width = $this->lineWidth/2*$this->scale;

        for ($i = $width*-1; $i <= $width; $i++) {
            imageline($this->im, $x1, $y1+$i, $x2, $y2+$i, $this->GdFgColor);
        }
    }




    
    protected function WriteText($text) {

        $fontfile = $_SERVER['DOCUMENT_ROOT'] . '/engine/files/data/font.ttf';
        $lettersMissing = $this->maxWordLength-strlen($text);
        $fontSizefactor = 1+($lettersMissing*0.09);

                $x      = 20*$this->scale;
        $y      = round(($this->height*27/40)*$this->scale);
        $length = strlen($text);
        for ($i=0; $i<$length; $i++) {
            $degree   = rand($this->maxRotation*-1, $this->maxRotation);
            $fontsize = rand($this->font_config['minSize'], $this->font_config['maxSize'])*$this->scale*$fontSizefactor;
            $letter   = substr($text, $i, 1);

            if ($this->shadowColor) {
                $coords = imagettftext($this->im, $fontsize, $degree,
                    $x+$this->scale, $y+$this->scale,
                    $this->GdShadowColor, $fontfile, $letter);
            }
            $coords = imagettftext($this->im, $fontsize, $degree,
                $x, $y,
                $this->GdFgColor, $fontfile, $letter);
            $x += ($coords[2]-$x) + ($this->font_config['spacing'] * $this->scale);
        }

        $this->textFinalX = $x;
    }

    
    protected function WaveImage() {
                $xp = $this->scale*$this->Xperiod*rand(1,3);
        $k = rand(0, 100);
        for ($i = 0; $i < ($this->width*$this->scale); $i++) {
            imagecopy($this->im, $this->im,
                $i-1, sin($k+$i/$xp) * ($this->scale*$this->Xamplitude),
                $i, 0, 1, $this->height*$this->scale);
        }

                $k = rand(0, 100);
        $yp = $this->scale*$this->Yperiod*rand(1,2);
        for ($i = 0; $i < ($this->height*$this->scale); $i++) {
            imagecopy($this->im, $this->im,
                sin($k+$i/$yp) * ($this->scale*$this->Yamplitude), $i-1,
                0, $i, $this->width*$this->scale, 1);
        }
    }

    protected function ReduceImage() {
                $imResampled = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($imResampled, $this->im,
            0, 0, 0, 0,
            $this->width, $this->height,
            $this->width*$this->scale, $this->height*$this->scale
        );
        imagedestroy($this->im);
        $this->im = $imResampled;
    }


    protected function WriteImage() {
        header("Content-type: image/png");
        imagepng($this->im);
		imagedestroy($this->im);
    }
}