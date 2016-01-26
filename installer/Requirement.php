<?php

namespace BackBee\Installer;

/**
 * @author Eric Chau <eric.chau@lp-digital.fr>
 */
class Requirement
{
    /**
     * @var mixed
     */
    private $expected;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $title;

    public function __construct($expected, $value, $title)
    {
        $this->expected = $expected;
        $this->value = $value;
        $this->title = $title;
    }

    /**
     * @return boolean
     */
    public function isOk()
    {
        return $this->expected === $this->value;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
