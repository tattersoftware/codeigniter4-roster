<?php

use Tatter\Roster\BaseRoster;
use Tatter\Roster\Roster;
use Tests\Support\Rosters\FruitRoster;
use Tests\Support\RosterTestCase;

class LibraryTest extends RosterTestCase
{
	public function testService()
	{
		$result = service('roster');

		$this->assertInstanceOf(Roster::class, $result);
	}

	public function testSetHandler()
	{
		$handler = new FruitRoster;
		$roster  = service('roster');

		$roster->setHandler('foo', $handler);

		$this->assertInstanceOf(FruitRoster::class, $roster->getHandler('foo'));
	}

	public function testGetHandlerDiscovers()
	{
		$result = service('roster')->getHandler('fruit');

		$this->assertInstanceOf(FruitRoster::class, $result);
	}

	public function testCallTooFewParams()
	{
		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage('Roster::fruit() expects a single parameter.');

		service('roster')->fruit();
	}

	public function testCallTooManyParams()
	{
		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage('Roster::fruit() expects a single parameter.');

		service('roster')->fruit(1, 2);
	}

	public function testCallUnknownHandler()
	{
		$this->expectException('BadFunctionCallException');
		$this->expectExceptionMessage('Unknown Roster handler "veggie".');

		service('roster')->veggie(1);
	}
}
