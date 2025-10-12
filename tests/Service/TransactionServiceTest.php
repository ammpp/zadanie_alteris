<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\ExchangeCache;
use App\Repository\TransactionsRepository;
use App\Service\TransactionService;
use App\Service\ApiClient;
use App\Entity\Transactions;

class TransactionServiceTest extends KernelTestCase
{
    public function testCreateTransaction(): void
    {
    	$apiClient = $this->createMock(ApiClient::class);
    	$exchangeCache = $this->createMock(ExchangeCache::class);
    	$transactionsRepository = static::getContainer()->get(TransactionsRepository::class);

    	$exchangeCache
	    	->expects($this->once())
	    	->method('get')
	    	->with('PLN', 'EUR')
	    	->willReturn(0.813399);
	    $apiClient
	    	->expects($this->never())
	    	->method('getExchange');

    	$transactionService = new TransactionService($apiClient, $exchangeCache, $transactionsRepository);

    	$transaction = $transactionService->createTransaction('bank', false, 100, 'PLN', 'EUR', '127.0.0.1');

    	self::assertTrue($transaction instanceof Transactions);
    	self::assertEquals($transaction->getExchangeRate(), 0.813399);
    	self::assertEquals($transaction->getTargetAmount(), 81.3399);

    	$transactionDirect = $transactionsRepository->find($transaction->getId());

    	self::assertEquals($transaction, $transactionDirect);
    }

    public function testCreateTransactionWithoutCache(): void
    {
    	$apiClient = $this->createMock(ApiClient::class);
    	$exchangeCache = $this->createMock(ExchangeCache::class);
    	$transactionsRepository = static::getContainer()->get(TransactionsRepository::class);

    	$exchangeCache
	    	->expects($this->once())
	    	->method('get')
	    	->with('PLN', 'EUR')
	    	->willReturn(0.0);
	   	$exchangeCache
	    	->expects($this->once())
	    	->method('save')
	    	->with('PLN', 'EUR', 0.777)
	    	->willReturn(0.777);
    	$apiClient
	    	->expects($this->once())
	    	->method('getExchange')
	    	->with('PLN', 'EUR')
	    	->willReturn(0.777);

    	$transactionService = new TransactionService($apiClient, $exchangeCache, $transactionsRepository);

    	$transaction = $transactionService->createTransaction('bank', false, 100, 'PLN', 'EUR', '127.0.0.1');

    	self::assertTrue($transaction instanceof Transactions);
    	self::assertEquals($transaction->getExchangeRate(), 0.777);
    	self::assertEquals($transaction->getTargetAmount(), 77.7);

    	$transactionDirect = $transactionsRepository->find($transaction->getId());

    	self::assertEquals($transaction, $transactionDirect);
    }

    public function testFailedToCreateTransactionDueToExchange(): void
    {
    	$apiClient = $this->createMock(ApiClient::class);
    	$exchangeCache = $this->createMock(ExchangeCache::class);
    	$transactionsRepository = static::getContainer()->get(TransactionsRepository::class);

    	$exchangeCache
	    	->expects($this->once())
	    	->method('get')
	    	->with('PLN', 'EUR')
	    	->willReturn(0.0);

    	$transactionService = new TransactionService($apiClient, $exchangeCache, $transactionsRepository);

    	$transaction = $transactionService->createTransaction('bank', false, 100, 'PLN', 'EUR', '127.0.0.1');

    	self::assertEquals($transaction, null);
    }

    public function testEditTransaction(): void
    {
    	$apiClient = $this->createMock(ApiClient::class);
    	$exchangeCache = $this->createMock(ExchangeCache::class);
    	$transactionsRepository = static::getContainer()->get(TransactionsRepository::class);

    	$exchangeCache
	    	->expects($this->once())
	    	->method('get')
	    	->with('USD', 'PLN')
	    	->willReturn(0.813399);

    	$transaction = $this->createTransaction($transactionsRepository);

    	$transactionService = new TransactionService($apiClient, $exchangeCache, $transactionsRepository);

    	$correctedTransaction = $transactionService->editTransaction($transaction->getId(), 'PLN');

    	self::assertTrue($correctedTransaction instanceof Transactions);
    	self::assertEquals($correctedTransaction->getExchangeRate(), 0.813399);
    	self::assertEquals($correctedTransaction->getTargetAmount(), 162.6798);

    	$transactionDirect = $transactionsRepository->find($correctedTransaction->getId());

    	self::assertEquals($correctedTransaction, $transactionDirect);
    }

