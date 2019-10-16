<?php
declare(strict_types=1);

namespace ApiV1\Command;

use App\Service\DateTime;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GetLogsCommand
 *
 * @package ApiV1\Command
 */
class GetLogsCommand extends Command
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * GetLogsCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
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
            ->setName('apiv1:log-get')
            ->setDescription('Получение логов по кассе')
            ->addOption('kkt', 'k', InputOption::VALUE_REQUIRED, 'ID кассы')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Начало периода')
            ->addOption('to', 't', InputOption::VALUE_OPTIONAL, 'Конец периода')
            ->addOption('file', null, InputOption::VALUE_OPTIONAL, 'Файл с логом');
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
        $idKkt = $input->getOption('kkt');
        $dateFrom = $input->getOption('from');
        $dateTo = $input->getOption('to');
        $logFileName = $input->getOption('file');

        $dateFrom = $dateFrom ? new DateTime($dateFrom) : (new DateTime())->sub(new \DateInterval('P1D'));
        $dateTo = $dateTo ? new DateTime($dateTo) : new DateTime();
        $dateFrom->setTime(0, 0, 0);
        $dateTo->setTime(23, 59, 59);

        $this->logger->info('Поиск логов по кассе за период '.$dateFrom->format('Y-m-d').'-'.$dateTo->format('Y-m-d'), ['kkt' => $idKkt]);
        /** @var Kkt $kkt */
        $kkt = $this->entityManager->getRepository(Kkt::class)->findOneBy(['id' => $idKkt]);
        if ($kkt) {
            $logFileName = $logFileName ?? ROOT_PATH.'data/send-check.log';
            $this->logger->debug('Чтение данных из файла: '.$logFileName);
            $iterator = $this->readFile($logFileName);
//            $iterator = $this->readFile(ROOT_PATH.'data/logs/1-2019-07-01-2019-07-09.log');

            $prevData = [];
            $fileName = ROOT_PATH.'data/logs/'.$kkt->getId().'-'.$dateFrom->format('Y-m-d').'-'.$dateTo->format('Y-m-d').'.log';
            $this->logger->debug('Сохранение данных в файл: '.$fileName);
            exec('wc -l '.($logFileName ?? ROOT_PATH.'data/send-check.log'), $out);
            $countLines = (int)$out[0];
            $this->logger->debug('Количество строк для обработки: '.$countLines);
            $isOwnerKkt = false;
            $count = 0;
            /** @var Processing[] $processings */
            $processings = $this->entityManager->getRepository(Processing::class)
                ->createQueryBuilder('p', 'p.id')
                ->where('p.docNumber IS NOT NULL and p.datetime >= :dateFrom and p.datetime <= :dateTo and p.kkt = :kkt')
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo)
                ->setParameter('kkt', $kkt)
                ->getQuery()->getResult();
            $this->logger->debug('Чеков в бд '.count($processings));
            foreach ($iterator as $key => $item) {
                if (!$item) {
                    break;
                }

                $item = trim($item);
                if ($output->isVerbose()) {
                    printf("\rProcessing: %3d%%", round($key / $countLines * 100, 0));
                }
                preg_match('/^\[([\w\s\-\:]+)\]/', $item, $logDateMatch);
                if (empty($logDateMatch[1]) || strlen($logDateMatch[1]) !== 19) {
                    if (!empty($prevData)) {
                        $prevData[] = $item;
                    }
                    continue;
                }
                try {
                    $logDate = new DateTime($logDateMatch[1]);
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                    $output->writeln($item);

                    return 0;
                }
                if ($logDate <= $dateFrom || $logDate >= $dateTo) {
                    continue;
                }

                if (strpos($item, 'кассу #'.$kkt->getSerialNumber()) !== false) {
                    $isOwnerKkt = true;
                }
                if (strpos($item, 'Повторная отправка чека #') !== false || strpos($item, 'Отправка чека #') !== false) {
                    if (strpos($item, 'Отправка чека #')) {
                        $count++;
                        preg_match('/(\#[\d]+)/', $item, $match);
                        $id = (int)trim($match[1], '#');
                        unset($processings[$id]);
                    }
                    if (!empty($prevData) && $isOwnerKkt) {
                        foreach ($prevData as $data) {
                            file_put_contents($fileName, $data.PHP_EOL, FILE_APPEND);
                        }
                    }
                    $prevData = [];
                    $isOwnerKkt = false;
                }
                $prevData[] = $item;
            }

            if (!empty($prevData) && $isOwnerKkt) {
                foreach ($prevData as $data) {
                    file_put_contents($fileName, $data.PHP_EOL, FILE_APPEND);
                }
            }
            $output->writeln('Уникальных чеков '.$count);

            if (!empty($processings)) {
                $output->writeln('Неизветсные чеки - '.implode(',', array_keys($processings)));
            }
        } else {
            $this->logger->addWarning('Касса не найдена');
        }
    }

    /**
     * @param string $path
     *
     * @return \Generator|null
     */
    protected function readFile(string $path): ?\Generator
    {
        $handle = fopen($path, 'rb');

        while (!feof($handle)) {
            yield fgets($handle);
        }

        fclose($handle);
    }
}
