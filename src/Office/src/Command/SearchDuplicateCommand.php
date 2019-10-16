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
use Office\Entity\Processing;
use Office\Service\Umka;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;

/**
 * Class SearchDuplicateCommand
 *
 * @package Office\Command
 */
class SearchDuplicateCommand extends Command
{
    private const TYPE_REGISTER = 1;
    private const TYPE_OPEN_SHIFT = 2;
    private const TYPE_CLOSE_SHIFT = 5;
    private const TYPE_CHECK = 3;
    private const TYPE_CHECK_CORRECTION = 4;

    private $logger;

    private $entityManager;

    private $umka;

    /**
     * CheckStatusCommand constructor.
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
            ->setName('office:search-duplicate-check')
            ->setDescription('Поиск дубликтаов чеков')
            ->addOption('kkt', 'k', InputOption::VALUE_OPTIONAL, 'Код кассы')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Начало периода')
            ->addOption('to', 't', InputOption::VALUE_OPTIONAL, 'Конец периода');
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
        $kktId = $input->getOption('kkt');
        $dateFrom = null;
        $dateTo = null;
        if ($input->getOption('from')) {
            $dateFrom = strtotime($input->getOption('from').' 00:00:01');
        }
        if ($input->getOption('to')) {
            $dateTo = strtotime($input->getOption('to').' 23:59:59');
        }

        if ($kktId) {
            $kkt = null;
            if ($kktId) {
                /** @var Kkt $kkt */
                $kkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(['id' => $kktId]);
                if ($kkt === null) {
                    $this->logger->alert('Касса не найдена');

                    return;
                }
                $this->search($kkt, $dateFrom, $dateTo);
            }

            return;
        }

        /** @var Kkt[] $kkts */
        $kkts = $this->entityManager->getRepository(Kkt::class)->findBy(['isFiscalized' => true]);
        foreach ($kkts as $kkt) {
            $this->search($kkt, $dateFrom, $dateTo);
        }
    }

    /**
     * @param Kkt $kkt
     * @param int $dateFrom
     * @param int $dateTo
     *
     * @throws \Exception
     */
    public function search(Kkt $kkt, ?int $dateFrom, ?int $dateTo): void
    {
        $this->logger->debug('Поиск дубликатов по кассе #'.$kkt->getSerialNumber().' ('.$kkt->getId().')');

        $this->logger->debug('Получение номеров в нашей бд');
        $processingIds = $this->entityManager->getRepository(Processing::class)->createQueryBuilder('p')
            ->select('p.docNumber')
            ->where('p.docNumber IS NOT NULL and p.datePrint >= :dateFrom and p.datePrint <= :dateTo and p.kkt = :kkt')
            ->setParameter('dateFrom', new \DateTime(date('Y-m-d H:i:s', $dateFrom)))
            ->setParameter('dateTo', new \DateTime(date('Y-m-d H:i:s', $dateTo)))
            ->setParameter('kkt', $kkt)
            ->getQuery()
            ->getArrayResult();
        $ids = [];
        foreach ($processingIds as $id) {
            $ids[$id['docNumber']] = $id['docNumber'];
        }
        $this->logger->debug('Найдено '.count($ids).' документов');

        $this->logger->debug('Получение документов из кассы');
        $docs = $this->umka->getDocuments($kkt, $dateFrom, $dateTo);

        $this->logger->debug('Обработка данных');
        $countChecks = 0;
        $docNumbers = [];

        $skipDocs = [];
        foreach ($docs['ext']['kkmDocs'] as $doc) {
            if ($doc['docKind'] != self::TYPE_CHECK && $doc['docKind'] != self::TYPE_CHECK_CORRECTION) {
                $skipDocs[] = $doc['docNo'];
                continue;
            }

            $countChecks++;
            if (!in_array($doc['docNo'], $ids)) {
                $this->logger->warning('Не найден номер документа у нас: '.$doc['docNo'], $doc);
            } else {
                unset($ids[$doc['docNo']]);
            }
            if (in_array($doc['docNo'], $docNumbers)) {
                $this->logger->warning('Повтор номера документа: '.$doc['docNo']);
            } else {
                $docNumbers[] = $doc['docNo'];
            }
        }
        $this->logger->info('Чеков в кассе: '.$countChecks);
        if (!empty($ids)) {
            $this->logger->debug('Остаток: '.implode(',', $ids));
        }

        $prevDocNumber = null;
        sort($docNumbers);
        foreach ($docNumbers as $docNumber) {
            if ($prevDocNumber !== null && $prevDocNumber + 1 !== (int)$docNumber && !in_array($prevDocNumber + 1, $skipDocs)) {
                $this->logger->warning(
                    'Пропуск документа: '.($prevDocNumber + 1),
                    [
                        'prev'    => $prevDocNumber,
                        'current' => $docNumber,
                    ]
                );
            }
            $prevDocNumber = (int)$docNumber;
        }
    }
}
