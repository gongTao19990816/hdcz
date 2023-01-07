<?php

return [
    'alias' => [
        'JwtAuth' => app\task\middleware\JwtAuth::class,
        'SmsAuth' => app\task\middleware\SmsAuth::class,
        'CaptchaAuth' => app\task\middleware\CaptchaAuth::class,
    ],
];
