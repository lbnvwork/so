<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 08.08.18
 * Time: 10:05
 */

namespace Cms\Command;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Office\Entity\Fn;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Service\Umka;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GetAllFnCommand
 *
 * @package Cms\Command
 */
class GetAllFnCommand extends Command
{

    private $logger;

    private $entityManager;

    private $umka;

    public const STATUS_FREE = 0; // Свободна
    public const STATUS_FISCALIZED = 1; // Фискализирована
    public const STATUS_ACTIVE = 2; // Установлена
    public const STATUS_DELETED = 3; // Удалена


    /**
     * GetAllFnCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param Umka $umka
     */
    public function __construct(
        Logger $logger,
        EntityManager $entityManager,
        Umka $umka
    ) {
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
        $this->setName('cms:getallfn')
            ->setDescription('Получение всех незанятых и занятых объектов ККТ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $notRegKkts = $this->umka->getAllNotRegistredKkt();

        foreach ($notRegKkts as $notRegKkt) {
            if ('' !== $notRegKkt['serialNo']) {
                $fnCheck = $this->entityManager->getRepository(Fn::class)
                    ->findOneBy(['serialNumber' => $notRegKkt['serialNo']]);
                if ($fnCheck === null) {
                    $fn = new Fn();
                    $fn->setSerialNumber($notRegKkt['serialNo']);
                    if (array_key_exists('fsStatus', $notRegKkt['status'])) {
                        if ($notRegKkt['status']['fsStatus']['fsNumber'] === null) {
                            continue;
                        }
                        $fn->setFnNumber($notRegKkt['status']['fsStatus']['fsNumber']);
                        $fn->setFnVersion($notRegKkt['status']['fsStatus']['fsVersion']);
                        $fn->setDocumentNumber('0');
                        $fn->setStatus(self::STATUS_FREE);
                        $this->entityManager->persist($fn);
                    }
                }
            }
        }
        //$this->entityManager->flush();

        /** @var Kkt[] $regKkts */
        $regKkts = $this->entityManager->getRepository(Kkt::class)
            ->createQueryBuilder('k')
            ->where('k.fsNumber IS NOT NULL')
            ->getQuery()->getResult();

        /** @var Kkt $regKkt */
        foreach ($regKkts as $regKkt) {
            $fn = $this->entityManager->getRepository(Fn::class)
                ->findOneBy(['fnNumber' => $regKkt->getFsNumber()]);
            if ($fn === null) {
                $fn = new Fn();
                $fn->setSerialNumber($regKkt->getSerialNumber());
                $fn->setFnNumber($regKkt->getFsNumber());
                $fn->setFnVersion($regKkt->getFsVersion());
                $fn->setCompany($regKkt->getShop()->getCompany()->getTitle());
            }

            $fn->setIsFiscalized($regKkt->getIsFiscalized());

            if ($regKkt->getFiscalRawData() === null) {
                $fn->setStatus(self::STATUS_ACTIVE);
            } else {
                $jsonData = json_decode($regKkt->getFiscalRawData(), true);
                $bodyFiscalized = json_decode($jsonData['body'], true);
                $fn->setDateFiscalized(new \DateTime($bodyFiscalized['t1012']));
                $fn->setStatus(self::STATUS_FISCALIZED);
            }

            if ($regKkt->getIsDeleted()) {
                $fn->setIsDeleted($regKkt->getIsDeleted());
                $fn->setDateDeleted($regKkt->getDateDeleted());
                $fn->setStatus(self::STATUS_DELETED);
            }


            /** @var Processing[] $lastDocNum */
            $lastDocNum = $this->entityManager->getRepository(Processing::class)
                ->createQueryBuilder('p')
                ->where('p.kkt = '.$regKkt->getId())
                ->andWhere('p.kkt = '.$regKkt->getId())
                ->andWhere('p.status = 4')
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();

            /** @var Processing $l */
            foreach ($lastDocNum as $l) {
                $fn->setDocumentNumber($l->getDocNumber());
            }
            $this->entityManager->persist($fn);
        }

        $this->entityManager->flush();
        $this->logger->info('Данные успешно записаны.');
    }
}
