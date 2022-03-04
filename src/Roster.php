<?php

namespace Tatter\Roster;

use BadFunctionCallException;
use BadMethodCallException;
use CodeIgniter\Config\Factories;
use CodeIgniter\Events\Events;

/**
 * Roster Library
 *
 * Centralized access to bulk lists of entity names.
 */
final class Roster
{
    /**
     * Store of loaded handlers.
     *
     * @var array<string, BaseRoster>
     */
    private array $handlers = [];

    /**
     * Registers the post-system Event to commit final
     * store values to the cache.
     */
    public function __construct()
    {
    	Events::on('post_system', [$this, 'commit']);
    }

    /**
     * Passes requests to the appropriate handler.
     *
     * @param string            $shortname The class name to pass to Factories
     * @param array<int|string> $params    A single row identifier
     *
     * @throws BadFunctionCallException
     * @throws BadMethodCallException
     */
    public function __call(string $shortname, array $params): string
    {
    	if (count($params) !== 1)
    	{
    		throw new BadMethodCallException('Roster::' . $shortname . '() expects a single parameter.');
    	}

    	$id = reset($params);
    	unset($params);

		if (null === $handler = $this->getHandler($shortname))
		{
			throw new BadFunctionCallException('Unknown Roster handler "' . $shortname . '".');
		}

		return $handler->get($id);
    }

    /**
     * Locates the correct handler to use.
     */
    public function getHandler(string $shortname): ?BaseRoster
    {
        if (! array_key_exists($shortname, $this->handlers))
        {
        	$class = ucfirst($shortname) . 'Roster';

        	$this->handlers[$shortname] = Factories::rosters($class);
        }

       	return $this->handlers[$shortname];
    }

    /**
     * Sets a handler directly (mostly for testing).
     */
    public function setHandler(string $shortname, ?BaseRoster $handler): self
    {
       	$this->handlers[$shortname] = $handler;

       	return $this;
    }

    /**
     * Instructs each handler to commit any changed stores to the Cache.
     */
    public function commit(): void
    {
    	foreach ($this->handlers as $handler)
    	{
    		$handler->cache();
    	}
    }
}
