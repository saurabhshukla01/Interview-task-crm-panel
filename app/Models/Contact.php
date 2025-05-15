<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'gender', 'profile_image', 'additional_file', 'is_merged'];

    public function customFields()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }
}
