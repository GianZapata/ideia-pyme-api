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
        $seedUser = User::where('email', 'gian@gian.com')->first();

        if(!$seedUser) {
            $adminRole = Role::firstOrCreate(['name' => 'admin']);

            $user = new User;
            $user->name = 'Gian';
            $user->user_name = 'gian.zapata';
            $user->email = 'gian@gian.com';
            $user->password = Hash::make('Abc123456!');
            $user->email_verified_at = now();
            $user->save();
            $user->assignRole($adminRole);

            $userProfile = new UserProfile;
            $userProfile->user_id = $user->id;
            $userProfile->save();

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
