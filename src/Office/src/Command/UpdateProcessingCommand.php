<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:26
 */

namespace Office\Command;

use ApiV1\Service\Umka\DocOut;
use App\Service\DateTime;
use Doctrine\ORM\EntityManager;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Office\Service\SendMail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Symfony\Component\VarDumper\VarDumper;
use Zend\Json\Json;

/**
 * Class UpdateProcessingCommand
 *
 * @package Office\Command
 */
class UpdateProcessingCommand extends Command
{
    private $logger;

    private $entityManager;

    /**
     * CheckUseKktCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param SendMail $sendMail
     */
    public function __construct(Logger $logger, EntityManager $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('office:update-processing')
            ->setDescription('Обновление параметров чека');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->debug('Старт обработки');

//        $shop = $this->entityManager->getRepository(Shop::class)->findBy(['id' => 123]);

        $countItems = $this->entityManager->getRepository(Processing::class)->count(
            [
                'fnNumber' => null,
                //                'error'     => null,
                //                'shop'     => $shop,
            ]
        );
        $this->logger->debug('Чеков для обработки: '.$countItems);

        $iteration = ceil($countItems / 1000);

        for ($i = 0; $i < $iteration; $i++) {
            $this->logger->debug('Итерация: '.$i.'/'.$iteration);
            /** @var Processing[] $processings */
            $processings = $this->entityManager->getRepository(Processing::class)->findBy(
                [
                    'fnNumber' => null,
                    //                    'error'     => null,
                    //                    'shop'     => $shop,
                ],
                null,
                1000
            );
            foreach ($processings as $processing) {
            }
            $this->logger->debug('Сохранение данных');
            $this->entityManager->flush();
            $this->logger->debug('Пауза');
            sleep(2);
        }
//            $this->deleteDuplicate();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteDuplicate(): void
    {
        /** @var Processing[] $items */
        $items = $this->entityManager->getRepository(Processing::class)->createQueryBuilder('p')
            ->select('p', 'COUNT(p.id) c')
            ->groupBy('p.shop', 'p.externalId')
            ->having('c > 1')
            ->getQuery()
            ->getResult();

        $this->logger->addDebug('Элементов для удаления: '.count($items));

        foreach ($items as $elements) {
            /** @var Processing $item */
            foreach ($elements as $key => $item) {
                if ($key === 'c') {
                    continue;
                }

                $this->logger->addDebug('Поиск по коду: '.$item->getExternalId().' и магазину '.$item->getShop()->getTitle());
                /** @var Processing[] $tikets */
                $tikets = $this->entityManager->getRepository(Processing::class)
                    ->createQueryBuilder('p')
                    ->where('p.externalId = :id and p.shop = :shop')
                    ->setParameter('id', $item->getExternalId())
                    ->setParameter('shop', $item->getShop())
                    ->orderBy('p.datetime', 'desc')
                    ->getQuery()->getResult();
                $this->logger->debug('Найдено: '.count($tikets));
                if (count($tikets) !== (int)$elements['c']) {
                    $this->logger->critical('Не соотвествие найденных элементов');

                    return;
                }
                /** @var Processing $original */
                $original = array_shift($tikets);
                $this->logger->addDebug('Удаление...');
                foreach ($tikets as $tiket) {
                    $this->logger->addDebug('#'.$tiket->getId());
                    if ($tiket->getStatus() === Processing::STATUS_ACCEPT || $tiket->getStatus() === Processing::STATUS_PREPARE) {
                        $this->logger->warning('Чек еще не печатался');

                        if ($original->getRawData() != $tiket->getRawData()) {
                            $this->logger->addInfo('Чеки разные, пропускаем');
                            continue;
                        }
                    }
                    $this->entityManager->remove($tiket);
                }

                $this->entityManager->flush();
                $this->logger->addDebug('Проверка');
                $tikets = $this->entityManager->getRepository(Processing::class)
                    ->createQueryBuilder('p')
                    ->where('p.externalId = :id and p.shop = :shop')
                    ->setParameter('id', $item->getExternalId())
                    ->setParameter('shop', $item->getShop())
                    ->orderBy('p.datetime', 'desc')
                    ->getQuery()->getResult();
                if (count($tikets) === 1) {
                    $this->logger->addDebug('ok');
                } else {
                    $this->logger->critical('НЕСООТВЕСТВИЕ!!! - '.count($tikets));

                    sleep(10);
                }
            }
        }
        $this->entityManager->flush();
    }
}
