<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserProfileImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seedUser = User::where('email', 'gian@gian.com')->first();

        if(!$seedUser) {
            $adminRole = Role::where('name', 'admin')->first();

            /** @var \App\Models\User $user **/
            // Crear un usuario y asignarle el rol de administrador
            User::factory()->create([
                'name' => 'Gian',
                'user_name' => 'gian.zapata',
                'email' => 'gian@gian.com',
                'password' => Hash::make('Abc123456!'),
                'email_verified_at' => now(),
            ])->each(function ($user) use ($adminRole) {

                /** @var \App\Models\UserProfile $userProfile **/
                $userProfile = UserProfile::factory()->create(['user_id' => $user->id]);
                $imageNumber = rand(1, 23);
                $imageFilename = $imageNumber !== 23 ? "profile-picture-{$imageNumber}.png" : "default-profile.png";
                $imagePath = public_path("img/profile-images/{$imageFilename}");
                $fileContents = file_get_contents($imagePath);

                /** @var \App\Models\Attachment $attachment **/
                $attachment = Attachment::factory()->create([
                    'original_name' => $imageFilename,
                    'mime' => 'image/png',
                    'extension' => 'png',
                    'size' => strlen($fileContents),
                    'path' => 'user-profile-images/',
                    'disk' => 'public',
                ]);

                Storage::disk('public')->put("user-profile-images/{$attachment->name}.{$attachment->extension}", $fileContents);
                $userProfileImage = UserProfileImage::factory()->create([
                    'user_profile_id' => $userProfile->id,
                    'attachment_id' => $attachment->id,
                ]);
                $userProfile->profileImage()->save($userProfileImage);
                $user->assignRole($adminRole);


            });
        }
    }
}
