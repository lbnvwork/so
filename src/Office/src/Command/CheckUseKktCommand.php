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
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Service\SendMail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;

/**
 * Class CheckUseKktCommand
 *
 * @package Office\Command
 */
class CheckUseKktCommand extends Command
{
    private $logger;

    private $entityManager;

    private $sendMail;

    /**
     * CheckUseKktCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param SendMail $sendMail
     */
    public function __construct(Logger $logger, EntityManager $entityManager, SendMail $sendMail)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->sendMail = $sendMail;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('office:check-use-kkt')
            ->setDescription('Проверка использованя ККТ')
            ->addArgument(
                'number',
                InputArgument::OPTIONAL,
                'Номер в бд'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $number = $input->getArgument('number');

        $qb = $this->entityManager->getRepository(Kkt::class)->createQueryBuilder('k')
            ->where('k.isFiscalized = 1 and k.regNumber IS NOT NULL and k.fiscalCommand IS NOT NULL and k.isDeleted = 0');

        if ($number) {
            $qb->andWhere('k.id = :number')->setParameter('number', $number);
        }

        /** @var Kkt[] $kkts */
        $kkts = $qb->getQuery()->getResult();
        if ($kkts) {
            $shopKkts = [];
            foreach ($kkts as $kkt) {
                /** @var Processing $processing */
                $processing = $this->entityManager->getRepository(Processing::class)->findOneBy(['kkt' => $kkt], ['id' => 'DESC']);
                if ($processing && $processing->getDatetime()->diff(new DateTime())->days > 15) {
                    $days = $processing->getDatetime()->diff(new DateTime())->days;
                    $this->logger->addWarning('Касса #'.$kkt->getId().' не использовалась '.$days.' дней!');
                    $shopKkts[$kkt->getShop()->getId()][] = ['kkt' => $kkt, 'days' => $days];
                }
            }
            $this->entityManager->flush();
            if (\count($shopKkts)) {
                $this->sendMail->sendNotUseKkt($shopKkts);
            }
        }

        $this->logger->info('Проверка использованя ККТ', ['number' => $number]);
    }
}
