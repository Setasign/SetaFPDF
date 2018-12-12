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
     * @return mixed
     * @throws \Throwable
     */
    public function __call($name, $arguments)
    {
        return $this->assertResult(function ($instance) use ($name, $arguments) {
            return call_user_func_array([$instance, $name], $arguments);
        }, '__call: ' . $name);
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Throwable
     */
    public function __get($name)
    {
        return $this->assertResult(function ($instance) use ($name) {
            return $instance->{$name};
        }, '__get: ' . $name);
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
     * @param callable $callback
     * @return mixed
     * @throws \RuntimeException
     * @throws \Throwable
     */
    protected function assertResult($callback, $method)
    {
        $results = array_map(function ($instance) use ($callback) {
            try {
                return \call_user_func($callback, $instance);
            // required for php5.6
            } catch (\Exception $e) {
                return $e;
            } catch (\Throwable $e) {
                return $e;
            }
        }, $this->instances);

        if (count($results) === 0) {
            throw new \BadMethodCallException('No result found! Probably because of no configured instances.');
        }

        $isFirst = true;
        $expectedResult = null;
        foreach ($results as $result) {
            if ($isFirst) {
                $expectedResult = $result;
                $isFirst = false;
                continue;
            }

            if ((is_object($result) && (
                    $result instanceof \Exception || $result instanceof \Throwable
                ))
                || (is_object($expectedResult) && (
                    $expectedResult instanceof \Exception || $expectedResult instanceof \Throwable
                ))
            ) {
                $this->assertInstanceOf(\Exception::class, $result, get_class($result));
                $this->assertInstanceOf(
                    \Exception::class,
                    $expectedResult,
                    'Check whether last result was also an exception'
                );
            } else {
                $this->assertEquals($expectedResult, $result, 'Different result: '. var_export($method, true));
            }
        }

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($expectedResult instanceof \Exception || $expectedResult instanceof \Throwable) {
            throw $expectedResult;
        }

        return $expectedResult;
    }

    /**
     * @return \FPDF[]|SetaFpdf[]
     */
    public function getInstances()
    {
        return $this->instances;
    }
}
