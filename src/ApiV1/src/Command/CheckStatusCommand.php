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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;

/**
 * Class CheckStatusCommand
 *
 * @package ApiV1\Command
 */
class CheckStatusCommand extends Command
{
    private $logger;

    private $entityManager;

    private $umka;

    /**
     * CheckStatusCommand constructor.
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

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('apiv1:check-status')
            ->setDescription('Проверка доступности ККТ')
            ->addArgument(
                'inn',
                InputArgument::OPTIONAL,
                'ИНН клиента'
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
        $inn = $input->getArgument('inn');

        var_dump($this->umka->getCashboxStatus($inn));

        $this->logger->info('Проверка доступности ККТ', ['inn' => $inn]);
    }
}
