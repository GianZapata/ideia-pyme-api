<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientsAttachments extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'attachment_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function clientAttachments()
    {
        return $this->hasMany(ClientAttachment::class);
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class);
    }

    public function getAttachmentUrlAttribute()
    {
        return $this->attachment->url;
    }

    public function getAttachmentNameAttribute()
    {
        return $this->attachment->name;
    }

    public function getAttachmentTypeAttribute()
    {
        return $this->attachment->type;
    }

    public function getAttachmentSizeAttribute()
    {
        return $this->attachment->size;
    }

    public function getAttachmentExtensionAttribute()
    {
        return $this->attachment->extension;
    }
}
