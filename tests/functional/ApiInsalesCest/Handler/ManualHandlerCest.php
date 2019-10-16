<?php

namespace ApiInsalesCest\Handler;

use PHPUnit\Framework\TestCase;

class ManualHandlerCest
{
    public function successView(\FunctionalTester $tester): void
    {
        $tester->sendGET(
            '/insales/manual'
        );
        $tester->seeResponseCodeIs(200);
    }
}
