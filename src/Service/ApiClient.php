<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ApiClient
{
	const API_DOMAIN = 'http://api.exchangeratesapi.io/v1/';

	private HttpClientInterface $client;
	private CacheInterface $fileCache;
	private string $apiKey;

	public function __construct(HttpClientInterface $client, CacheInterface $fileCache, string $apiKey)
	{
		$this->client = $client;
		$this->fileCache = $fileCache;
		$this->apiKey = $apiKey;
	}

	public function getSymbols(): string
	{
		$symbols = $this->fileCache->get('currenciesSymbols', [$this, 'readSymbols']);
		if ($symbols) {
			return $symbols;
		}

		$this->fileCache->delete('currenciesSymbols');
		return '{}';
	}

	public function readSymbols(): ?string {
		$response = $this->client->request(
			'GET',
			self::API_DOMAIN . 'symbols',
			['query' => [
				'access_key' => $this->apiKey
			]]
		);
		return $response->getStatusCode() == 200
			? $response->getContent() : null;
	}

	public function getExchange(string $base, string $target): float
	{
		$response = json_decode($this->client->request(
			'GET',
			self::API_DOMAIN . 'latest',
			['query' => [
				'access_key' => $this->apiKey,
				'base' => $base,
				'symbols' => $target
			]]
		)->getContent(false));

		$rates = isset($response->rates) ? get_object_vars($response->rates) : [];
		foreach ($rates as $currency => $rate) {
			if ($currency == $target) {
				return $rate;
			}
		}

		return 0;
	}
}
