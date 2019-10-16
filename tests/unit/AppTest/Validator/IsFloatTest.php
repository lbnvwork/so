<?php
declare(strict_types=1);

namespace AppTest\Validator;

use App\Validator\IsFloat;
use PHPUnit\Framework\TestCase;

class IsFloatTest extends TestCase
{
    /**
     * @param $value
     * @param $result
     *
     * @dataProvider testDataProvider
     */
    public function testIsValid($value, $result): void
    {
        $validator = new IsFloat();
        $this->assertEquals($result, $validator->isValid($value));
    }

    public function testDataProvider(): array
    {
        return [
            [
                0,
                true,
            ],
            [
                10,
                true,
            ],
            [
                -1,
                true,
            ],
            [
                -1.2,
                true,
            ],
            [
                10002,
                true,
            ],
            [
                10002.03,
                true,
            ],
            [
                10002.0323123,
                true,
            ],
            [
                '10002.0323123',
                true,
            ],
            [
                '-10002.0323123',
                true,
            ],
            [
                '-1 0002.0323123',
                false,
            ],
            [
                '1 0002',
                false,
            ],
            [
                '1,0002',
                false,
            ],
        ];
    }
}
