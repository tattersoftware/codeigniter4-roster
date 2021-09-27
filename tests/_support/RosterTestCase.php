<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use Nexus\PHPUnit\Extension\Expeditable;

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
}
