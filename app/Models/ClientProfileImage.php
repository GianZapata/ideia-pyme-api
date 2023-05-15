<?php

namespace App\Models;

use App\Models\Attachment;
use App\Models\ClientProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProfileImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_profile_id',
        'attachment_id',
    ];


    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class);
    }
}
