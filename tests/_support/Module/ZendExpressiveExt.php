<?php
declare(strict_types=1);

namespace Codeception\Module;

use Codeception\Lib\Connector\ZendExpressive as ZendExpressiveConnector;
use Codeception\Stub;
use Codeception\TestInterface;
use Zend\Expressive\Session\Ext\PhpSessionPersistence;
use Zend\Expressive\Session\Session;

class ZendExpressiveExt extends ZendExpressive
{
    public function _initialize()
    {
        $this->client = new ZendExpressiveConnector();
        $this->client->setConfig($this->config);

        $this->application = $this->client->initApplication();
        $this->container = $this->client->getContainer();
    }

    /**
     * Получение сервиса из контейнера
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getService(string $serviceName)
    {
//        return $this->client->getContainer()->get($serviceName);
        return $this->container->get($serviceName);
    }

    public function _before(TestInterface $test)
    {
        parent::_before($test); // TODO: Change the autogenerated stub
        $this->setService(
            PhpSessionPersistence::class,
            Stub::make(PhpSessionPersistence::class, ['initializeSessionFromRequest' => new Session([])])
        );
    }

    public function setService($name, $obj)
    {
        $this->container->setAllowOverride(true);
        $this->container->setService($name, $obj);
        $this->container->setAllowOverride(false);
    }

    /**
     * @return mixed
     */
    public function getEntityManager()
    {
        return $this->_getEntityManager();
    }

    public function followRedirects($fllow)
    {
        $this->client->followRedirects($fllow);
    }
}