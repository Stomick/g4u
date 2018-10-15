<?php

return [
    'class' => 'yii\swiftmailer\Mailer',
    //'viewPath' => '@/mail',
    'useFileTransport' => false,
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'smtp.gmail.com',
        'username' => 'no-reply@mygame4u.com',
        'password' => 'whtmigxruuwzetad',
        'port' => '587',
        'encryption' => 'TLS',
    ],
];