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
use Illuminate\Support\Str;

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

            $name = Str::uuid();
            $pathToSave = "user-profile-images/";
            $diskToSave = "public";
            $imageNumber = rand(1, 23);
            $imageFilename = $imageNumber !== 23 ? "profile-picture-{$imageNumber}.png" : "default-profile.png";
            $imagePath = public_path("img/profile-images/{$imageFilename}");
            $fileContents = file_get_contents($imagePath);
            $hash = md5($fileContents);

            /** @var \App\Models\Attachment $attachment **/
            $attachment = Attachment::create([
                'name' => $name,
                'original_name' => $imageFilename,
                'mime' =>'image/png',
                'extension' => 'png',
                'size' => strlen($fileContents),
                'sort' => 0,
                'path' => $pathToSave,
                'description' => null,
                'alt' => null,
                'hash' => $hash,
                'disk' => $diskToSave,
                'group' => null,
            ]);
            Storage::disk('public')->put("user-profile-images/{$attachment->name}.{$attachment->extension}", $fileContents);

            $userProfileImage = UserProfileImage::create([
                'user_profile_id' => $userProfile->id,
                'attachment_id' => $attachment->id,
            ]);

            $userProfile->profileImage()->save($userProfileImage);
        }
    }
}
