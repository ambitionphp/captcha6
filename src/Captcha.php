<?php

namespace AmbitionPHP\Captcha;
use Illuminate\Support\Str;

/**
 * Class Captcha.
 *
 * @author hashem Moghaddari <hashemm364@gmail.com>
 */
class Captcha
{
    /**
     * Image resource.
     *
     * @var resource
     */
    protected $img;

    /**
     * Image width.
     *
     * @var int
     */
    protected $width;

    /**
     * Image height.
     *
     * @var int
     */
    protected $height;

    /**
     * Image Background color.
     *
     * @var string
     */
    protected $backColor;

    /**
     * Image Font color.
     *
     * @var string
     */
    protected $fontColor;

    /**
     * Image font file path.
     *
     * @var string
     */
    protected $font;

    /**
     * Image font size.
     *
     * @var int
     */
    protected $size;

    /**
     * Length of captcha code.
     *
     * @var int
     */
    protected $length;

    /**
     * Set attributes values.
     *
     * @return void
     */
    protected function configure()
    {
        if (config()->has('scaptcha')) {
            foreach (config('scaptcha') as $key => $value) {
                $key = Str::camel($key);
                $this->{$key} = $value;
            }
        }
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $this->backColor = imagecolorallocate($this->img, $this->hex2rgb($this->backColor)[0], $this->hex2rgb($this->backColor)[1], $this->hex2rgb($this->backColor)[2]); //white
        $this->fontColor = imagecolorallocate($this->img, $this->hex2rgb($this->fontColor)[0], $this->hex2rgb($this->fontColor)[1], $this->hex2rgb($this->fontColor)[2]);
        $this->font = __DIR__.'/../assets/font/arial.ttf';
    }

    /**
     * Generate random text.
     *
     * @return string
     */
    protected function randomText()
    {
        $alphaNumeric = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        $text = '';
        for ($i = 0; $i < $this->length; $i++) {
            $text .= $alphaNumeric[rand(0, count($alphaNumeric) - 1)];
        }
        //put captcha code into session
        session(['captcha' => $text]);

        return $text;
    }

    /**
     * Convert Hexadecimal to RGB font color.
     *
     * @return array
     */
    protected function hex2rgb($color)
    {
        $hex = str_replace('#', '', $color);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = [$r, $g, $b];
        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }

    /**
     * Create captcha image.
     */
    public function create()
    {
        $this->configure();

        $text = $this->randomText();

        $text_box = imagettfbbox($this->size, -5,$this->font, $text);
        $text_width = $text_box[2]-$text_box[0];
        $text_height = $text_box[7]-$text_box[1];

        $x = ($this->width/2) - ($text_width/2);
        $y = ($this->height/2) - ($text_height/2);

        imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $this->backColor);
        imagettftext($this->img, $this->size, -5, $x, $y, $this->fontColor, $this->font, $text);
        header('Content-type: image/png');
        imagepng($this->img);
    }

    /**
     * Generate captcha image html tag.
     *
     * @return string img HTML Tag
     */
    public function img()
    {
        return '<img src="'.$this->src().'" alt="captcha">';
    }

    /**
     * Check user input captcha code.
     *
     * @param string $input
     *
     * @return bool
     */
    public function check($input)
    {
        if (!session()->has('captcha')) {
            return false;
        }

        $code = session()->pull('captcha');

        if (config('scaptcha.sensitive')) {
            if ($input == $code) {
                return true;
            } else {
                return false;
            }
        } else {
            if (strtolower($input) == strtolower($code)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Generate captcha image source.
     *
     * @return string
     */
    public function src()
    {
        return url('captcha').'?'.Str::random(8);
    }
}
