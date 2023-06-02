<?php

namespace App\Models;

use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_profile_id',
        'name',
        'original_name',
        'mime',
        'extension',
        'size',
        'sort',
        'path',
        'description',
        'alt',
        'hash',
        'disk',
        'group',
    ];

    protected $hidden = ['hash', 'disk', 'storage_path', 'full_path', 'created_at', 'updated_at'];
    protected $appends = [
        'url',
        'relative_url',
        'storage_path',
        'full_path',
    ];

    public function url(): ?string
    {
        /** @var Filesystem|Cloud $disk */
        $disk = Storage::disk( 'public' );
        $path = "{$this->path}{$this->name}.{$this->extension}";
        return $disk->url( $path );
    }

    public function getStoragePathAttribute(): ?string
    {
        return "public/{$this->path}{$this->name}.{$this->extension}";
    }

    /**
     * Return path starting from storage/ folder
     *
     * @return string|null
     */
    public function getFullPathAttribute(): ?string
    {
        return storage_path("app/public/{$this->path}{$this->name}.{$this->extension}");
    }

    public function getUrlAttribute(): ?string
    {
        return $this->url();
    }

    public function getRelativeUrlAttribute(): ?string
    {
        $url = $this->url();

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return parse_url($url, PHP_URL_PATH);
    }

    /**
     * @return string|null
     */
    public function getTitleAttribute(): ?string
    {
        if ($this->original_name !== 'blob') {
            return $this->original_name;
        }

        return "{$this->name}.{$this->extension}";
    }

    public function uploadFile($file, $path = null, $disk = 'public')
    {
        $path = $path ?? $this->path;
        $name = $this->name;
        $extension = $this->extension;

        $file->storeAs($path, "{$name}.{$extension}", $disk);
    }
}
