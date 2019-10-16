<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 14:29
 */

namespace Auth\UserRepository;

use Auth\Service\SendMail;
use Interop\Container\ContainerInterface;

/**
 * Class DatabaseFactory
 *
 * @package Auth\UserRepository
 */
class DatabaseFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return Database
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        return new Database($container->get('doctrine.entity_manager.orm_default'), $container->get(SendMail::class));
    }
}
