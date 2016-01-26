<?php

namespace BackBee\Installer;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class RequirementBag
{
    private $title;
    private $requirements = [];

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function addRequirement(Requirement $requirement)
    {
        $this->requirements[] = $requirement;

        return $this;
    }

    public function clearRequirements()
    {
        $this->requirements = [];

        return $this;
    }

    public function getRequirements()
    {
        return $this->requirements;
    }

    public function isOk()
    {
        foreach ($this->getRequirements() as $requirement) {
            if (!$requirement->isOk()) {
                return false;
            }
        }

        return true;
    }
}
