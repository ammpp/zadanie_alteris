<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\ApiClient;

class ApiClientTest extends TestCase
{
	/** @var CacheInterface */
	protected $fileCache;

    public function testGetSymbolsFromCache(): void
    {
    	$client = new MockHttpClient();
    	$apiClient = new ApiClient($client, $this->fileCache, 'xxx');
    	$this->fileCache
	    	->expects($this->once())
	    	->method('get')
	    	->with('currenciesSymbols', [$apiClient, 'readSymbols'])
	    	->willReturn('{symbols}');
	   	$this->fileCache
	    	->expects($this->never())
	    	->method('delete')
	    	->with('currenciesSymbols');

    	self::assertEquals($apiClient->getSymbols(), '{symbols}');
    }

    public function testUnableToGetSymbols(): void
    {
    	$client = new MockHttpClient();
    	$apiClient = new ApiClient($client, $this->fileCache, 'xxx');
    	$this->fileCache
	    	->expects($this->once())
	    	->method('get')
	    	->with('currenciesSymbols', [$apiClient, 'readSymbols'])
	    	->willReturn(null);
    	$this->fileCache
    		->expects($this->once())
	    	->method('delete');

    	self::assertEquals($apiClient->getSymbols(), '{}');
    }

    public function testGetExchangeFromApi()
    {
    	$client = new MockHttpClient([
    		new MockResponse('{"success": true,"timestamp": 1519296206,"base": "USD","date": "2021-03-17","rates": {"GBP": 0.72007,"JPY": 107.346001,"EUR": 0.813399}}')
    	]);

    	$apiClient = new ApiClient($client, $this->fileCache, 'xxx');

    	self::assertEquals($apiClient->getExchange('USD', 'JPY'), 107.346001);
    }

    public function testFailedToGetWrongExchangeFromApi()
    {
    	$client = new MockHttpClient([
    		new MockResponse('{"success": true,"timestamp": 1519296206,"base": "USD","date": "2021-03-17","rates": {"GBP": 0.72007,"JPY": 107.346001,"EUR": 0.813399}}')
    	]);

    	$apiClient = new ApiClient($client, $this->fileCache, 'xxx');

    	self::assertEquals($apiClient->getExchange('USD', 'KPW'), 0);
    }

    public function testFailedToGetExchangeFromWrongApi()
    {
    	$client = new MockHttpClient([
    		new MockResponse('', ['http_code' => 404])
    	]);

    	$apiClient = new ApiClient($client, $this->fileCache, 'xxx');

    	self::assertEquals($apiClient->getExchange('USD', 'JPY'), 0);
    }

    protected function setUp(): void
    {
    	$this->fileCache = $this->createMock(CacheInterface::class);
    }
}
