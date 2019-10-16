<?php
declare(strict_types=1);

namespace App\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Class IsFloat
 *
 * @package App\Validator
 */
class IsFloat extends AbstractValidator
{
    public function isValid($value): bool
    {
        if (!is_scalar($value)) {
            return false;
        }

        $type = gettype($value);

        if ($type === 'float') {
            return true;
        }

        $tirmValue = ltrim((string)$value, '-');
        if ((string)$tirmValue !== preg_replace('/[^[:digit:]\.]/', '', (string)$tirmValue)) {
            return false;
        }

        return true;
    }
}
