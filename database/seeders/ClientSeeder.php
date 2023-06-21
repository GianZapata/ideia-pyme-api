<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Emisor;
use App\Models\Receptor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $user = User::find(1);
        // if(!$user) return;

        // $rfcEmisores = Emisor::all();
        // $rfcReceptores = Receptor::all();
        // $rfcs = $rfcEmisores->concat($rfcReceptores);

        /** @var \App\Models\Client $client **/
        foreach (Emisor::all() as $rfc) {
            Client::factory()->create([
                'rfc' => $rfc->rfc,
                'name' => $rfc->nombre,
                // 'user_id' => $user->id,
            ]);
        }
    }
}
