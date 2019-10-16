<?php
declare(strict_types=1);

namespace ApiV1\Service\Check\Normal;

use App\Entity\Setting;
use Cms\Service\SettingService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class PackFactory
 *
 * @package ApiV1\Service\Check\Normal
 */
class PackFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return Pack|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var EntityManager $em */
        $em = $container->get(EntityManager::class);
        /** @var Setting[] $settings */
        $settings = $em->getRepository(Setting::class)->createQueryBuilder('s', 's.param')->getQuery()->getResult();

        $config = [
//            'host' => isset($settings[SettingService::KKT_HOST]) ? $settings[SettingService::KKT_HOST]->getValue() : 'test',
            'login' => isset($settings[SettingService::CASHIER_LOGIN]) ? $settings[SettingService::CASHIER_LOGIN]->getValue() : 'test',
            'password' => isset($settings[SettingService::CASHIER_PASSWORD]) ? $settings[SettingService::CASHIER_PASSWORD]->getValue() : 'test',
        ];

        return new Pack($config);
    }
}
