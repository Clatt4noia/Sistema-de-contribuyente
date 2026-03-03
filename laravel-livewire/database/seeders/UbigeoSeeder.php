<?php

namespace Database\Seeders;

use App\Models\Ubigeo;
use Illuminate\Database\Seeder;

class UbigeoSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/ubigeos_peru_full.json');
        
        if (!file_exists($jsonPath)) {
            $this->command->warn("Changes skipped: $jsonPath not found.");
            return;
        }

        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
             $this->command->error("JSON Error: " . json_last_error_msg());
             return;
        }

        foreach ($data as $item) {
             Ubigeo::updateOrCreate(
                ['code' => $item['code']],
                [
                    'department' => $item['department'], 
                    'province' => $item['province'], 
                    'district' => $item['district']
                ]
            );
        }
    }
}
