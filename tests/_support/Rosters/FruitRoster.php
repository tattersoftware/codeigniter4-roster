<?php

namespace Tests\Support\Rosters;

use Tatter\Roster\BaseRoster;

class FruitRoster extends BaseRoster
{
	private $data = [
		1 => 'banana',
		2 => 'mango',
		9 => 'apple',
	];

	/**
	 * Returns the handler-specific identifier used for caching
	 * E.g. "roster-users"
	 */
	protected function key(): string
	{
		return 'roster-fruits';
	}

    /**
     * Loads all IDs and their names from the data source.
     *
     * @return array<int|string, string> as [ID => name]
     */
    protected function fetchAll(): array
    {
    	return $this->data;
    }

    /**
     * Loads a single ID and name from the data source.
     * Used as a fallback for the rare case when an ID
     * is missing from the store.
     *
     * @param int|string $id
     *
     * @return string|null Null for "ID not found"
     */
    protected function fetch($id): ?string
    {
    	return $this->data[$id] ?? null;
    }
}
