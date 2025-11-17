<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'gender', 'profile_image', 
        'additional_file', 'custom_fields', 'is_merged', 'merged_into', 'merged_data'
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'merged_data' => 'array',
        'is_merged' => 'boolean'
    ];

    public function masterContact()
    {
        return $this->belongsTo(Contact::class, 'merged_into');
    }

    public function mergedContacts()
    {
        return $this->hasMany(Contact::class, 'merged_into')->withTrashed();
    }

    public function scopeActive($query)
    {
        return $query->where('is_merged', false);
    }
}
