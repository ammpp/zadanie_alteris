<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\ExchangeCache;

class ExchangeCacheTest extends KernelTestCase
{
    public function testSaveExchange(): void
    {
    	$container = static::getContainer();

    	$redis = $container->get('Predis\Client');

    	$exchangeCache = new ExchangeCache($redis, 5);

    	self::assertEquals($exchangeCache->save('PLN', 'EUR', 2), 2);
    	self::assertEquals($redis->get('PLN_EUR'), 2);
    }
}
