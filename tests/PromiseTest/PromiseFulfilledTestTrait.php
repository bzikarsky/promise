<?php

namespace React\Promise\PromiseTest;

trait PromiseFulfilledTestTrait
{
    /**
     * @return \React\Promise\PromiseAdapter\PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter();

    /** @test */
    public function thenShouldForwardResultWhenCallbackIsNull()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                null,
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function thenShouldForwardCallbackResultToNextCallback()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function ($val) {
                    return $val + 1;
                },
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function thenShouldForwardPromisedCallbackResultValueToNextCallback()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function ($val) {
                    return \React\Promise\resolve($val + 1);
                },
                $this->expectCallableNever()
            )
            ->then(
                $mock,
                $this->expectCallableNever()
            );
    }

    /** @test */
    public function thenShouldSwitchFromCallbacksToErrbacksWhenCallbackReturnsARejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function ($val) {
                    return \React\Promise\reject($val + 1);
                },
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock
            );
    }

    /** @test */
    public function thenShouldSwitchFromCallbacksToErrbacksWhenCallbackThrows()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->throwException($exception));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                $mock,
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $mock2
            );
    }
}
