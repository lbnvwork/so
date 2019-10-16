<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:26
 */

namespace ApiV1\Command;

use ApiV1\Service\Check\Correction;
use ApiV1\Service\Check\Normal;
use ApiV1\Service\Umka\UmkaApi;
use ApiV1\Service\Umka;
use App\Service\DateTime;
use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\FlockStore;
use Zend\Json\Json;

/**
 * Class SendCheckCommand
 *
 * @package ApiV1\Command
 */
class SendCheckCommand extends Command
{
    private $logger;

    private $entityManager;

    private $umka;

    private $umkaTest;

    private $umkaKeaz;

    /**
     * @var Normal
     */
    private $normalService;

    /**
     * @var Correction
     */
    private $correctionService;

    /**
     * SendCheckCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param UmkaApi $umka
     * @param Normal $normalService
     * @param Correction $correctionService
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        UmkaApi $umka,
        Normal $normalService,
        Correction $correctionService
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->umka = $umka;

        $this->umkaKeaz = clone $umka;
        $this->umkaKeaz->setHost('office.armax.ru:28183');

        $this->umkaTest = clone $umka;
        $this->umkaTest->setHost('office.armax.ru:38088');

        $this->normalService = $normalService;
        $this->correctionService = $correctionService;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('apiv1:send-check')
            ->setDescription('Отправка чеков')
            ->addArgument(
                'inn',
                InputArgument::OPTIONAL,
                'ИНН клиента'
            )
            ->addOption('limit', 'l', InputArgument::OPTIONAL, 'Лимит запросов')
            ->addOption('test', 't', InputArgument::OPTIONAL, 'Отправка тестовых чеков', false)
            ->addOption('shop', 's', InputArgument::OPTIONAL, 'Отправка по конкретному магазину', false);
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

        $locker = $factory->createLock('lock-send-check-'.$inn.'-'.$isTest.'-'.$shopId, 10800);
        if (!$locker->acquire()) {
            $this->logger->warning('This command is already running in another process.');

            return 0;
        }

        $shop = null;
        if ($shopId) {
            /** @var Shop $shop */
            $shop = $this->entityManager->getRepository(Shop::class)->findOneBy(
                [
                    'id'       => $shopId,
                    'isSingle' => true,
                    'isPack'   => false,
                ]
            );
            if ($shop) {
                $this->logger->debug('Отправка чеков магазина: '.$shop->getTitle());
            } else {
                $this->logger->critical('Магазин с кодом #'.$shopId.' для индивидуальной отправки не найден');

                return 0;
            }
        }

        /** @var Processing[] $tickets */
        $tickets = $this->entityManager
            ->getRepository(Processing::class)
            ->getProcessing(Processing::STATUS_PREPARE, $isTest, $shop, $shop ? $shop->getIsSingle() : false);
        //->findBy(['status' => Processing::STATUS_PREPARE]);

        foreach ($tickets as $ticket) {
            sleep(1); //На всякий пожарный
            /** @var Processing $ticket */

            if (file_exists(ROOT_PATH.'data/stop-send')) {
                $this->logger->info('Наден файл аварийной остановки. Прекращение работы');

                return;
            }

            $this->logger->addInfo('Повторная отправка чека #'.$ticket->getId());
            $ticket->setStatus(200);
            $this->logger->debug('Set status 200');
            $this->entityManager->flush();
            $this->sendTicket($ticket);
        }

        $tickets = $this->entityManager
            ->getRepository(Processing::class)
            ->getProcessing(Processing::STATUS_ACCEPT, $isTest, $shop, $shop ? $shop->getIsSingle() : false);
        //->findBy(['status' => Processing::STATUS_ACCEPT]);
        foreach ($tickets as $ticket) {
            sleep(1);//На всякий пожарный
            if (file_exists(ROOT_PATH.'data/stop-send')) {
                $this->logger->info('Наден файл аварийной остановки. Прекращение работы');

                return;
            }

            $this->logger->addInfo('Отправка чека #'.$ticket->getId());
            $ticket->setStatus(200);
            $this->logger->debug('Set status 200');
            $this->entityManager->flush();
            $this->sendTicket($ticket);
        }

        $this->logger->info('Завершение отправки чеков');
        $locker->release();
    }

    /**
     * @param Processing $processing
     *
     * @return array|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function sendTicket(Processing $processing): ?array
    {
        $shop = $processing->getShop();
        /** @var Kkt $kkt */
        $kkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(
            [
                'shop'         => $shop,
                'isEnabled'    => true,
                'isFiscalized' => true,
            ]
        );
//        /** @var Kkt $item */
//        foreach ($shop->getKkt() as $item) {
//            if ($item->getIsEnabled() && $item->getIsFiscalized()) {
//                $kkt = $item;
//                break;
//            }
//        }
        if ($kkt === null) {
            $this->logger->addWarning('Нет ни одной активной кассы для магазина '.$shop->getTitle());

            return null;
        }
        $this->logger->addDebug('Отправка на кассу #'.$kkt->getSerialNumber());

        $test = null;

        $corrections = [
            Processing::OPERATION_SELL_CORRECTION,
            Processing::OPERATION_BUY_CORRECTION,
        ];

        if (in_array($processing->getOperation(), $corrections)) {
            $test = $this->correctionService->prepareSend($processing, $kkt);
        } else {
            $test = $this->normalService->prepareSend($processing, $kkt);
        }
        $sessionId = $test->getSessionId();

