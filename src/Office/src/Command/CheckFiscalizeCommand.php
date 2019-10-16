<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:26
 */

namespace Office\Command;

use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Office\Service\Umka;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Zend\Json\Json;

/**
 * Class CheckFiscalizeCommand
 *
 * @package App\Command
 */
class CheckFiscalizeCommand extends Command
{
    private $logger;

    private $entityManager;

    private $umka;

    /**
     * CheckFiscalizeCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param Umka $umka
     */
    public function __construct(Logger $logger, EntityManager $entityManager, Umka $umka)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->umka = $umka;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('office:check-fiscalize')
            ->setDescription('Проверка фискализации ККТ')
            ->addArgument(
                'number',
                InputArgument::OPTIONAL,
                'Серийный номер'
            );
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
        $number = $input->getArgument('number');
        $this->logger->info('Проверка фискализации', ['number' => $number]);

        $qb = $this->entityManager->getRepository(Kkt::class)->createQueryBuilder('k')
            ->where('k.isFiscalized = 0 and k.regNumber IS NOT NULL and k.fiscalCommand IS NOT NULL and k.isDeleted = 0');
        if ($number) {
            $qb->andWhere('k.serialNumber = :number')->setParameter('number', $number);
        }


        /** @var Kkt[] $kkts */
        $kkts = $qb->getQuery()->getResult();
        if ($kkts) {
            $this->logger->addInfo('Количество касс для проверки - '.\count($kkts));
            foreach ($kkts as $kkt) {
                $this->logger->addInfo('Проверка кассы #'.$kkt->getSerialNumber());
                $response = $this->umka->getFiscalResult($kkt);
                if (!isset($response['ext']['kkmCommands'])) {
                    $this->logger->warning('Не корректный ответ сервера');
                    continue;
                }

                $commands = $response['ext']['kkmCommands'];
                foreach ($commands as $command) {
                    if ($command['cmd']['serialNo'] == $kkt->getSerialNumber() && $command['cmd']['regNo'] == $kkt->getRegNumber()) {
                        $this->logger->addInfo('Команда найдена');
                        if ($command['state'] === 'error') {
                            $this->logger->addWarning('Фискализация прошла с ошибкой!', $command['res']);
                        } elseif ($command['state'] === 'finished') {
                            $this->logger->addInfo('Касса фискализированна', $command['res'] ?? []);

                            $docs = $this->umka->getDocuments($kkt, strtotime($command['res']['receiveDt'] ?? $command['dt']) - 60 * 60 * 24);
                            if (isset($docs['ext']['kkmDocs']) && count($docs['ext']['kkmDocs'])) {
                                foreach ($docs['ext']['kkmDocs'] as $key => $doc) {
                                    $this->logger->addDebug('Документ #', [$doc['docNo']]);
                                    if ($doc['docKind'] != 1) {
                                        continue;
                                    }
                                    $this->logger->debug('Документ фискализации найден');

                                    //Неделать === !!!
                                    if ($kkt->getSerialNumber() == $doc['serialNo']) {
                                        $this->logger->addInfo('Касса #'.$kkt->getSerialNumber().' фискализирована');
                                        $kkt->setFiscalRawData(Json::encode($doc))
                                            ->setIsFiscalized(true);
                                        $this->entityManager->flush();
                                    } else {
                                        $this->logger->addError('Не корректный серийник для ККТ #'.$kkt->getSerialNumber().' в команде!', $doc);
                                    }
                                }
                            } else {
                                $this->logger->addWarning('Документ еще не пришел');
                            }
                            break;
                        }
                    } else {
                        $this->logger->addWarning('Неверный ID команды - '.$command['cmdId'], $command['res']);
                    }
                }
            }
            $this->entityManager->flush();
        }
    }
}
