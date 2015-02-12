<?php

return [
    'id' => 'Yii2 Taxonomy Test',
    'basePath' => dirname(__DIR__),
    'components' => array(
        'db' => array(
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:taxonomy.sqlite',
//            'username' => 'root',
//            'password' => '',
            'charset' => 'utf8',
        ),
        'taxonomy' => [
            'class' => 'nkostadinov\taxonomy\Taxonomy',
        ]
    ),
    'modules' => [
        'taxonomy' => [ 'class' => 'nkostadinov\taxonomy\Module' ]
    ],
];