    public function testDoNotEditTransactionWhenCurrencyTheSame(): void
    {
    	$apiClient = $this->createMock(ApiClient::class);
    	$exchangeCache = $this->createMock(ExchangeCache::class);
    	$transactionsRepository = static::getContainer()->get(TransactionsRepository::class);

    	$exchangeCache
	    	->expects($this->never())
	    	->method('get');

    	$transaction = $this->createTransaction($transactionsRepository);

    	$transactionService = new TransactionService($apiClient, $exchangeCache, $transactionsRepository);

    	$correctedTransaction = $transactionService->editTransaction($transaction->getId(), 'EUR');

    	self::assertTrue($correctedTransaction instanceof Transactions);
    	self::assertEquals($correctedTransaction->getExchangeRate(), 2);
    	self::assertEquals($correctedTransaction->getTargetAmount(), 400);
    }

    public function testDoNotEditTransactionWhenCurrencyNotRecognized(): void
    {
    	$apiClient = $this->createMock(ApiClient::class);
    	$exchangeCache = $this->createMock(ExchangeCache::class);
    	$transactionsRepository = static::getContainer()->get(TransactionsRepository::class);

    	$exchangeCache
	    	->expects($this->once())
	    	->method('get')
	    	->with('USD', 'KPW')
	    	->willReturn(0.0);

    	$transaction = $this->createTransaction($transactionsRepository);

    	$transactionService = new TransactionService($apiClient, $exchangeCache, $transactionsRepository);

    	$correctedTransaction = $transactionService->editTransaction($transaction->getId(), 'KPW');

    	self::assertNull($correctedTransaction);
    }

    public function testTryToEditInexistingTransaction(): void
    {
    	$apiClient = $this->createMock(ApiClient::class);
    	$exchangeCache = $this->createMock(ExchangeCache::class);
    	$transactionsRepository = static::getContainer()->get(TransactionsRepository::class);

    	$exchangeCache
	    	->expects($this->never())
	    	->method('get');

    	$transaction = $this->createTransaction($transactionsRepository);

    	$transactionService = new TransactionService($apiClient, $exchangeCache, $transactionsRepository);

    	$correctedTransaction = $transactionService->editTransaction($transaction->getId() + 1, 'PLN');

    	self::assertNull($correctedTransaction);
    }

    public function testDeleteTransaction(): void
    {
    	$apiClient = $this->createMock(ApiClient::class);
    	$exchangeCache = $this->createMock(ExchangeCache::class);
    	$transactionsRepository = static::getContainer()->get(TransactionsRepository::class);

    	$transaction = $this->createTransaction($transactionsRepository);
    	$id = $transaction->getId();

    	$transactionService = new TransactionService($apiClient, $exchangeCache, $transactionsRepository);

    	$deleteResponse = $transactionService->deleteTransaction($id);

    	self::assertTrue($deleteResponse);

    	$transactionDirect = $transactionsRepository->find($id);

    	self::assertNull($transactionDirect);
    }

    public function testTryToDeleteInexistingTransaction(): void
    {
    	$apiClient = $this->createMock(ApiClient::class);
    	$exchangeCache = $this->createMock(ExchangeCache::class);
    	$transactionsRepository = static::getContainer()->get(TransactionsRepository::class);

    	$transaction = $this->createTransaction($transactionsRepository);

    	$transactionService = new TransactionService($apiClient, $exchangeCache, $transactionsRepository);

    	$deleteResponse = $transactionService->deleteTransaction($transaction->getId() + 1);

    	self::assertFalse($deleteResponse);
    }

    private function createTransaction(TransactionsRepository $transactionsRepository): Transactions
    {
    	$transaction = (new Transactions())
	    	->setPaymentMethod('something')
	    	->setTransactionDeposit(true)
	    	->setTimestamp(time())
	    	->setBaseAmount(200)
	    	->setBaseCurrency('USD')
	    	->setTargetAmount(400)
	    	->setTargetCurrency('EUR')
	    	->setExchangeRate(2)
	    	->setClientIp('127.0.0.1');
	   	$transactionsRepository->save($transaction);
	   	return $transaction;
    }
}
