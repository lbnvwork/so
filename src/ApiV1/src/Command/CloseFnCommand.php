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
use Zend\Json\Json;

/**
 * Class CloseFnCommand
 *
 * @package ApiV1\Command
 */
class CloseFnCommand extends Command
{
    private $logger;

    private $entityManager;

    private $umka;

    private $umkaTest;

    /**
     * CloseFnCommand constructor.
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
            ->setName('apiv1:close-fn')
            ->setDescription('Закрытие ФН')
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
        $this->logger->info('Закрытие ФН', ['kkt' => $idKkt]);
        /** @var Kkt $kkt */
        $kkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(['id' => $idKkt]);

        if ($kkt) {
            $umka = $this->umka;
            if ($kkt->getSerialNumber() === '11200001' || $kkt->getSerialNumber() === '17000675') {
                $this->logger->debug('Set test api');
                $umka = $this->umkaTest;
            }
            $response = null;
//            var_dump($umka->getCashboxStatus($kkt->getSerialNumber()));
//            return;
            try {
                $response = $umka->closeFn($kkt->getSerialNumber(), $kkt->getShop()->getCompany()->getInn());
                //fiscalCheck($data, $kkt->getShop()->getCompany()->getInn());
                var_dump($response);
                $kkt->setCloseFnRawData(Json::encode($response));
                $this->entityManager->flush();
            } catch (\Exception $e) {
                var_dump($response);
                $this->logger->addCritical(
                    $e->getMessage(),
                    [
                        'kkt'          => $kkt->getSerialNumber(),
                        'response'     => $response,
                        'lastResponse' => $umka->getLastResponse(),
                        'httpCode'     => $umka->getLastHttpCode(),
                    ]
                );
            }
        } else {
            $this->logger->addWarning('Касса не найдена');
        }
    }
}
