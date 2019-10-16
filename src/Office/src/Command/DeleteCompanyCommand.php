<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:26
 */

namespace Office\Command;

use Doctrine\ORM\EntityManager;
use Office\Entity\Company;
use Office\Entity\Invoice;
use Office\Entity\Kkt;
use Office\Service\Umka;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;

/**
 * Class DeleteCompanyCommand
 *
 * @package Office\Command
 */
class DeleteCompanyCommand extends Command
{
    private $logger;

    private $entityManager;

    /**
     * CheckFiscalizeCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param Umka $umka
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
            ->setName('office:delete-company')
            ->setDescription('Удаление компании')
            ->addArgument(
                'number',
                InputArgument::REQUIRED,
                'Номер компании'
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

        $company = $this->entityManager->getRepository(Company::class)->find($number);
        if ($company) {
            $invoices = $this->entityManager->getRepository(Invoice::class)->findBy(['company' => $company]);
            if ($invoices) {
                foreach ($invoices as $invoice) {
                    $this->entityManager->remove($invoice);
                }
            }

            $this->entityManager->remove($company);
            $this->entityManager->flush();
            $this->logger->addInfo('Компания удалена');
        } else {
            $this->logger->addWarning('Компания не найдена');
        }

        $this->logger->info('Удаление компании', ['number' => $number]);
    }
}
