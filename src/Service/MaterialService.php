<?php
namespace App\Service;

use App\Repository\MaterialRepository;
use App\Entity\Material;

class MaterialService
{
	private ApiClient $client;

	private ExchangeCache $cache;

	private MaterialRepository $transactionsRepository;

	public function __construct
	(
		ApiClient $client,
		ExchangeCache $cache,
	    MaterialRepository $transactionsRepository
	) {
		$this->client = $client;
		$this->cache = $cache;
		$this->transactionsRepository = $transactionsRepository;
	}

	public function getTransactions(?int $limit = 20, ?int $offset = 0)
	{
		return $this->transactionsRepository->findBy([], ['timestamp' => 'ASC'], $limit, $offset);
	}

	public function createTransaction(
		string $method,
		bool $isDeposit,
		float $amount,
		string $baseCurrency,
		string $targetCurrency,
		string $ip
	): ?Transactions {
		$baseCurrency = strtoupper($baseCurrency);
		$targetCurrency = strtoupper($targetCurrency);
		$exchangeRate = $this->getExchangeRate($baseCurrency, $targetCurrency);

		if (!$exchangeRate) {
			return null;
		}

		$transaction = (new Transactions())
			->setPaymentMethod($method)
			->setTransactionDeposit($isDeposit)
			->setTimestamp(time())
			->setBaseAmount($amount)
			->setBaseCurrency($baseCurrency)
			->setTargetAmount($amount * $exchangeRate)
			->setTargetCurrency($targetCurrency)
			->setExchangeRate($exchangeRate)
			->setClientIp($ip);

		$this->transactionsRepository->save($transaction);

		return $transaction;
	}

	public function editTransaction(int $id, string $targetCurrency): ?Transactions
	{
		/** @var Transactions $transaction */
		$transaction = $this->transactionsRepository->find($id);

		if ($transaction) {
			$targetCurrency = strtoupper($targetCurrency);
			if ($targetCurrency != $transaction->getTargetCurrency()) {
				$exchangeRate = $this->getExchangeRate($transaction->getBaseCurrency(), $targetCurrency);

				if (!$exchangeRate) {
					return null;
				}

				$transaction
					->setTargetAmount($transaction->getBaseAmount() * $exchangeRate)
					->setTargetCurrency($targetCurrency)
					->setExchangeRate($exchangeRate);

				$this->transactionsRepository->save($transaction);
			}

			return $transaction;
		}

		return null;
	}

	public function deleteTransaction(int $id): bool
	{
		/** @var Transactions $transaction */
		$transaction = $this->transactionsRepository->find($id);

		if ($transaction) {
			$this->transactionsRepository->remove($transaction);

			return true;
		}

		return false;
	}

	private function getExchangeRate(string $baseCurrency, string $targetCurrency)
	{
		return $this->cache->get($baseCurrency, $targetCurrency) ?:
			$this->cache->save(
				$baseCurrency,
				$targetCurrency,
				$this->client->getExchange($baseCurrency, $targetCurrency)
			);
	}
}
