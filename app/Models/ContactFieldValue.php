<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactFieldValue extends Model
{
    use HasFactory;
    protected $fillable = ['contact_id', 'custom_field_id', 'value'];
    
    public function contact() {
        return $this->belongsTo(Contact::class);
    }
    
    public function customField() {
        return $this->belongsTo(ContactCustomField::class, 'custom_field_id');
    }
}
