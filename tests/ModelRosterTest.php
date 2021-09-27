<?php

use CodeIgniter\Test\DatabaseTestTrait;
use Tatter\Roster\ModelRoster;
use Tests\Support\Models\ColorModel;
use Tests\Support\RosterTestCase;

/**
 * @internal
 */
final class ModelRosterTest extends RosterTestCase
{
	use DatabaseTestTrait;

	/**
	 * @var ModelRoster
	 */
	private $roster;

	protected function setUp(): void
	{
		parent::setUp();

		$this->roster         = new class() extends ModelRoster {
			protected $modelName = ColorModel::class;

			protected $field = 'name';
		};
	}

	public function testMissingModel()
	{
		$this->expectException('UnexpectedValueException');
		$this->expectExceptionMessage('You must set the model name.');

		new class() extends ModelRoster {};
	}

	public function testInvalidModel()
	{
		$this->expectException('UnexpectedValueException');
		$this->expectExceptionMessage('Not a known model: Foo');

		new class() extends ModelRoster {
			protected $modelName = 'Foo';
		};
	}

	public function testMissingField()
	{
		/** @var stdClass $color */
		$color                = fake(ColorModel::class);
		$roster               = new class() extends ModelRoster {
			protected $modelName = ColorModel::class;
		};

		$this->expectException('UnexpectedValueException');
		$this->expectExceptionMessage('You must set the target field, or override the "fetch" methods.');

		$roster->get($color->id);
	}

	public function testFetchAll()
	{
		/** @var stdClass $color */
		$color = fake(ColorModel::class);

		$result = $this->roster->get($color->id);

		$this->assertSame($color->name, $result);
	}

	public function testFetchMiss()
	{
		/** @var stdClass $color */
		$color = fake(ColorModel::class);
		$this->roster->get($color->id);

		$result = $this->roster->get('becauseimhappy');

		$this->assertSame('', $result);
		$this->assertLogged('warning', 'Roster request for missing ID: becauseimhappy');
	}

	public function testFetch()
	{
		/** @var stdClass $color */
		$color = fake(ColorModel::class);
		$this->roster->get($color->id);

		/** @var stdClass $color2 */
		$color2 = fake(ColorModel::class);
		$result = $this->roster->get($color2->id);

		$this->assertSame($color2->name, $result);
	}
}
