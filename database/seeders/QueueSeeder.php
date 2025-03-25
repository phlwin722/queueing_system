<?php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\Admin;
use App\Models\Teller;
use App\Models\Window;
use App\Models\Type;
use App\Models\Currency;
use Database\Factories\Customer_logFactory;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create informatoin of admin
        // You are inserting a single record.
        Admin::create([
            'Firstname' => 'Tim',
            'Lastname' => 'Tequillo',
            'Username' => 'Admin',
            'Password' => bcrypt('admin123'), // Hash the password using bcrypt
        ]);

        // insert types records that match your factory data (1-5)
        //You are inserting multiple records at once.
        Type::insert([
            ['name' => 'Foreign Exchange', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Deposit', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'WithDrawal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Money Tranfer', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Customer Service', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // insert teller records that match your factory data (1-5)
        //You are inserting multiple records at once.
        Teller::insert([
            [
                'teller_firstname' => 'Rafael', 
                'teller_lastname' => 'Baldonado', 
                'teller_username' => 'rafael', 
                'teller_password' => bcrypt('rafael'), 
                'type_id' => '1', 
                'type_ids_selected' => '["1","2"]',
                'Image' => 'assets/teller/1/image.png',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'teller_firstname' => 'Alvin', 
                'teller_lastname' => 'Abogado', 
                'teller_username' => 'alvin', 
                'teller_password' => bcrypt('alvin'), 
                'type_id' => '2', 
                'type_ids_selected' => '["2"]',
                'Image' => 'assets/teller/2/image.png',
                'created_at' => now(), 
                'updated_at' => now()],
            [
                'teller_firstname' => 'John Carlo', 
                'teller_lastname' => 'Rica', 
                'teller_username' => 'rica', 
                'teller_password' => bcrypt('rica'), 
                'type_id' => '1',
                'type_ids_selected' => '["1","3","4","5"]', 
                'Image' => 'assets/teller/3/image.png',
                'created_at' => now(), 
                'updated_at' => now()],
            [
                'teller_firstname' => 'Jercy', 
                'teller_lastname' => 'Guevarra', 
                'teller_username' => 'jercy', 
                'teller_password' => bcrypt('jercy'), 
                'type_id' => '3',
                'type_ids_selected' => '["3"]', 
                'Image' => 'assets/teller/4/image.png',
                'created_at' => now(), 
                'updated_at' => now()],
            [
                'teller_firstname' => 'Dexter', 
                'teller_lastname' => 'Jamero', 
                'teller_username' => 'dexter', 
                'teller_password' => bcrypt('dexter'), 
                'type_id' => null, 
                'type_ids_selected' => '["4"]',
                'Image' => 'assets/teller/5/image.png',
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);

        Window::insert([
            [
                'window_name' => 'Window 1',
                'type_id' => '1',
                'teller_id' => '1',
                'created_at' => now(),
                'updated_at' => now (),
            ],
            [
                'window_name' => 'Window 2',
                'type_id' => '2',
                'teller_id' => '2',
                'created_at' => now(),
                'updated_at' => now (),
            ],
            [
                'window_name' => 'Window 3',
                'type_id' => '1',
                'teller_id' => '3',
                'created_at' => now(),
                'updated_at' => now (),
            ],
            [
                'window_name' => 'Window 4',
                'type_id' => '3',
                'teller_id' => '4',
                'created_at' => now(),
                'updated_at' => now (),
            ],
        ]);

        Currency::insert([
            [
                'currency_name' => 'Php',
                'currency_symbol' => '₱',
                'value' => '100.00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'currency_name' => 'Japanese Yen',
                'currency_symbol' => '¥',
                'value' => '1.01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'currency_name' => 'ChineseYuan',
                'currency_symbol' => '¥',
                'value' => '0.10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'currency_name' => 'Euro',
                'currency_symbol' => '€',
                'value' => '10.21',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'currency_name' => 'USD',
                'currency_symbol' => '$',
                'value' => '56.62',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Create 10 orders with random Customer logs 
        Queue::factory(1000)->create();
    }
}
