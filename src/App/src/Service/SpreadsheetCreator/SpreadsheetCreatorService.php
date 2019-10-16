<?php

namespace App\Service\SpreadsheetCreator;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class SpreadsheetCreatorService
 *
 * @package App\Service\SpreadsheetCreator
 */
class SpreadsheetCreatorService
{
    protected $sheet;

    protected $spreadsheet;

    protected $fileTemplate = '';

    /**
     * @param string $fileTemplatePath
     * @param SprValueBuilder ...$SprValObjects
     *
     * @return Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getSpreadsheet(string $fileTemplatePath, SprValueBuilder ...$SprValObjects): Spreadsheet
    {
        $this->generateSpreadsheet($fileTemplatePath);

        foreach ($SprValObjects as $item) {
            $this->sheet = $this->spreadsheet->getSheet($item->getSheetIndex());
            $this->setValueByWords($item->getValue(), $item->getJumpLength(), ...$item->getPosition());
            //$this->setValueByLetters($item->getValue(), $item->getJumpLength(), ...$item->getPosition());
        }

        return $this->spreadsheet;
    }

    /**
     * @param string $filePath
     * @param string $fileName
     *
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function putSpreadsheetIntoFile(string $filePath, string $fileName): bool
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($_SERVER['DOCUMENT_ROOT'].$filePath.$fileName);

        return true;
    }

    /**
     * @return string|null
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function putSpreadsheetIntoStream(): ?string
    {
        $writer = new Xlsx($this->spreadsheet);
        ob_start();
        $writer->save('php://output');
        $data = ob_get_contents();
        ob_get_clean();

        return $data ?? null;
    }

    /**
     * @return string
     */
    public function getFileTemplatePath()
    {
        return $this->fileTemplate;
    }

    /**
     * @param string $fileTemplatePath
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function generateSpreadsheet(string $fileTemplatePath)
    {

        $this->fileTemplate = $fileTemplatePath;
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $this->spreadsheet = $reader->load($this->fileTemplate);
        $this->sheet = $this->spreadsheet->getSheet(0);
    }

    /**
     * Устанавливает значение в таблицу
     *
     * @param string $value
     * @param int $jumpLength
     * @param SprValueString ...$position
     */
    protected function setValueByWords(string $value, int $jumpLength, SprValueString ...$position)
    {

        if ($jumpLength == 0) {
            $jumpLength = 1;
        } //защита от деления на ноль

        $positionIndex = 0;
        $currPosition = $position[$positionIndex]; //фокус на первой позиции

        $words = preg_split("/\s/", trim($value), -1, PREG_SPLIT_NO_EMPTY);

        //begin добавление слов в строки
        foreach ($words as $wordIndex => $word) {
            if ($wordIndex + 1 < count($words)) {
                $word .= ' ';
            } //к последнему слову пробел не добавляем

            //begin если слово не помещается в строку, перенос на следующую
            $wordLength = mb_strlen($word);
            $freeSpace = ceil(($currPosition->getColFinal() - $currPosition->getColStart() + 1) / $jumpLength);
            $freeRowsCount = count($position);
            $currRowNumber = $positionIndex + 1;

            if ($wordLength > $freeSpace && $currRowNumber < $freeRowsCount) {
                $positionIndex++;
                $currPosition = $position[$positionIndex];
                $this->setChByChValue($currPosition->getColStart(), $currPosition->getRowNumber(), $word, $jumpLength);
                $currPosition->setColStart($currPosition->getColStart() + ($wordLength * $jumpLength));
            } elseif ($wordLength <= $freeSpace) {
                $this->setChByChValue($currPosition->getColStart(), $currPosition->getRowNumber(), $word, $jumpLength);
                $currPosition->setColStart($currPosition->getColStart() + ($wordLength * $jumpLength));
            } else {
                break;
            }
            //end если слово не помещается в строку, перенос на следующую
        }
        //end добавление слов в строки
    }

    /**
     * @param string $value
     * @param int $jumpLength
     * @param SprValueString ...$position
     */
    protected function setValueByLetters(string $value, int $jumpLength, SprValueString ...$position)
    {
        if ($jumpLength == 0) {
            $jumpLength = 1;
        } //защита от деления на ноль

        $content = $value;
        $character = '';

        foreach ($position as $currRow) {
            for ($i = $currRow->getColStart(); $i <= $currRow->getColFinal(); $i += $jumpLength) {
                $character = mb_substr($content, 0, 1);
                if ($character === '') {
                    continue;
                }
                $this->setChByChValue($i, $currRow->getRowNumber(), $character, $jumpLength);
                $content = mb_substr($content, 1);
            }
        }
    }

    /**
     * Добавляет строку в ячейки таблицы посимвольно
     * @param int $colStart
     * @param int $rowStart
     * @param $value
     * @param int $jumpLength
     */
    private function setChByChValue(int $colStart, int $rowStart, $value, int $jumpLength = 3)
    {

        $valueArr = $this->strSplitUnicode($value);
        foreach ($valueArr as $i => $val) {
            $this->sheet->setCellValueByColumnAndRow($colStart, $rowStart, $val);
            $colStart += $jumpLength;
        }
    }

    /**
     * Разбивает строку на символы (без обшибок кодировки)
     * @param string $str
     * @param int $l
     *
     * @return array
     */
    private function strSplitUnicode(string $str, int $l = 0): array
    {
        if ($l > 0) {
            $ret = [];
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }

            return $ret;
        }

        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }
}
