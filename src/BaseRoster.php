<?php

namespace Tatter\Roster;

use InvalidArgumentException;

/**
 * Abstract Roster Class
 *
 * Central class for use by the library.
 */
abstract class BaseRoster
{
    /**
     * How long to cache this handler's store.
     *
     * @var int
     */
    protected $ttl = DAY;

    /**
     * Stored hash of IDs and their name.
     *
     * @var array<int|string, string>|null
     */
    private ?array $store = null;

    /**
     * Whether the store has been updated since its last cache.
     */
    private bool $updated = false;

    /**
     * Returns the display name for the given ID.
     *
     * @param int|string $id
     */
    final public function get($id): string
    {
        if (! (is_int($id) || is_string($id)) || $id === '') { // @phpstan-ignore-line
            throw new InvalidArgumentException('ID must be an integer or non-empty string.');
        }

        if ($this->store === null) {
            $this->build();
        }

        if (! isset($this->store[$id])) {
            // Not found! Fall back to the data source
            if (null === $name = $this->fetch($id)) {
                log_message('warning', 'Roster request for missing ID: ' . $id);

                return '';
            }

            $this->store[$id] = $name;
            $this->updated    = true;
        }

        return $this->store[$id];
    }

    /**
     * Caches the store. Usually called by Roster
     * when the post_system Event is triggered.
     */
    final public function cache(): void
    {
        if (! $this->updated) {
            return;
        }

        cache()->save($this->key(), $this->store, $this->ttl);
    }

    /**
     * Builds the storage.
     */
    private function build(): void
    {
        // Check the Cache first
        if (null !== $this->store = cache($this->key())) {
            return;
        }

        $this->store   = $this->fetchAll();
        $this->updated = true;
    }

    /**
     * Returns the handler-specific identifier used for caching
     * E.g. "roster-users"
     */
    abstract protected function key(): string;

    /**
     * Loads all IDs and their names from the data source.
     *
     * @return array<int|string, string> as [ID => name]
     */
    abstract protected function fetchAll(): array;

    /**
     * Loads a single ID and name from the data source.
     * Used as a fallback for the rare case when an ID
     * is missing from the store.
     *
     * @param int|string $id
     *
     * @return string|null Null for "ID not found"
     */
    abstract protected function fetch($id): ?string;
}
