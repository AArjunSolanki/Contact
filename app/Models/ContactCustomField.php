<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactCustomField extends Model
{
    use HasFactory;
    protected $fillable = ['label', 'type'];
    
    public function values() {
        return $this->hasMany(ContactFieldValue::class, 'custom_field_id');
    }
}
