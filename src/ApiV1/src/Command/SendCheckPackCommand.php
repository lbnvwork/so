<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:26
 */

namespace ApiV1\Command;

use ApiV1\Service\Check\Normal\Pack;
use ApiV1\Service\Umka;
use Doctrine\ORM\EntityManager;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;

/**
 * Class SendCheckPackCommand
 *
 * @package ApiV1\Command
 */
class SendCheckPackCommand extends Command
{
    private $logger;

    private $entityManager;

    /**
     * @var Pack
     */
    private $packService;

    /**
     * @var Umka\UmkaLkApi
     */
    private $umkaLkApi;

    /**
     * SendCheckPackCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param Pack $packService
     * @param Umka\UmkaLkApi $umkaLkApi
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Pack $packService,
        Umka\UmkaLkApi $umkaLkApi
    ) {
        parent::__construct();

        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->packService = $packService;
        $this->umkaLkApi = $umkaLkApi;
    }

    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('apiv1:send-check-pack')
            ->setDescription('Отправка чеков пачкой')
            ->addArgument(
                'inn',
                InputArgument::OPTIONAL,
                'ИНН клиента'
            )
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Лимит запросов')
            ->addOption('test', 't', InputOption::VALUE_NONE, 'Отправка тестовых чеков')
            ->addOption('shop', 's', InputOption::VALUE_REQUIRED, 'Отправка по конкретному магазину');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inn = $input->getArgument('inn');
        $isTest = (bool)$input->getOption('test');
        $shopId = $input->getOption('shop');

        $store = new FlockStore();
        $factory = new Factory($store);

        $locker = $factory->createLock('lock-send-check-pack-'.$inn.'-'.$isTest.'-'.$shopId, 10800);
        if (!$locker->acquire()) {
            $this->logger->warning('This command is already running in another process.');

            return 0;
        }

        $shop = null;
//        if ($shopId) {
        /** @var Shop $shop */
        $shop = $this->entityManager->getRepository(Shop::class)->findOneBy(
            [
                'id'       => $shopId,
                'isSingle' => true,
                'isPack'   => true,
            ]
        );
        if ($shop) {
            $this->logger->debug('Отправка чеков магазина: '.$shop->getTitle());
        } else {
            $this->logger->critical('Магазин с кодом #'.$shopId.' для индивидуальной отправки не найден');

            return 0;
        }
//        }
        $this->packService->isTest($isTest);

        /** @var Processing[] $tickets */
        $tickets = $this->entityManager
            ->getRepository(Processing::class)
            ->getProcessing(Processing::STATUS_PREPARE, $isTest, $shop, $shop ? $shop->getIsSingle() : false, 500);

        if (!empty($tickets)) {
            $dataForSend = $this->packService->prepareSend($tickets);
            $this->entityManager->flush();
            $this->logger->addDebug('Отправка '.count($tickets).' чеков');
            $resultSend = $this->umkaLkApi->sendPackage($dataForSend);
            $this->prepareResponse($resultSend);
        }

        $tickets = $this->entityManager
            ->getRepository(Processing::class)
            ->getProcessing(Processing::STATUS_ACCEPT, $isTest, $shop, $shop ? $shop->getIsSingle() : false, 500);

        if (!empty($tickets)) {
            $dataForSend = $this->packService->prepareSend($tickets);
            $this->entityManager->flush();
            $this->logger->addDebug('Отправка '.count($tickets).' чеков');
            $resultSend = $this->umkaLkApi->sendPackage($dataForSend);
            $this->prepareResponse($resultSend);
        }

        $this->logger->info('Завершение отправки чеков');
        $locker->release();
    }

    /**
     * Обработка результатов отправки чеков
     *
     * @param array $resultSend
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function prepareResponse(array $resultSend): void
    {
        try {
            $this->umkaLkApi->prepareError($resultSend);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            return;
        }

        $sessionIds = [];
        foreach ($resultSend['results'] as $item) {
            $sessionIds[] = $item['externalId'];
        }
        /** @var Processing[] $tickets */
        $tickets = $this->entityManager->getRepository(Processing::class)
            ->createQueryBuilder('p', 'p.sessionId')
            ->where('p.sessionId IN(:ids)')
            ->setParameter('ids', $sessionIds)
            ->getQuery()->getResult();

        foreach ($resultSend['results'] as $item) {
            $tickets[$item['externalId']]->setStatus($item['status'] === 'OK' ? Processing::STATUS_PRINT_PROCESS : Processing::STATUS_ERROR_PRINT);
            if ($tickets[$item['externalId']]->getStatus() !== Processing::STATUS_PRINT_PROCESS) {
                $tickets[$item['externalId']]->setError($item['status']);
            }
            if ($item['status'] === 'invalid login/password/inn') {
                $tickets[$item['externalId']]->setStatus(Processing::STATUS_PREPARE);
                $this->logger->addCritical('Проверь ИНН!!!');
            }
        }
        $this->entityManager->flush();
    }
}
