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

/**
 * Class FiscalizeCommand
 *
 * @package Office\Command
 */
class FiscalizeCommand extends Command
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
            ->setName('office:fiscalize')
            ->setDescription('Фискализации ККТ')
            ->addArgument(
                'number',
                InputArgument::REQUIRED,
                'Серийный номер'
            )->addArgument(
                'rnm',
                InputArgument::REQUIRED,
                'РНМ'
            )->addArgument('reason', InputArgument::OPTIONAL, 'Вид регистрации')
            ->addOption('force', 'f')
            ->addOption('test', 't');
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
        $rnm = $input->getArgument('rnm');
        $isForce = $input->getOption('force');
        $isTest = $input->getOption('test');
        $reason = -1;
        if ($input->getArgument('reason')) {
            $reason = (int)$input->getArgument('reason');
        }
//        var_dump($reason, $isForce);exit;

        /** @var Kkt $kkt */
        $kkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(['id' => $number]);

        $this->logger->info(
            'Фискализации',
            [
                'number' => $number,
                'rnm'    => $rnm
            ]
        );

        if (empty($rnm) && $kkt->getRegNumber()) {
            $rnm = $kkt->getRegNumber();
        }

//        if (!$kkt->getIsEnabled()) {
//            $this->logger->info('Касса не установлена');
//        } else {
        if (!empty($rnm)) {
            if ($kkt->getRegNumber() && !$isForce) {
                $this->logger->info('РНМ уже записан');
            } else {
                $kkt->setRegNumber($rnm)
                    ->setIsFiscalized(false);
                $this->entityManager->persist($kkt);
                $this->entityManager->flush();

                if ($this->umka->fiscalizeKkt($kkt, $reason, $isTest)) {
                    $this->logger->info('РНМ сохранен, ожидайте активации');
                } else {
                    $this->logger->info('Ошибка обработки запроса');
                }
            }
        } else {
            $this->logger->info('Не корректный РНМ');
        }
//        }
    }
}
