<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 16.03.18
 * Time: 23:10
 */

namespace App\Service\SpreadsheetCreator;

/**
 * Class SpreadsheetCreatorService
 *
 * @package App\Service
 */
class SprValueBuilder
{
    const NAME = '';
    const VALUE = '';
    const SHEETINDEX = 0;
    const JUMPLENGTH = 3;
    const POSITION = [];

    /**
     * @var string
     */
    protected $name = self::NAME;

    /**
     * @var string
     */
    protected $value = self::VALUE;

    /**
     * @var array
     */
    protected $position = self::POSITION;

    /** @var int */
    private $jumpLength = self::JUMPLENGTH;

    /** @var int */
    private $sheetIndex = self::SHEETINDEX;

    /**
     * SprValueBuilder constructor.
     *
     * @param string $name
     * @param int $sheetIndex
     * @param string $value
     * @param int $jumpLength
     * @param SprValueString ...$position
     *
     * @return bool
     */
    public function __construct(
        string $name = self::NAME,
        int $sheetIndex = self::SHEETINDEX,
        string $value = self::VALUE,
        int $jumpLength = self::JUMPLENGTH,
        SprValueString ...$position
    ) {
        $this->setName($name);
        $this->setValue($value);
        $this->setPosition($position);
        $this->setJumpLength($jumpLength);
        $this->setSheetIndex($sheetIndex);

        return true;
    }

    /**
     * @param string $name
     *
     * @return SprValueBuilder
     */
    public function setName(string $name): SprValueBuilder
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return SprValueBuilder
     */
    public function setValue(string $value): SprValueBuilder
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param array $strings
     *
     * @return SprValueBuilder
     */
    public function setPosition(array $strings): SprValueBuilder
    {
        try {
            foreach ($strings as $string) {
                if ($string->getColStart() > $string->getColFinal()) {
                    throw new \Exception("Значение позиции начального столбца должно быть меньше зачения конечного");
                }
            }
        } catch (\Exception $e) {
            echo $e;
        }
        $this->position = $strings;

        return $this;
    }

    /**
     * @param int $sheetIndex
     *
     * @return SprValueBuilder
     */
    public function setSheetIndex(int $sheetIndex): SprValueBuilder
    {
        $this->sheetIndex = $sheetIndex;

        return $this;
    }

    /**
     * @param int $jumpLength
     *
     * @return SprValueBuilder
     */
    public function setJumpLength(int $jumpLength): SprValueBuilder
    {
        $this->jumpLength = $jumpLength;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getPosition(): array
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getJumpLength()
    {
        return $this->jumpLength;
    }

    /**
     * @return int
     */
    public function getSheetIndex()
    {
        return $this->sheetIndex;
    }
}
