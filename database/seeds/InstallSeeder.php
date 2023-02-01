<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $folder = storage_path('app/seeds');
        $files = scandir($folder);
        foreach ($files as $file) {
            var_dump($file);
            if (strpos($file, '.json') === false)
                continue;

            $full_path = $folder . '/' . $file;
            $json = json_decode(file_get_contents($full_path), true);
            $table_name = $json['table_name'];
            $data = $json['data'];

            if (Schema::hasTable($table_name)) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table($table_name)->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            foreach ($data as $datum) {
                DB::table($table_name)->insert($datum);
            }
        }
    }
}
