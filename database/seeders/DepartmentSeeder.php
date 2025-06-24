<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{

    public function run()
    {
        Department::create(['name' => 'Üretim', 'description' => 'Üretim süreçleri ile ilgili bölüm.']);
        Department::create(['name' => 'Pazarlama', 'description' => 'Pazarlama faaliyetleri.']);
        Department::create(['name' => 'İnsan Kaynakları', 'description' => 'Personel ve işe alım süreçleri.']);
        Department::create(['name' => 'IT Destek', 'description' => 'Teknik destek ve altyapı.']);
    }
    /**
     * Run the database seeds.
     */

}
