<?php

namespace setasign\tests;

use setasign\SetaFpdf\SetaFpdf;

/**
 * Class TestProxy
 *
 * @mixin \setasign\Fpdi\Fpdi
 */
class TestProxy extends \PHPUnit\Framework\TestCase
{
    private $instances;

    public function __construct($instances)
    {
        $this->instances = $instances;
        parent::__construct();
    }

    /**
     * @param $name
     * @param $arguments
     * @return \stdClass|mixed
     * @throws \RuntimeException
     */
    public function __call($name, $arguments)
    {
        return $this->assertResult(function ($instance) use ($name, $arguments) {
            return call_user_func_array([$instance, $name], $arguments);//->{$name}(...$arguments);
        }, 'Method: ' . $name);
    }

    /**
     * @param $name
     * @return \stdClass|mixed
     * @throws \RuntimeException
     */
    public function __get($name)
    {
        return $this->assertResult(function($instance) use ($name) {
            return $instance->{$name};
        });
    }

    /** @noinspection MagicMethodsValidityInspection */
    /**
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        foreach ($this->instances as $instance) {
            $instance->{$name} = $value;
        }
    }

    /**
     * @param callback $callback
     * @return mixed
     * @throws \RuntimeException
     * @throws \Throwable
     */
    protected function assertResult($callback, $method)
    {
        $exceptionThrown = false;

        $empty = new \stdClass();
        $result = $empty;

        foreach ($this->instances as $instance) {
            try {
                $currentResult = call_user_func($callback, $instance);
            } catch (\Throwable $e) {
                $exceptionThrown = true;
                $currentResult = $e;
            }

            if ($result === $empty) {
                $result = $currentResult;
                continue;
            }

            if (
                (is_object($currentResult) && $currentResult instanceof \Throwable) ||
                (is_object($result) && $result instanceof \Throwable)
            ) {
                $this->assertInstanceOf(\Exception::class, $currentResult, get_class($instance));
                $this->assertInstanceOf(\Exception::class, $result, 'Compare');
            } else {
                $this->assertEquals($result, $currentResult, $method);
            }
        }

        if ($exceptionThrown === true) {
            throw $result;
        }

        return $result;
    }

    /**
     * @return \FPDF[]|SetaFpdf[]
     */
    public function getInstances()
    {
        return $this->instances;
    }
}