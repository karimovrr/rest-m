<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'rest-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => 'rest\versions\v1\RestModule'
        ],
        'v2' => [
            'class' => 'rest\versions\v2\RestModule'
        ],
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableSession' => false,
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'request' => [
            'class' => '\yii\web\Request',
            'enableCookieValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
		    ['class' => 'yii\rest\UrlRule', 'controller' => [
		        'v1/post',
                'v1/comment',
                'v2/post',
                'v1/navigation-d/matrix',
                'v1/navigation-d/matrix-direction',
                'v1/navigation-d/matrix-direction-multi',
                'v1/navigation-d/test',
            ]],
                'OPTIONS v1/user/login' => 'v1/user/login',
                'POST v1/user/login' => 'v1/user/login',
                'POST v2/user/login' => 'v2/user/login',
                'OPTIONS v2/user/login' => 'v2/user/login',
                'OPTIONS v1/navigation-d/matrix' => 'v1/navigation-d/matrix',
                'POST v1/navigation-d/matrix' => 'v1/navigation-d/matrix',
                'OPTIONS v1/navigation-d/matrix-direction' => 'v1/navigation-d/matrix-direction',
                'POST v1/navigation-d/matrix-direction' => 'v1/navigation-d/matrix-direction',
                'OPTIONS v1/navigation-d/matrix-direction-multi' => 'v1/navigation-d/matrix-direction-multi',
                'POST v1/navigation-d/matrix-direction-multi' => 'v1/navigation-d/matrix-direction-multi',
                'OPTIONS v1/navigation-d/test' => 'v1/navigation-d/test',
                'POST v1/navigation-d/test' => 'v1/navigation-d/test'
            ],

        ],
    ],
    'params' => $params,
];
