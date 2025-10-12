<?php
namespace App\Service;

use Predis\Client;

class ExchangeCache
{
	private Client $redis;
	private int $ttl;

	public function __construct(Client $redis, int $ttl)
	{
		$this->redis = $redis;
		$this->ttl = $ttl;
	}

	public function save(string $base, string $target, float $exchange): float
	{
		$this->redis->set($base . '_' . $target, $exchange, 'EX', $this->ttl);
		return $exchange;
	}

	public function get(string $base, string $target): ?float
	{
		return $this->redis->get($base . '_' . $target);
	}
}
