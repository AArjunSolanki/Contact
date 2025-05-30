<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email', 'phone', 'gender', 'profile_image', 'additional_file'];
    
    public function customFieldValues() {
        return $this->hasMany(ContactFieldValue::class);
    }
    
    public function mergedInto() {
        return $this->belongsTo(Contact::class, 'merged_into');
    }

}
