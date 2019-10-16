<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 30.01.19
 * Time: 9:38
 */

namespace Office\Repository;

use App\Service\DateTime;
use Doctrine\ORM\EntityRepository;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Entity\Shop;

/**
 * Class ShopRepository
 *
 * @package Office\Repository
 */
class ShopRepository extends EntityRepository
{
    /**
     * Возвращает оборот по кассам магазина с первого дня по текущий
     *
     * @param Shop $shop
     *
     * @return int|string|null
     * @throws \Exception
     */
    public function getMonthTurnoverShop(Shop $shop)
    {
        $turnover = 0;
        /** @var Processing[] $cheques */
        $cheques = $this->getEntityManager()->getRepository(Processing::class)
            ->createQueryBuilder('s')
            ->where('s.shop = :shop AND s.datetime BETWEEN :firstDay AND :currDay')
            ->setParameters(
                [
                    'shop'     => $shop,
                    'currDay'  => new DateTime(),
                    'firstDay' => new DateTime('first day of this month 00:00:00')
                ]
            )
            ->getQuery()
            ->getResult();
        foreach ($cheques as $cheque) {
            $turnover += $cheque->getSum();
        }

        return $turnover;
    }

    /**
     * @param Kkt $kkt
     *
     * @return int|string|null
     * @throws \Exception
     */
    public function getMonthTurnoverKkt(Kkt $kkt)
    {
        $turnover = 0;
        $currDate = new DateTime();
        $firstDay = new DateTime('first day of this month 00:00:00');
        /** @var Processing[] $cheques */
        $cheques = $this->getEntityManager()->getRepository(Processing::class)
            ->createQueryBuilder('k')
            ->where('k.kkt = :kkt AND k.datetime BETWEEN :firstDay AND :currDay')
            ->setParameters(
                [
                    'kkt'      => $kkt,
                    'currDay'  => $currDate,
                    'firstDay' => $firstDay
                ]
            )
            ->getQuery()
            ->getResult();
        foreach ($cheques as $cheque) {
            $turnover += $cheque->getSum();
        }
        return $turnover;
    }

    /**
     * @param Kkt $kkt
     *
     * @return float|int
     * @throws \Exception
     */
    public function getMonthTurnoverPercentKkt(Kkt $kkt)
    {
        $turnover = $this->getMonthTurnoverKkt($kkt);
        $turnoverPercent = 0;
        if (!empty($turnover)) {
            $tariff = $kkt->getTariff();
            $turnoverPercent = $turnover / 100 * $tariff->getTurnoverPercent();
        }

        return $turnoverPercent;
    }

    /**
     * @param Kkt $kkt
     *
     * @return float|int|null
     * @throws \Exception
     */
    public function getKktPrice(Kkt $kkt)
    {
        $tariff = $kkt->getTariff();
        $turnoverPercent = $this->getMonthTurnoverPercentKkt($kkt);
        if (!empty($tariff)) {
            return ($tariff->getRentCost() + $turnoverPercent) * $tariff->getMonthCount();
        }

        return null;
    }
}
