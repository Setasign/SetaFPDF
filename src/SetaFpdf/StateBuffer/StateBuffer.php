<?php
/**
 * This file is part of SetaFPDF
 *
 * @package   setasign\SetaFpdf
 * @copyright Copyright (c) 2018 Setasign - Jan Slabon (https://www.setasign.com)
 * @author    Timo Scholz <timo.scholz@setasign.com>
 * @author    Jan Slabon <jan.slabon@setasign.com>
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\SetaFpdf\StateBuffer;

abstract class StateBuffer implements StateBufferInterface
{
    /**
     * The states.
     *
     * @var array
     */
    private $states = [];

    /**
     * The stored state.
     *
     * @var array|null
     */
    private $storedStates = [];

    /**
     * The state callbacks.
     *
     * @var array
     */
    private $callbacks = [];

    /**
     * StateBuffer constructor.
     *
     * @param array $callbacks
     */
    public function __construct($callbacks)
    {
        foreach ($callbacks as $name => $callback) {
            $this->states[$name]['value'] = null;
            $this->states[$name]['newValue'] = null;
            $this->callbacks['ensure' . ucfirst($name)] = [
                'name' => $name,
                'callback' => $callback
            ];
        }
    }

    /**
     * Get the current value for the state.
     *
     * @param string $name
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __get($name)
    {
        if (!isset($this->states[$name])) {
            throw new \BadMethodCallException(sprintf('Unknown state name "%s".', $name));
        }

        return $this->states[$name]['value'];
    }

    /**
     * Check if the this instance contains a specific state.
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->states[$name]);
    }

    /**
     * Set the new value for the state.
     *
     * @param string $name
     * @param mixed $value
     * @throws \BadMethodCallException
     */
    public function __set($name, $value)
    {
        if (!isset($this->states[$name])) {
            throw new \BadMethodCallException(sprintf('Unknown state name "%s".', $name));
        }

        if ($this->states[$name]['value'] !== $value) {
            $this->states[$name]['newValue'] = $value;
        }
    }

    /**
     * Call the ensure method for the state.
     *
     * @param $callName
     * @param $arguments
     * @throws \BadMethodCallException
     */
    public function __call($callName, $arguments)
    {
        if (!isset($this->callbacks[$callName])) {
            throw new \BadMethodCallException(sprintf('Unknown callback name "%s".', $callName));
        }

        /**
         * @var $callback callable
         * @var $name string
         */
        extract($this->callbacks[$callName], EXTR_OVERWRITE);

        if ($this->states[$name]['value'] !== $this->states[$name]['newValue']) {
            $callback($this->states[$name]['newValue']);
            $this->states[$name]['value'] = $this->states[$name]['newValue'];
        }
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
        foreach ($this->states as &$state) {
            $state['value'] = null;
        }
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        $states = $this->states;
        $this->reset();
        $this->storedStates = $this->states;
        $this->states = $states;
    }

    /**
     * @inheritdoc
     */
    public function restore()
    {
        if ($this->storedStates === null) {
            throw new \BadMethodCallException('There are no states stored.');
        }

        $this->states = $this->storedStates;
    }
}