//        var_dump(json_encode($test->toArray()));exit;
//        file_put_contents(ROOT_PATH.'data/check/'.$processing->getId().'.json', json_encode($test->toArray()));

        $umka = $this->umka;

        if ($kkt->getSerialNumber() === Kkt::KEAZ_KKT_ID) {
            $umka = $this->umkaKeaz;
        } elseif ($kkt->getSerialNumber() === Kkt::TEST_KKT_ID) {
            $this->logger->debug('Set test api');
            $umka = $this->umkaTest;
            $this->logger->addError('На тестовой кассе чек не печатается');
            $processing->setError(null)
                ->setFnNumber($kkt->getFsNumber())
                ->setDocNumber($processing->getId())
                ->setDatePrint(new DateTime())
                ->setShiftNumber((int)date('d'))
                ->setReceiptNumber(time())
                ->setEcrRegistrationNumber($kkt->getRegNumber())
                ->setDocumentAttribute(time() + 10)
                ->setStatus(Processing::STATUS_SUCCESS);
            $this->entityManager->flush();

            return null;
        }

        try {
            $this->logger->debug('Request', ['request' => $test->toArray()]);
            $response = $umka->fiscalCheck($test, $kkt->getShop()->getCompany()->getInn());
            $this->logger->debug('Response', ['response' => $response]);
            if (!isset($response['document']['result'])) {
                $processing->setSessionId(null);
                var_dump($response, $umka->getLastResponse(), $umka->getLastHttpCode());
//                exit;
                throw new \Exception('Empty document!');
            }
            if ($response['document']['result'] == 155 || $response['document']['result'] == 154) {
                $processing->setError($response['document']['message']['resultDescription']);
                $processing->setStatus(Processing::STATUS_PRINT_PROCESS);
                $this->entityManager->flush();

                return null;
            }
            if ($response['document']['result'] == 114 && $response['document']['message']['resultDescription'] === 'Сумма платежей меньше суммы чека') {
                $processing->setError($response['document']['message']['resultDescription']);
                $processing->setStatus(Processing::STATUS_ERROR_PRINT);
                $this->entityManager->flush();

                return null;
            }

            if ($response['document']['result'] != 0) {
                $processing->setSessionId(null);
                $processing->setError($response['document']['message']['resultDescription']);
                throw new \Exception($response['document']['message']['resultDescription']);
            }
            $processing->setStatus(Processing::STATUS_SEND_CLIENT);
            $this->logger->debug('Set status '.Processing::STATUS_SEND_CLIENT);
//            $this->entityManager->flush();

            $docOut = new Umka\DocOut($response);

            /** @var Kkt $realKkt */
            $realKkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(['regNumber' => $docOut->{'getKktNumber'}()]);
            if ($realKkt) {
                $processing->setKkt($realKkt);
                //На случай если будет напечатно на "левой" ккт
                if ($realKkt->getSerialNumber() !== $kkt->getSerialNumber()) {
                    $errorLog = [
                        'request'     => $test->toArray(),
                        'response'    => $response,
                        'sendKkt'     => $kkt->getSerialNumber(),
                        'realSendKkt' => $realKkt->getSerialNumber(),
                    ];
                    file_put_contents(ROOT_PATH.'data/logs/umka/send-errors.log', Json::encode($errorLog).PHP_EOL.PHP_EOL, FILE_APPEND);
                }
            }

            $link = null;

            try {
                $link = $docOut->getOfdLink();
            } catch (\Exception $e) {
                $linkQuery = [
                    't'  => (new \DateTime($docOut->{'getDatetime'}()))->format('Ymd\THi00'),
                    's'  => $docOut->{'getReceiptSumElectro'}() / 100,
                    'fn' => $docOut->{'getFN'}(),
                    'i'  => $docOut->getDocNumber(),
                    'fp' => $docOut->{'getFPD'}(),
                    'n'  => $docOut->{'getCalculationSign'}(),
                ];

                $link = http_build_query($linkQuery);
            }

            $processing->setOfdLink($link);
            if ($processing->getError()) {
                $processing->setError(null);
            }

            $processing->setDocNumber($docOut->getDocNumber())
                ->setDatePrint(new \DateTime($docOut->{'getDatetime'}()))
                ->setFnNumber($docOut->getFN())
                ->setShiftNumber($docOut->getShiftNumber())
                ->setReceiptNumber($docOut->getReceiptNumber())
                ->setEcrRegistrationNumber($docOut->getKktNumber())
                ->setDocumentAttribute($docOut->getFPD());

            $this->entityManager->flush();
        } catch (\Exception $e) {
            $processing->setStatus(Processing::STATUS_PREPARE);
            $this->logger->debug('Set status '.Processing::STATUS_PREPARE);
            $this->entityManager->flush();
            $this->logger->addCritical(
                $e->getMessage(),
                [
                    'sessionId'    => $sessionId,
                    'kkt'          => isset($realKkt) ? $realKkt->getSerialNumber() : $kkt->getSerialNumber(),
                    'response'     => $response ?? null,
                    'lastResponse' => $umka->getLastResponse(),
                    'httpCode'     => $umka->getLastHttpCode(),
                ]
            );

            return null;
        }
        if ($processing->getStatus() === 200) {
            $processing->setStatus(Processing::STATUS_PREPARE);
            $this->logger->debug('Set status '.Processing::STATUS_PREPARE);
        }

        $this->entityManager->flush();

        return [
            'payload' => [],
            'link'    => $link,
        ];
    }
}
