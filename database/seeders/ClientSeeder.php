<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        /** @var \App\Models\Client $client **/
        Client::factory(50)->create()->each( function ( $client ) {
            /** @var \App\Models\ClientProfile $clientProfile **/
            $clientProfile = ClientProfile::factory()->create([
                'client_id' => $client->id,
            ]);

            $client->assignRole('client');

        });
    }
}
