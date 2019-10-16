<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 16.03.18
 * Time: 23:10
 */

namespace App\Service;

use ZendService\ReCaptcha\ReCaptcha;

/**
 * Class RecaptchaService
 *
 * @package App\Service
 */
class RecaptchaService
{
    private const PRIVATE_RECAPTCHA_KEY = '6Le3mIQUAAAAAPUb4i6EA7iu_ZArcmQyNi0fG-b4';
    private const PUBLIC_RECAPTCHA_KEY = '6Le3mIQUAAAAAPAcJphR3BWh4cwP7XR69wVt-9Iy';

    /**
     * @return ReCaptcha
     */
    public function getRecaptcha()
    {
        return new ReCaptcha(self::PUBLIC_RECAPTCHA_KEY, self::PRIVATE_RECAPTCHA_KEY);
    }
}
