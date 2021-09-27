<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use Nexus\PHPUnit\Extension\Expeditable;
use ReflectionObject;
use RuntimeException;
use Tatter\Roster\BaseRoster;

/**
 * @internal
 */
abstract class RosterTestCase extends CIUnitTestCase
{
    use Expeditable;

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();
    }

    /**
     * Extracts private properties buried in a Roster.
     *
     * @return mixed|null
     */
    protected function getBaseRosterProperty(BaseRoster $roster, string $name)
    {
		$class = new ReflectionObject($roster);

		while ($class && $class->getName() !== BaseRoster::class) {
			$class = $class->getParentClass();
		}

		if ($class === false) {
			throw new RuntimeException('Unable to locate BaseRoster.');
		}

		if (! $class->hasProperty($name)) {
			throw new RuntimeException('Unable to locate ' . $name . '.');
		}

        $property = $class->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($roster);
    }
}
