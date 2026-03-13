<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PharmacyMedicineAttributes extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        $definitions = [
            ['definition_name' => 'Generic Name', 'definition_type' => TEXT, 'definition_flags' => 1, 'definition_unit' => null, 'deleted' => 0],
            ['definition_name' => 'Dosage Form', 'definition_type' => DROPDOWN, 'definition_flags' => 1, 'definition_unit' => null, 'deleted' => 0],
            ['definition_name' => 'Strength', 'definition_type' => TEXT, 'definition_flags' => 1, 'definition_unit' => null, 'deleted' => 0],
            ['definition_name' => 'Batch Number', 'definition_type' => TEXT, 'definition_flags' => 5, 'definition_unit' => null, 'deleted' => 0],
            ['definition_name' => 'Expiry Date', 'definition_type' => DATE, 'definition_flags' => 5, 'definition_unit' => null, 'deleted' => 0],
            ['definition_name' => 'Requires Prescription', 'definition_type' => CHECKBOX, 'definition_flags' => 1, 'definition_unit' => null, 'deleted' => 0]
        ];

        foreach ($definitions as $definition) {
            $builder = $this->db->table('attribute_definitions');
            $builder->where('definition_name', $definition['definition_name']);
            if ($builder->countAllResults() === 0) {
                $this->db->table('attribute_definitions')->insert($definition);
            }
        }
    }

    /**
     * Revert a migration step.
     */
    public function down(): void
    {
    }
}
