<?php
declare(strict_types=1);

namespace Office\Service;

use ApiV1\Service\Umka\UmkaApi;
use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Zend\Json\Json;

/**
 * Class KktService
 *
 * @package Office\Service
 */
class KktService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Umka
     */
    private $umka;

    /**
     * @var UmkaApi
     */
    private $umkaApi;

    /**
     * KktService constructor.
     *
     * @param EntityManager $entityManager
     * @param Umka $umka
     * @param UmkaApi $umkaApi
     */
    public function __construct(EntityManager $entityManager, Umka $umka, UmkaApi $umkaApi)
    {
        $this->entityManager = $entityManager;
        $this->umka = $umka;
        $this->umkaApi = $umkaApi;
    }

    /**
     * Получение отчет о фискализации
     *
     * @param Kkt $kkt
     *
     * @return string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getFiscalReport(Kkt $kkt): string
    {
        if (empty($kkt->getFiscalRawData())) {
            $response = $this->umka->getDocuments($kkt);
            $kkt->setFiscalRawData(Json::encode($response));
            $this->entityManager->persist($kkt);
            $this->entityManager->flush();
        } else {
            $response = Json::decode($kkt->getFiscalRawData(), Json::TYPE_ARRAY);
        }


        $shop = $kkt->getShop();
        $company = $shop->getCompany();
        $ofd = $company->getOfd();

//        $nalogSystem = (int)$company->getNalogSystem();

        //TODO удалить после теста, только документ оставить
        $doc = $response['ext']['kkmDocs'][0] ?? $response;

        $body = Json::decode($doc['body'], Json::TYPE_ARRAY);

        $nalogSystem = $body['t1062'] ?? pow(2, (int)$kkt->getShop()->getCompany()->getNalogSystem());

        $string = [];
        $string[] = 'Отчет о регистрации';
        $string[] = '====================';
        $string[] = 'наименование пользователя: '.$company->getDefaultTitle();
        $string[] = 'адрес: '.$shop->getUrl();
        $string[] = 'инн пользователя: '.$company->getInn();
        $string[] = 'рег. номер ККТ: '.$kkt->getRegNumber();
        $string[] = 'зав. номер ККТ: '.$kkt->getSerialNumber();
        $string[] = 'системы налогооблажения:';
        if ($nalogSystem === pow(2, 0)) {
            $string[] = '   - общая';
        }
        if ($nalogSystem === pow(2, 1)) {
            $string[] = '   - УСН Доход';
        }
        if ($nalogSystem === pow(2, 2)) {
            $string[] = '   - УСН Доход минус расход';
        }
        if ($nalogSystem === pow(2, 3)) {
            $string[] = '   - ЕНВД';
        }
        if ($nalogSystem === pow(2, 4)) {
            $string[] = '   - ЕСХН';
        }
        if ($nalogSystem === pow(2, 5)) {
            $string[] = '   - ПСН';
        }
        $string[] = 'автономный режим: 0';
        $string[] = 'признак услуги: 0';
        $string[] = 'признак шифрования: 1';
        $string[] = 'признак расчетов в интернете: 1';
        $string[] = 'автоматический режим: 0';
        $string[] = 'ИНН ОФД: '.$ofd->getInn();
        $string[] = 'зав. номер ФН: '.$kkt->getFsNumber();

        $string[] = 'дата, время: '.(new \DateTime($body['t1012']))->format('d.m.Y H:i');//date('d.m.Y H:i', strtotime($doc['docDt']));
        $string[] = 'порядковый номер ФД: '.$doc['docNo'];
        $string[] = 'ФП документа: '.$doc['fiscalCode'];

        return implode(PHP_EOL, $string);
    }

    /**
     * Обновление данных из Армакса
     *
     * @param Kkt $kkt
     *
     * @throws \Exception
     */
    public function updateKkt(Kkt $kkt)
    {
        $status = $this->umka->getKktInfo($kkt);

        if (!empty($status['status']['fsStatus']['fsNumber'])) {
            $kkt->setFsNumber($status['status']['fsStatus']['fsNumber']);
        }
        if (!empty($status['status']['fsStatus']['fsVersion'])) {
            $kkt->setFsVersion($status['status']['fsStatus']['fsVersion']);
        }
    }

    /**
     * Фискализация кассы
     *
     * @param Kkt $kkt
     * @param int $idReason
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fiscalize(Kkt $kkt, int $idReason)
    {
        $this->umka->fiscalizeKkt($kkt, $idReason);
        $kkt->setIsFiscalized(false);
    }

    /**
     * Закрытие ФН
     *
     * @param Kkt $kkt
     *
     * @throws \ApiV1\Service\Umka\Exception\TimeoutException
     */
    public function closeFn(Kkt $kkt)
    {
        $this->umkaApi->closeFn($kkt->getSerialNumber(), $kkt->getShop()->getCompany()->getInn());
    }
}
