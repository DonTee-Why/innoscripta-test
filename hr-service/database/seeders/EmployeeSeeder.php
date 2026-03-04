<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::create([
            'name' => 'John',
            'last_name' => 'Doe',
            'salary' => 75000,
            'ssn' => '123-45-6789',
            'address' => '123 Main St, New York, NY',
            'country' => 'USA',
        ]);

        Employee::create([
            'name' => 'Hans',
            'last_name' => 'Mueller',
            'salary' => 65000,
            'goal' => 'Increase team productivity by 20%',
            'tax_id' => 'DE123456789',
            'country' => 'Germany',
        ]);
    }
}
