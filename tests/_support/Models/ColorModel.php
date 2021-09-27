<?php

namespace Tests\Support\Models;

use CodeIgniter\Model;
use Faker\Generator;

class ColorModel extends Model
{
    protected $table         = 'colors';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
    	'name',
    	'hex',
    ];

    protected $validationRules = [
        'name' => 'required|max_length[255]',
        'hex'  => 'permit_empty|max_length[7]',
    ];

    /**
     * Faked data for Fabricator.
     */
    public function fake(Generator &$faker): array
    {
        return [
            'name' => $faker->safeColorName,
            'hex'  => $faker->hexColor,
        ];
    }
}
