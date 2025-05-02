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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        //You are inserting multiple records at once.
        DB::table('branchs')
        ->insert([
            [
                'id' => '1',
                'branch_name' => 'VRT - San jose del monte', 
                'region' => 'Region III (Central Luzon)', 
                'province' => 'Bulacan', 
                'city' => 'Norzagaray',
                'Barangay' => 'Friendship Village Resources',
                'address' => 'blk 1 lot 5',
                'status' => 'Active',
                'opening_date' => '2025-04-12',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '2',
                'branch_name' => 'VRT - Caloocan', 
                'region' => 'Region III (Central Luzon)', 
                'province' => 'Bulacan', 
                'city' => 'Norzagaray',
                'Barangay' => 'Friendship Village Resources',
                'address' => 'blk 1 lot 5',
                'status' => 'Active',
                'opening_date' => '2025-04-12',
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);

            //You are inserting multiple records at once.
            DB::table('managers')
            ->insert([
            [
                'id' => '1',
                'manager_firstname' => 'Dexter', 
                'manager_lastname' => 'Dex', 
                'manager_username' => 'dex', 
                'manager_password' => bcrypt('dex'), 
                'Image' => 'assets/manager/1/profile.jpg',
                'manager_status' => 'Online',
                'branch_id' => '1',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '2',
                'manager_firstname' => 'Alvin', 
                'manager_lastname' => 'Abogado', 
                'manager_username' => 'alv', 
                'manager_password' => bcrypt('alv'), 
                'Image' => 'assets/manager/2/image.png',
                'manager_status' => 'Online',
                'branch_id' => '2',
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);

        // insert types records that match your factory data (1-5)
        //You are inserting multiple records at once.
        Type::insert([
            [
                'id' => '1',
                'name' => 'Foreign Exchange', 
                'indicator' => 'FE', 
                'serving_time' => '10',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '2',
                'name' => 'Online Appointment', 
                'indicator' => 'OA', 
                'serving_time' => '10',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '3',
                'name' => 'Foreign Exchange', 
                'indicator' => 'FE', 
                'serving_time' => '10',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '4',
                'name' => 'Online Appointment', 
                'indicator' => 'OA', 
                'serving_time' => '10',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '5',
                'name' => 'Deposit', 
                'indicator' => 'DP', 
                'serving_time' => '10',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '6',
                'name' => 'WithDrawal', 
                'indicator' => 'WD',  
                'serving_time' => '10',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '7',
                'name' => 'Money Tranfer', 
                'indicator' => 'MT',  
                'serving_time' => '10',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '8',
                'name' => 'Customer Service', 
                'indicator' => 'CS',  
                'serving_time' => '10',
                'created_at' => now(),
                'updated_at' => now()
             ],
             [
                'id' => '9',
                'name' => 'Manual Queueing', 
                'indicator' => 'MQ',  
                'serving_time' => '10',
                'created_at' => now(),
                'updated_at' => now()
             ],
             [
                'id' => '10',
                'name' => 'Manual Queueing', 
                'indicator' => 'MQ',  
                'serving_time' => '10',
                'created_at' => now(),
                'updated_at' => now()
             ],
        ]);

        // insert teller records that match your factory data (1-5)
        //You are inserting multiple records at once.
        Teller::insert([
            [   
                'id' => '1',
                'teller_firstname' => 'Rafael', 
                'teller_lastname' => 'Baldonado', 
                'teller_username' => 'rafael', 
                'teller_password' => bcrypt('rafael'), 
                'type_id' => '1', 
                'type_ids_selected' => '["1","2"]',
                'Image' => 'assets/teller/1/profile.jpg',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '2',
                'teller_firstname' => 'John Carlo', 
                'teller_lastname' => 'Rica', 
                'teller_username' => 'rica', 
                'teller_password' => bcrypt('rica'), 
                'type_id' => '2', 
                'type_ids_selected' => '["1","2","8" ]', 
                'Image' => 'assets/teller/3/image.png',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '3',
                'teller_firstname' => 'John Kenneth', 
                'teller_lastname' => 'Guiterez', 
                'teller_username' => 'Kenneth', 
                'teller_password' => bcrypt('Kenneth'), 
                'type_id' => '9',
                'type_ids_selected' => '["4","5","6","9"]', 
                'Image' => 'assets/teller/4/image.jpg',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'id' => '4',
                'teller_firstname' => 'Rovir Gian', 
                'teller_lastname' => 'Degula', 
                'teller_username' => 'Gian', 
                'teller_password' => bcrypt('Gian'), 
                'type_id' => '4', 
                'type_ids_selected' => '["4","5"]',
                'Image' => 'assets/teller/5/image.png',
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);

        Window::insert([
            [
                'window_name' => 'Window 1',
                'type_id' => '4',
                'teller_id' => '4',
                'status' => 'Offline',
                'created_at' => now(),
                'updated_at' => now (),
            ],
            [
                'window_name' => 'Window 2',
                'type_id' => '2',
                'teller_id' => '2',
                'status' => 'Offline',
                'created_at' => now(),
                'updated_at' => now (),
            ],
            [
                'window_name' => 'Window 3',
                'type_id' => '1',
                'teller_id' => '1',
                'status' => 'Offline',
                'created_at' => now(),
                'updated_at' => now (),
            ],
            [
                'window_name' => 'Window 4',
                'type_id' => '9',
                'teller_id' => '3',
                'status' => 'Offline',
                'created_at' => now(),
                'updated_at' => now (),
            ],
        ]);

        Currency::insert([
            [
                'currency_name' => 'US Dollar',
                'currency_symbol' => '$',
                'flag' => 'fi-us',
                'buy_value' => '12.00',
                'sell_value' => '32.00',
                'branch_id' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'currency_name' => 'Euro',
                'currency_symbol' => '€',
                'flag' => 'fi-eu',
                'buy_value' => '59.00',
                'sell_value' => '32.00',
                'branch_id' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'currency_name' => 'Philippine Peso',
                'currency_symbol' => '₱',
                'flag' => 'fi-ph',
                'buy_value' => '25.00',
                'sell_value' => '32.00',
                'branch_id' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'currency_name' => 'Hong Kong Dollar',
                'currency_symbol' => 'HK$',
                'flag' => 'fi-hk',
                'buy_value' => '12.00',
                'sell_value' => '32.00',
                'branch_id' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'currency_name' => 'Egyptian Poun',
                'currency_symbol' => 'E£',
                'flag' => 'fi-eg',
                'buy_value' => '10.00',
                'sell_value' => '38.00',
                'branch_id' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // update branchs
        DB::table ('branchs')
            ->where('id','1')
            ->update(['manager_id' => '1']);

        // update branchs
        DB::table ('branchs')
            ->where('id','2')
            ->update(['manager_id' => '2']);

        // update teller
        DB::table ('tellers')
            ->where('id','1')
            ->update(['branch_id' => '2']);

        // update teller
        DB::table ('tellers')
        ->where('id','2')
        ->update(['branch_id' => '2']);

        // update teller
        DB::table ('tellers')
            ->where('id','3')
            ->update(['branch_id' => '1']);

        // update teller
        DB::table ('tellers')
        ->where('id','4')
        ->update(['branch_id' => '1']);

        // update teller
        DB::table ('tellers')
        ->where('id','5')
        ->update(['branch_id' => '1']);
    
        
        // update type
        DB::table ('types')
            ->where('id','1')
            ->update(['branch_id' => '2']);

        // update type
        DB::table ('types')
        ->where('id','2')
        ->update(['branch_id' => '2']);

        // update type
        DB::table ('types')
            ->where('id','3')
            ->update(['branch_id' => '1']);

        // update type
        DB::table ('types')
        ->where('id','4')
        ->update(['branch_id' => '1']);

        // update types
        DB::table ('types')
        ->where('id','5')
        ->update(['branch_id' => '1']);

        // update types
        DB::table ('types')
        ->where('id','6')
        ->update(['branch_id' => '1']);

        // update types
        DB::table ('types')
        ->where('id','7')
        ->update(['branch_id' => '1']);

        // update types
        DB::table ('types')
        ->where('id','8')
        ->update(['branch_id' => '2']);

        // update types
        DB::table ('types')
        ->where('id','9')
        ->update(['branch_id' => '1']);

        // update types
        DB::table ('types')
        ->where('id','10')
        ->update(['branch_id' => '2']);
        
         // update types
         DB::table ('windows')
         ->where('id','1')
         ->update(['branch_id' => '1']);

        // update types
        DB::table ('windows')
        ->where('id','2')
        ->update(['branch_id' => '2']);

        // update types
        DB::table ('windows')
        ->where('id','3')
        ->update(['branch_id' => '2']);

        // update types
        DB::table ('windows')
        ->where('id','4')
        ->update(['branch_id' => '1']);
    
        // Insert survey responses
        DB::table('survey_responses')->insert([
            [
                'id' => '1',
                'name' => 'John Doe',
                'rating' => '1',
                'ease_of_use' => 'Yes',
                'waiting_time_satisfaction' => '1',
                'suggestions' => "The bank is highly regarded for its efficient and customer-friendly queueing system, which ensures a smooth and organized experience for all visitors. By implementing a well-structured system, the bank minimizes wait times and provides a more streamlined service, making each visit quicker and more convenient. Customers appreciate how the queueing system helps manage high volumes of people, reducing confusion and frustration. Whether it's for routine transactions or more complex financial services, this thoughtful approach to queuing demonstrates the bank's commitment to providing excellent service and enhancing the overall customer experience.",
                'branch_id' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '2',
                'name' => null,
                'rating' => '2',
                'ease_of_use' => 'No',
                'waiting_time_satisfaction' => '2',
                'suggestions' => 'The queue system is decent, but it could be faster. More staff during peak hours would help.',
                'branch_id' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '3',
                'rating' => '3',
                'name' => 'Jane Smith',
                'ease_of_use' => 'No',
                'waiting_time_satisfaction' => '3',
                'suggestions' => 'It would be great if the queue system displayed estimated wait times on screens.',
                'branch_id' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '4',
                'name' => null,
                'rating' => '4',
                'ease_of_use' => 'No',
                'waiting_time_satisfaction' => '4',
                'suggestions' => 'The queue could be better organized with clear signage to avoid confusion.',
                'branch_id' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '5',
                'name' => null,
                'rating' => '5',
                'ease_of_use' => 'Yes',
                'waiting_time_satisfaction' => '5',
                'suggestions' => 'I had a great experience! Everything went smoothly, and the queue was well-managed.',
                'branch_id' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '6',
                'name' => null,
                'rating' => '1',
                'ease_of_use' => 'Yes',
                'waiting_time_satisfaction' => '1',
                'suggestions' => 'The queue system was not clear. I had difficulty finding where to stand.',
                'branch_id' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '7',
                'name' => 'John',
                'rating' => '2',
                'ease_of_use' => 'No',
                'waiting_time_satisfaction' => '2',
                'suggestions' => 'Wait times were too long. There should be more efficient handling of customers.',
                'branch_id' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '8',
                'name' => 'Jane',
                'rating' => '3',
                'ease_of_use' => 'No',
                'waiting_time_satisfaction' => '3',
                'suggestions' => 'More staff is needed during busy hours to reduce waiting time.',
                'branch_id' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '9',
                'name' => null,
                'rating' => '4',
                'ease_of_use' => 'No',
                'waiting_time_satisfaction' => '4',
                'suggestions' => 'It would be helpful if the system could integrate with the app for a smoother check-in process.',
                'branch_id' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '10',
                'name' => 'James Dy',
                'rating' => '5',
                'ease_of_use' => 'Yes',
                'waiting_time_satisfaction' => '5',
                'suggestions' => 'Excellent experience! The queue system was fast and efficient.',
                'branch_id' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert dummy appointment data
        DB::table('appointments')->insert([
            [
                'referenceNumber' => Str::random(10),
                'branch_id' => 2,
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
                'email_status' => 'Not Sent',
                'type_id' => 1,
                'appointment_date' => '2025-04-28',
                'status' => 'Expired',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'referenceNumber' => Str::random(10),
                'branch_id' => 2,
                'name' => 'Jane Smith',
                'email' => 'janesmith@example.com',
                'email_status' => 'Not Sent',
                'type_id' => 8,
                'appointment_date' => '2025-04-29',
                'status' => 'Booked',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'referenceNumber' => Str::random(10),
                'branch_id' => 1,
                'name' => 'Michael Black',
                'email' => 'michaelblack@example.com',
                'email_status' => 'Not Sent',
                'type_id' => 3,
                'appointment_date' => '2025-04-28',
                'status' => 'Expired',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'referenceNumber' => Str::random(10),
                'branch_id' => 1,
                'name' => 'Alex Green',
                'email' => 'alexgreen@example.com',
                'email_status' => 'Not Sent',
                'type_id' => 7,
                'appointment_date' => '2025-05-02',
                'status' => 'Booked',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'referenceNumber' => Str::random(10),
                'branch_id' => 1,
                'name' => 'David Red',
                'email' => 'davidred@example.com',
                'email_status' => 'Not Sent',
                'type_id' => 5,
                'appointment_date' => '2025-05-02',
                'status' => 'Booked',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   // this is another branch
                'referenceNumber' => Str::random(10),
                'branch_id' => 2,
                'name' => 'Patricia White',
                'email' => 'patriciawhite@example.com',
                'email_status' => 'Not Sent',
                'type_id' => 1,
                'appointment_date' => '2025-05-02',
                'status' => 'Booked',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [   // this is another branch
                'referenceNumber' => Str::random(10),
                'branch_id' => 2,
                'name' => 'Emma Yellow',
                'email' => 'emmayellow@example.com',
                'email_status' => 'Not Sent',
                'type_id' => 8,
                'appointment_date' => '2025-05-02',
                'status' => 'Booked',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'referenceNumber' => Str::random(10),
                'branch_id' => 1,
                'name' => 'Henry Orange',
                'email' => 'henryorange@example.com',
                'email_status' => 'Not Sent',
                'type_id' => 3,
                'appointment_date' => '2025-05-03',
                'status' => 'Booked',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'referenceNumber' => Str::random(10),
                'branch_id' => 1,
                'name' => 'Lisa Blue',
                'email' => 'lisablue@example.com',
                'email_status' => 'Not Sent',
                'type_id' => 5,
                'appointment_date' => '2025-05-03',
                'status' => 'Booked',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'referenceNumber' => Str::random(10),
                'branch_id' => 2,
                'name' => 'Sam Brown',
                'email' => 'sambrown@example.com',
                'email_status' => 'Not Sent',
                'type_id' => 8,
                'appointment_date' => '2025-05-04',
                'status' => 'Booked',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);


        // Create 10 orders with random Customer logs
        Queue::factory(1000)->create();

    }
}