<?php

namespace OApigility\OAuth2Adapter;

/**
 * Description of Adapter
 *
 * @author oprokidnev
 */
class AdapterFactory implements \Zend\ServiceManager\FactoryInterface
{

    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $config        = $serviceLocator->get('Config');
        $entityManager = $serviceLocator->get('Doctrine\ORM\EntityManager');
        /* @var $entityManager \Doctrine\ORM\EntityManager */
        $connection    = $entityManager->getConnection();

        switch (get_class($connection->getDriver())) {
            case "Doctrine\DBAL\Driver\PDOMySql\Driver":
                $platform = 'mysql';
                break;
            default :
                $platform = 'mysql';
                break;
        }

        $dbname = $connection->getDatabase();
        $port   = $connection->getPort();
        $host   = $connection->getHost();

        $dsn = sprintf('%s:host=%s;port=%s;dbname=%s', $platform, $host, $port, $dbname);

        $oauth2ServerConfig = array();
        if (isset($config['zf-oauth2']['storage_settings']) && is_array($config['zf-oauth2']['storage_settings'])) {
            $oauth2ServerConfig = $config['zf-oauth2']['storage_settings'];
        }
        $username = $connection->getUsername();
        $password = $connection->getPassword();
        /**
         * Strange: driver options are not visible in connection
         */
        $options = isset($config['doctrine']['connection']['orm_default']['params']['driverOptions']) ? $config['doctrine']['connection']['orm_default']['params']['driverOptions'] : [];

        $config        = $serviceLocator->get('Config');
        $adapterConfig = isset($config['wm-user']['oauth2']) ? $config['wm-user']['oauth2'] : [];
        $authService      = $serviceLocator->get('zfcuser_auth_service');
        $adapter       = new Adapter(array(
            'dsn' => $dsn,
            'username' => $username,
            'password' => $password,
            'options' => $options,
            ), $oauth2ServerConfig, $adapterConfig, $entityManager,$authService);

        return $adapter;
    }

}