<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run()
    {
        BankAccount::create([
            'bank_name'     => 'BCA',
            'account_identifier_number'    => '105',
            'account_number' => '1111105',
            'account_name' => 'ob'
        ]);

        BankAccount::create([
            'bank_name'     => 'BCA',
            'account_identifier_number'    => '106',
            'account_number' => '1111105',
            'account_name' => 'ob'
        ]);
    }
}
