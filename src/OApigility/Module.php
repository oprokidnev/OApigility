<?php

namespace OApigility;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use WmMain\Module\ComponentsProviderInterface;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use OApigility\Hydrator\JmsSerializerHydrator;

class Module
{
    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return require dirname(dirname(__DIR__ )). '/config/module.config.php';
    }
    /**
     * 
     * @return string
     */
    public function getAlias()
    {
        return __NAMESPACE__;
    }

    /**
     * 
     * @return string
     */
    public function getDir()
    {
        return __DIR__;
    }

    /**
     * 
     * @return string
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * 
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * 
     * @return null
     */
    public function onBootstrap(MvcEvent $event)
    {
        $serizlierManager = \Zend\Serializer\Serializer::getAdapterPluginManager();
        $serizlierConfig  = $event->getApplication()->getServiceManager()->get('Config')['o-apigility'];


        if (isset($_SERVER['REQUEST_URI'])) {
            $eventManager = $event->getApplication()->getEventManager();
            $strategy     = new \BjyAuthorize\View\RedirectionStrategy();
            $strategy->setRedirectUri('/user/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            $eventManager->attach($strategy);
        }
    }

    /**
     * 
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'zfcuser_redirect_callback' => function ($sm) {
                    /* @var RouteInterface $router */
                    $router = $sm->get('Router');

                    /* @var Application $application */
                    $application = $sm->get('Application');

                    /* @var ModuleOptions $options */
                    $options = $sm->get('zfcuser_module_options');

                    return new Controller\RedirectCallback($application,
                        $router, $options);
                },
            ),
        );
    }

}
