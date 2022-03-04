<?php

use Tests\Support\Rosters\FruitRoster;
use Tests\Support\RosterTestCase;

/**
 * @internal
 */
final class BaseRosterTest extends RosterTestCase
{
    private FruitRoster $roster;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roster = new FruitRoster();
    }

    /**
     * @dataProvider invalidArgumentProvider
     *
     * @param mixed $input
     */
    public function testGetInvalidArgument($input)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('ID must be an integer or non-empty string.');

        $this->roster->get($input);
    }

    public function invalidArgumentProvider()
    {
        return [
            [null],
            [new stdClass()],
            [0.42],
            [''],
        ];
    }

    public function testGetBuildsStore()
    {
        $store   = $this->getBaseRosterProperty($this->roster, 'store');
        $updated = $this->getBaseRosterProperty($this->roster, 'updated');
        $this->assertNull($store);
        $this->assertFalse($updated);

        $this->roster->get(1);

        $store   = $this->getBaseRosterProperty($this->roster, 'store');
        $updated = $this->getBaseRosterProperty($this->roster, 'updated');
        $this->assertNotNull($store);
        $this->assertTrue($updated);
    }

    public function testGetUsesCache()
    {
        cache()->save('roster-fruits', [
            'test' => 'store',
        ]);

        $result = $this->roster->get('test');

        $this->assertSame('store', $result);
        $this->assertFalse($this->roster->didFetchAll);
    }

    public function testGetFallsBack()
    {
        $result = $this->roster->get(42);

        $this->assertSame('kumquat', $result);
        $this->assertTrue($this->roster->didFetch);
    }

    public function testGetNotFound()
    {
        $result = $this->roster->get(43);

        $this->assertSame('', $result);
        $this->assertTrue($this->roster->didFetch);
        $this->assertLogged('warning', 'Roster request for missing ID: 43');
    }

    public function testSkipsCache()
    {
        $this->roster->cache();

        $this->assertNull(cache('roster-fruits'));
    }

    public function testStoresCache()
    {
        $this->roster->get(1);
        $this->roster->cache();

        $updated = $this->getBaseRosterProperty($this->roster, 'updated');
        $this->assertTrue($updated);

        $this->assertSame($this->roster->data, cache('roster-fruits'));
    }
}
