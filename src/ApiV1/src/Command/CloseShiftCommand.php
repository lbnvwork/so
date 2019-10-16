<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:26
 */

namespace ApiV1\Command;

use ApiV1\Service\Umka\UmkaApi;
use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;

/**
 * Class CloseShiftCommand
 *
 * @package ApiV1\Command
 */
class CloseShiftCommand extends Command
{
    private $logger;

    private $entityManager;

    private $umka;

    private $umkaTest;

    /**
     * CloseShiftCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param UmkaApi $umka
     */
    public function __construct(Logger $logger, EntityManager $entityManager, UmkaApi $umka)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->umka = $umka;
        $this->umkaTest = clone $umka;
        $this->umkaTest->setHost('office.armax.ru:38088');

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('apiv1:close-shift')
            ->setDescription('Закрытие смены')
            ->addArgument(
                'kkt',
                InputArgument::REQUIRED,
                'ID кассы'
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
        $idKkt = $input->getArgument('kkt');
        $this->logger->info('Закрытие смены', ['kkt' => $idKkt]);
        /** @var Kkt $kkt */
        $kkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(['id' => $idKkt]);
        if ($kkt) {
            $umka = $this->umka;
            if ($kkt->getSerialNumber() === '11200001' || $kkt->getSerialNumber() === '17000675') {
                $this->logger->debug('Set test api');
                $umka = $this->umkaTest;
            }
            $response = null;

            try {
                $response = $umka->cycleClose($kkt->getSerialNumber());
                var_dump($response);
            } catch (\Exception $e) {
                $this->logger->addCritical(
                    $e->getMessage(),
                    [
                        'kkt'          => $kkt->getSerialNumber(),
                        'response'     => $response,
                        'lastResponse' => $umka->getLastResponse(),
                        'httpCode'     => $umka->getLastHttpCode()
                    ]
                );
            }
        } else {
            $this->logger->addWarning('Касса не найдена');
        }
    }
}
