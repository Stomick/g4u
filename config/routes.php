<?php

return [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
        'suffix' => '',
        'rules' => [
	        'admin/<controller:\w+>/<action:\w+>/<id:\d+>' => 'admin/<controller>/<action>',
            'admin/<controller:\w+>/<action:\w+>' => 'admin/<controller>/<action>',
            '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
        ]
];
