<?php

namespace Tatter\Roster\Config;

use CodeIgniter\Config\BaseService;
use Tatter\Roster\Roster;

class Services extends BaseService
{
    /**
     * Creates a Roster for bulk handling of display name lookup.
     */
    public static function roster(bool $getShared = true): Roster
    {
        if ($getShared) {
            return static::getSharedInstance('roster');
        }

        return new Roster();
    }
}
