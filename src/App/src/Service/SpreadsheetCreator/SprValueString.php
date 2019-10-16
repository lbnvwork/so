<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 09.01.19
 * Time: 13:38
 */

namespace App\Service\SpreadsheetCreator;

/**
 * Class SprValueString
 *
 * @package App\Service\SpreadsheetCreator
 */
class SprValueString
{
    private $rowNumber = '';

    private $colStart = '';

    private $colFinal = '';

    /**
     * SprValueString constructor.
     *
     * @param int $rowNumber
     * @param int $colStart
     * @param int $colFinal
     *
     * @throws \Exception
     */
    public function __construct(int $rowNumber, int $colStart, int $colFinal)
    {
        if ($colStart[1] > $colFinal[2]) {
            throw new \Exception("Значение позиции начального столбца должно быть меньше зачения конечного");
        }
        $this->rowNumber = $rowNumber;
        $this->colStart = $colStart;
        $this->colFinal = $colFinal;
    }

    /**
     * @return int
     */
    public function getRowNumber(): int
    {
        return $this->rowNumber;
    }

    /**
     * @return int
     */
    public function getColStart(): int
    {
        return $this->colStart;
    }

    /**
     * @return int
     */
    public function getColFinal(): int
    {
        return $this->colFinal;
    }

    /**
     * @param int $colStart
     */
    public function setColStart(int $colStart)
    {
        $this->colStart = $colStart;
    }

    /**
     * @param int $colFinal
     */
    public function setColFinal(int $colFinal)
    {
        $this->colFinal = $colFinal;
    }

    /**
     * @param int $rowNumber
     */
    public function setRowNumber(int $rowNumber)
    {
        $this->rowNumber = $rowNumber;
    }
}
