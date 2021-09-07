<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *php artisan krlove:generate:model
     * @return void
     */
    public function run()
    {
        $genres = [
            [
                'code' => 'ALT',
                'name' => 'Alternative',
            ],
            [
                'code' => 'ANI',
                'name' => 'Anime',
            ],
            [
                'code' => 'BLU',
                'name' => 'Blues',
            ],
            [
                'code' => 'CHG',
                'name' => 'Christian & Gospel',
            ],
            [
                'code' => 'CLA',
                'name' => 'Classical',
            ],
            [
                'code' => 'CNT',
                'name' => 'Country',
            ],
            [
                'code' => 'DNC',
                'name' => 'Dance',
            ],
            [
                'code' => 'HHR',
                'name' => 'Hip Hop/Rap',
            ],
            [
                'code' => 'JZZ',
                'name' => 'Jazz',
            ],
            [
                'code' => 'LAT',
                'name' => 'Latin',
            ],
            [
                'code' => 'NAG',
                'name' => 'New Age',
            ],
            [
                'code' => 'POP',
                'name' => 'Pop',
            ],
            [
                'code' => 'RBS',
                'name' => 'R&B/Soul',
            ],
            [
                'code' => 'REG',
                'name' => 'Reggae',
            ],
            [
                'code' => 'ROC',
                'name' => 'Rock',
            ],
            [
                'code' => 'SSW',
                'name' => 'Singer/Songwriter',
            ],
            [
                'code' => 'WRL',
                'name' => 'World',
            ],
            [
                'code' => 'ELC',
                'name' => 'Electronica',
            ],
        ];

        if (Schema::hasTable('genres')) {
            foreach($genres as $genre) {
                Genre::firstOrCreate($genre);
            }
        }
    }
}
