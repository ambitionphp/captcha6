<?php

if ((float) app()->version() >= 5.2) {
    Route::get('captcha', '\AmbitionPHP\Captcha\CaptchaController@getCaptcha')->middleware('web');
} else {
    Route::get('captcha', '\AmbitionPHP\Captcha\CaptchaController@getCaptcha');
}
