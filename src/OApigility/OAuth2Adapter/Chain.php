<?php

namespace WmUserApi\OAuth2;

/**
 * Description of Adapter
 *
 * @author oprokidnev
 */
class Adapter extends \ZF\OAuth2\Adapter\PdoAdapter
{

    /**
     * @var int
     */
    protected $bcryptCost = 14;
    protected $config = [];
    
    public function getUser($username)
    {
        if ($standartResult = parent::getUser($username)) {
            return $standartResult;
        } else {
            $em    = $this->getEntityManager();
            $users = $em->getRepository('WmUser\Entity\User')->findBy([
                'email'    => $username,
            ]);
            foreach ($users as $user) {
                return $this->parseUser($user);
            }
            $users = $em->getRepository('WmUser\Entity\User')->findBy([
                'username'    => $username,
            ]);
            foreach ($users as $user) {
                return $this->parseUser($user);
            }
        }
    }

    /* OAuth2\Storage\UserCredentialsInterface */

    public function checkUserCredentials($username, $password)
    {
        if ($user = $this->getUser($username)) {
            return $this->checkPassword($user, $password);
        }

        return false;
    }

    /**
     *
     * @var \Zend\Authentication\AuthenticationService
     */
    protected $authService = null;

    public function __construct($connection, $config = array(), $config, $entityManager, $authService)
    {
        $this->config        = $config;
        $this->entityManager = $entityManager;
        $this->authService   = $authService;
        return parent::__construct($connection, $config);
    }

    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager = null;

    /* OAuth2\Storage\ClientCredentialsInterface */

    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $result = null;
        $config = $this->config;
        if (isset($config['clients'])) {
            foreach ($config['clients'] as $client) {
                if (@$client['client_id'] == $client_id) {
                    $result = $client;
                }
            }
        }
        if ($result === null) {

            $stmt   = $this->db->prepare(sprintf('SELECT * from %s where client_id = :client_id',
                    $this->config['client_table']));
            $stmt->execute(compact('client_id'));
            $result = $stmt->fetch(\PDO::FETCH_BOTH);
        }
        // make this extensible
        return $result && $result['client_secret'] == $client_secret;
    }

    public function isPublicClient($client_id)
    {
        $config = $this->config;
        if (isset($config['clients'])) {
            foreach ($config['clients'] as $client) {
                if (@$client['client_id'] == $client_id) {
                    $result = $client;
                    return empty($result['client_secret']);
                }
            }
        }

        $stmt = $this->db->prepare(sprintf('SELECT * from %s where client_id = :client_id',
                $this->config['client_table']));
        $stmt->execute(compact('client_id'));

        if (!$result = $stmt->fetch(\PDO::FETCH_BOTH)) {
            return false;
        }

        return empty($result['client_secret']);
    }

    protected final function parseUser(\WmUser\Entity\User $user)
    {
        return [
            'username'   => $user->getUsername(),
            'password'   => $user->getPassword(),
            'first_name' => $user->getProfile()->getName(),
            'last_name'  => $user->getProfile()->getSurname(),
            'user_id'    => $user->getId(),
            'user'    => $user,
        ];
    }

    /* OAuth2\Storage\ClientInterface */

    public function getClientDetails($client_id)
    {

        $result = null;
        $config = $this->config;
        if (isset($config['clients'])) {
            foreach ($config['clients'] as $client) {
                if (@$client['client_id'] == $client_id) {
                    return $client;
                }
            }
        }
        if ($result === null) {
            $stmt = $this->db->prepare(sprintf('SELECT * from %s where client_id = :client_id',
                    $this->config['client_table']));
            $stmt->execute(compact('client_id'));

            return $stmt->fetch(\PDO::FETCH_BOTH);
        }
    }

    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null)
    {
        $entityManager = $this->getEntityManager();
        $authService   = $this->getAuthService();
        // convert expires to datestring
        $expires       = date('Y-m-d H:i:s', $expires);
        $user = $user_id !== null ? $entityManager->getRepository('WmUser\\Entity\\User')->find($user_id) : $authService->getIdentity();

        // if it exists, update it.
        if ($this->getAccessToken($access_token)) {
            $stmt = $this->db->prepare(sprintf('UPDATE %s SET client_id=:client_id, expires=:expires, user_id=:user_id, scope=:scope where access_token=:access_token',
                    $this->config['access_token_table']));
        } else {
            $accessToken = new \WmApigility\Entity\OAuth\AccessToken();
            $accessToken->setAccessToken($access_token)
                ->setClientId($client_id)
                ->setUser($user)
                ->setExpires($expires)
                ->setScope($scope);
            $entityManager->persist($accessToken);
            return $entityManager->flush($accessToken);
//            $stmt = $this->db->prepare(sprintf('INSERT INTO %s (access_token, client_id, expires, user_id, scope) VALUES (:access_token, :client_id, :expires, :user_id, :scope)', $this->config['access_token_table']));
        }

        return $stmt->execute(compact('access_token', 'client_id', 'user_id',
                    'expires', 'scope'));
    }
    
    public function setClientDetails($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $scope = null, $user_id = null)
    {
        // if it exists, update it.
        if ($this->getClientDetails($client_id)) {
            $stmt = $this->db->prepare($sql  = sprintf('UPDATE %s SET client_secret=:client_secret, redirect_uri=:redirect_uri, grant_types=:grant_types, scope=:scope, user_id=:user_id where client_id=:client_id',
                $this->config['client_table']));
        } else {
            $stmt = $this->db->prepare(sprintf('INSERT INTO %s (client_id, client_secret, redirect_uri, grant_types, scope, user_id) VALUES (:client_id, :client_secret, :redirect_uri, :grant_types, :scope, :user_id)',
                    $this->config['client_table']));
        }

        return $stmt->execute(compact('client_id', 'client_secret',
                    'redirect_uri', 'grant_types', 'scope', 'user_id'));
    }

    /**
     * 
     * @return \Doctrine\ORM\EntityRepository
     */
    function getUserRepository()
    {
        return $this->userRepository;
    }

    /**
     * 
     * @param \Doctrine\ORM\EntityRepository $userRepository
     * @return \WmUser\OAuth2\Adapter
     */
    function setUserRepository(\Doctrine\ORM\EntityRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthService()
    {
        return $this->authService;
    }

    /**
     * 
     * @param array $config
     * @return \WmUser\OAuth2\Adapter
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * 
     * @param \Zend\Authentication\AuthenticationService $authService
     * @return \WmUser\OAuth2\Adapter
     */
    public function setAuthService(\Zend\Authentication\AuthenticationService $authService)
    {
        $this->authService = $authService;
        return $this;
    }

    /**
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * 
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @return \WmUser\OAuth2\Adapter
     */
    public function setEntityManager(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }

}