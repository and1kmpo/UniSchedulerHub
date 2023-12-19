<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'credits', 'knowledge_area', 'elective',
    ];

    public function professors()
    {
        return $this->belongsToMany(Professor::class, 'professor_subject');
    }
}
