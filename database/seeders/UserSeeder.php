<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Client;
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
        $users = [
            [
                'name' => 'Gian',
                'user_name' => 'gian.zapata',
                'email' => 'gian@gian.com',
            ],
            [
                'name' => 'Bruno',
                'user_name' => 'bruno.mendoza',
                'email' => 'bruno@bruno.com',
            ],
            [
                'name' => 'Antwan',
                'user_name' => 'antwan.hernandez',
                'email' => 'antwan@antwan.com',
            ],
        ];

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        foreach ($users as $key => $user) {
            $seedUser = User::where('email', $user['email'])->first();
            if($seedUser) continue;

            $user = User::create([
                'name' => $user['name'],
                'user_name' => $user['user_name'],
                'email' => $user['email'],
                'password' => Hash::make('Abc123456!'),
                'email_verified_at' => now()
            ]);
            $user->assignRole($adminRole);

            $userProfile = UserProfile::create([
                'user_id' => $user->id,
                'last_name' => '',
            ]);

            $imageNumber = rand(1, 23);
            $imageFilename = $imageNumber !== 23 ? "profile-picture-{$imageNumber}.png" : "default-profile.png";
            $imagePath = public_path("img/profile-images/{$imageFilename}");
            $fileContents = file_get_contents($imagePath);

            $attachment = new Attachment;
            $attachment->original_name = $imageFilename;
            $attachment->mime = 'image/png';
            $attachment->extension = 'png';
            $attachment->size = strlen($fileContents);
            $attachment->path = 'user-profile-images/';
            $attachment->disk = 'public';
            $attachment->save();

            Storage::disk('public')->put("user-profile-images/{$attachment->name}.{$attachment->extension}", $fileContents);

            $userProfileImage = new UserProfileImage;
            $userProfileImage->user_profile_id = $userProfile->id;
            $userProfileImage->attachment_id = $attachment->id;
            $userProfileImage->save();

            $userProfile->profileImage()->save($userProfileImage);
        }
    }
}
