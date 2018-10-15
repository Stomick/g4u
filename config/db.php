<?php

return [
    //local
/*    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=aflbase',
    'username' => 'root',
    'password' => '23232323',
    'charset' => 'utf8',
*/
    //server

    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=g4ubase',
    'username' => 'root',
    'password' => 'qwerty123',
    'charset' => 'utf8',
    'on afterOpen' => function($event) {
        $event->sender->createCommand("SET time_zone = '+02:00'")->execute();
    }
    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
