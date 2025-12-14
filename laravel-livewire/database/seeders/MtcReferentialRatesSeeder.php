<?php

namespace Database\Seeders;

use App\Models\MtcReferentialRate;
use Illuminate\Database\Seeder;

class MtcReferentialRatesSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Formato:
         * [route_key, origin, destination, dv_partial_km, dv_acum_km, rate_soles_per_tm]
         */
        $rows = [
            // LIMA-AGUAS-VERDES
            ['LIMA-AGUAS-VERDES', 'Lima', 'Ovalo de Chancay', 82.64, 82.64, 68.32],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Huaral', 9.00, 91.64, 69.85],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Huacho', 56.35, 147.99, 79.49],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Supe Pueblo', 38.25, 186.24, 86.02],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Pativilca', 11.20, 197.44, 87.94],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Barranca', 5.35, 202.79, 88.86],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Paramonga', 16.40, 219.19, 91.66],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Huarmey', 212.98, 432.17, 128.09],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Casma', 45.30, 477.47, 135.84],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Chimbote', 15.10, 492.57, 138.42],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Nuevo Chimbote', 10.00, 502.57, 140.13],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Pto. Samanco', 17.00, 519.57, 143.04],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Pto. Santa', 34.77, 537.34, 146.08],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Pte. Santa', 13.50, 550.84, 148.39],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Paijan', 7.63, 558.47, 149.69],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Chicama', 9.62, 568.09, 151.34],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Trujillo', 76.46, 644.55, 164.43],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Chao', 64.26, 708.81, 175.43],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Viru', 18.77, 727.58, 178.64],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Chimbote (Desvio)', 42.08, 769.66, 185.84],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Guadalupe', 109.24, 753.79, 191.62],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Pacasmayo', 22.00, 775.79, 195.39],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Chepen', 38.90, 814.69, 202.05],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Chiclayo', 0.00, 763.79, 213.28],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Lambayeque', 0.00, 780.06, 216.46],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Motupe', 97.88, 877.94, 241.74],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Olmos', 33.10, 911.04, 250.97],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Pto. Pizarro', 173.45, 1220.27, 341.80],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Sullana', 84.68, 1057.97, 295.45],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Piura', 0.00, 973.29, 271.78],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Talara', 117.62, 1090.91, 304.66],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Mancora', 99.46, 1190.37, 332.47],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Tumbes', 0.00, 1222.50, 341.45],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Zorritos', 35.00, 1257.50, 351.23],
            ['LIMA-AGUAS-VERDES', 'Lima', 'Aguas Verdes', 24.99, 1282.49, 358.12],

            // LIMA-CARAZ
            ['LIMA-CARAZ', 'Lima', 'Huaraz', 404.00, 404.00, 131.10],
            ['LIMA-CARAZ', 'Lima', 'Caraz', 68.00, 472.00, 150.00],

            // LIMA-CHURIN
            ['LIMA-CHURIN', 'Lima', 'Churin', 0.00, 197.00, 94.00],

            // LIMA-CUZCO-SANTA-TERESA
            ['LIMA-CUZCO-SANTA-TERESA', 'Lima', 'Nazca', 444.00, 444.00, 154.00],
            ['LIMA-CUZCO-SANTA-TERESA', 'Lima', 'Abancay', 377.00, 821.00, 243.00],
            ['LIMA-CUZCO-SANTA-TERESA', 'Lima', 'Cusco', 192.00, 1013.00, 301.00],
            ['LIMA-CUZCO-SANTA-TERESA', 'Lima', 'Quillabamba', 220.00, 1233.00, 366.00],
            ['LIMA-CUZCO-SANTA-TERESA', 'Lima', 'Santa Teresa', 68.00, 1301.00, 386.00],

            // LIMA-HUANCAYO-AYACUCHO
            ['LIMA-HUANCAYO-AYACUCHO', 'Lima', 'La Oroya', 0.00, 220.88, 91.93],
            ['LIMA-HUANCAYO-AYACUCHO', 'Lima', 'Huancayo', 79.00, 299.88, 105.00],
            ['LIMA-HUANCAYO-AYACUCHO', 'Lima', 'Ayacucho', 340.00, 639.88, 210.00],

            // LIMA-LA-OROYA-TARMA-LA-MERCED
            ['LIMA-LA-OROYA-TARMA-LA-MERCED', 'Lima', 'La Oroya', 220.88, 220.88, 91.93],
            ['LIMA-LA-OROYA-TARMA-LA-MERCED', 'Lima', 'Tarma', 78.06, 298.94, 105.27],
            ['LIMA-LA-OROYA-TARMA-LA-MERCED', 'Lima', 'La Merced', 80.82, 379.76, 119.08],

            // LIMA-PUCALLPA
            ['LIMA-PUCALLPA', 'Lima', 'La Oroya', 0.00, 220.88, 91.93],
            ['LIMA-PUCALLPA', 'Lima', 'Tingo Maria', 355.00, 575.88, 195.00],
            ['LIMA-PUCALLPA', 'Lima', 'Pucallpa', 260.00, 835.88, 275.00],

            // LIMA-TARAPOTO
            ['LIMA-TARAPOTO', 'Lima', 'Tingo Maria', 0.00, 575.88, 195.00],
            ['LIMA-TARAPOTO', 'Lima', 'Juanjui', 240.00, 815.88, 270.00],
            ['LIMA-TARAPOTO', 'Lima', 'Tarapoto', 190.00, 1005.88, 330.00],

            // LIMA-TACNA-LA-CONCORDIA
            ['LIMA-TACNA-LA-CONCORDIA', 'Lima', 'Chincha', 200.00, 200.00, 92.00],
            ['LIMA-TACNA-LA-CONCORDIA', 'Lima', 'Ica', 76.00, 276.00, 115.00],
            ['LIMA-TACNA-LA-CONCORDIA', 'Lima', 'Nazca', 168.00, 444.00, 154.00],
            ['LIMA-TACNA-LA-CONCORDIA', 'Lima', 'Camana', 437.00, 881.00, 260.00],
            ['LIMA-TACNA-LA-CONCORDIA', 'Lima', 'Arequipa', 167.96, 1048.96, 292.91],
            ['LIMA-TACNA-LA-CONCORDIA', 'Lima', 'Pto. Matarani', 57.93, 1074.31, 299.99],
            ['LIMA-TACNA-LA-CONCORDIA', 'Lima', 'Moquegua', 193.34, 1209.72, 337.80],
            ['LIMA-TACNA-LA-CONCORDIA', 'Lima', 'Ilo', 102.44, 1305.57, 364.57],
            ['LIMA-TACNA-LA-CONCORDIA', 'Lima', 'Tacna', 352.70, 1369.08, 382.30],
            ['LIMA-TACNA-LA-CONCORDIA', 'Lima', 'La Concordia', 35.95, 1405.03, 392.34],
        ];

        foreach ($rows as [$routeKey, $origin, $dest, $dvPartial, $dvAcum, $rate]) {
            MtcReferentialRate::updateOrCreate(
                [
                    'source' => 'DS-026-2024-MTC',
                    'year' => 2024,
                    'route_key' => $routeKey,
                    'destination' => $dest,
                ],
                [
                    'origin' => $origin,
                    'dv_partial_km' => $dvPartial,
                    'dv_acum_km' => $dvAcum,
                    'rate_soles_per_tm' => $rate,
                ]
            );
        }
    }
}
