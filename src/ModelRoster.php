<?php

namespace Tatter\Roster;

use CodeIgniter\Model;
use UnexpectedValueException;

/**
 * Abstract Model Roster
 *
 * Support class to build a Roster from a Model.
 */
abstract class ModelRoster extends BaseRoster
{
	/**
	 * Name of the Model to use.
	 * Can be anything that `model()` accepts.
	 *
	 * @var string
	 */
	protected $modelName;

	/**
	 * Which field contains the target display name.
	 * Alternatively override getFieldValue();
	 *
	 * @var string|null
	 */
	protected $field;

	/**
	 * @var Model
	 */
	private $model;

    /**
     * Validates the Model.
     *
     * @throws UnexpectedValueException
     */
    public function __construct()
    {
    	if (! is_string($this->modelName) || $this->modelName === '')
    	{
    		throw new UnexpectedValueException('You must set the model name.');
    	}

		if (! $this->model = model($this->modelName)) {
    		throw new UnexpectedValueException('Not a known model: ' . $this->modelName);
		}
    }

    /**
     * Returns the value of the target field from the data row.
     */
    protected function getFieldValue(array $row): string
    {
    	if (! is_string($this->field) || $this->field === '')
    	{
    		throw new UnexpectedValueException('You must set the target field, or override the "fetch" methods.');
		}

		return $row[$this->field];
    }

	/**
	 * Returns the handler-specific identifier used for caching
	 * E.g. "roster-users"
	 */
	protected function key(): string
	{
		return 'roster-' . $this->model->table; // @phpstan-ignore-line
	}

    /**
     * Loads all IDs and their names from the data source.
     *
     * @return array<int|string, string> as [ID => name]
     */
    protected function fetchAll(): array
    {
    	$results = [];

		foreach ($this->model->findAll() as $row)
		{
			if (! is_array($row)) {
				$row = (array) $row;
			}

			$id   = $row[$this->model->primaryKey]; // @phpstan-ignore-line
			$name = $this->getFieldValue($row);

			$results[$id] = $name;
		}

		return $results;
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
    	if (! $row = $this->model->withDeleted()->find($id))
    	{
    		return null;
    	}

		if (! is_array($row)) {
			$row = (array) $row;
		}

		return $this->getFieldValue($row);
    }
}
