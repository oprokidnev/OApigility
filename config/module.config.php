<?php

return array(
    'doctrine' => array(
        'driver' => array(
            'wm_apigility_annotation_driver' => array(
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\AnnotationDriver',
                'paths' => array(
                    0 => __DIR__ . '/../src/OApigility/Entity',
                ),
            ),
            'orm_default' => array(
                'drivers' => array(
                    'OApigility\\Entity' => 'wm_apigility_annotation_driver',
                ),
            ),
        ),
    ),
    'hydrators' => [
        'factories' => [
            'OApigility\\Hydrator\\DoctrineObject' => 'OApigility\\Hydrator\\DoctrineObjectHydratorFactory',
        ],
        'aliases' => [
            'ApigilityDoctrineHydrator' => 'OApigility\Hydrator\DoctrineObject',
        ],
    ],
    'zf-hal' => array(
        'renderer' => array(
            'default_hydrator' => 'ApigilityDoctrineHydrator',
            'hydrators' => array(),
        ),
    ),
    'zfcuser' => [
        'use_redirect_parameter_if_present' => true,
    ],
    'view_manager' => array(
        'template_map' => array(
            'oauth/authorize' => __DIR__ . '/../view/zf/auth/authorize.phtml',
            'oauth/receive-code' => __DIR__ . '/../view/zf/auth/receive-code.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'zf-oauth2' => array(
        'allow_implicit' => true, // default (set to true when you need to support browser-based or mobile apps)
        'access_lifetime' => 36000, // default (set a value in seconds for access tokens lifetime)
        'enforce_state' => true, // default
    ),
    'bjyauthorize' => array(
        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
                array('controller' => 'ZF\Apigility\Admin\Controller\Source', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\Authentication', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\Authorization', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\ContentNegotiation', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\DbAdapter', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\RpcService', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\RestService', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\Module', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\HttpBasicAuthentication', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\HttpDigestAuthentication', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\OAuth2Authentication', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\App', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\CacheEnabled', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\FsPermissions', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\Dashboard', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\Documentation', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\Filters', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\Hydrators', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\InputFilter', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\SettingsDashboard', 'roles' => array()),
                array('controller' => 'ZF\Apigility\Admin\Controller\Validators', 'roles' => array()),
//                array('controller' => 'ZF\OAuth2\Controller\Auth', 'roles' => array('user')),
                array('controller' => 'ZF\OAuth2\Controller\Auth', 'action' => 'authorize', 'roles' => array('user')),
                array('controller' => 'ZF\OAuth2\Controller\Auth', 'action' => 'token', 'roles' => array()),
            ),
        ),
    ),
    'service_manager' => [
        'invokables' => [
            \OApigility\Hydrator\Formatter\GroupFormatter::class => \OApigility\Hydrator\Formatter\GroupFormatter::class,
//            \OApigility\Controller\RedirectCallback::class=> \OApigility\Controller\RedirectCallback::class,
        ],
        'aliases'=>[
//            'zfcuser_redirect_callback'=>\OApigility\Controller\RedirectCallback::class
        ],
        'shared' => [
//\OApigility\Hydrator\Formatter\EmbeddedFormatter::class => true,
        ],
    ],
    'wm-apigility' => [
        'apigility_doctrine_hydrator' => [
            'naming_strategy' => \OApigility\Hydrator\Strategy\JsonNamingStrategy::class,
            'formatters' => [
                \OApigility\Hydrator\Formatter\CircularFormatter::class,
                \OApigility\Hydrator\Formatter\DateTimeFormatter::class,
                \OApigility\Hydrator\Formatter\GroupFormatter::class,
                \OApigility\Hydrator\Formatter\EmbeddedFormatter::class,
            ],
            'filters' => [
                \OApigility\Hydrator\Filter\AnnotationFilter::class,
//                \OApigility\Hydrator\Filter\MetadataFilter::class,
            ],
        ],
    ],
    'router' => array(
        'routes' => array(
            'oauth' => array(
                'options' => array(
                    'route' => '/oauth',
                ),
            ),
        ),
    ),
);
