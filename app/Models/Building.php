<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'code'];

    protected static function booted()
    {
        static::creating(function ($building) {
            if (empty($building->code)) {
                // Buscar el último código numérico
                $lastCode = self::withTrashed()
                    ->orderBy('id', 'desc')
                    ->value('code');

                $nextNumber = $lastCode
                    ? (int) preg_replace('/\D/', '', $lastCode) + 1
                    : 1;

                // Generar en formato B001, B002, etc.
                $building->code = 'B' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            } else {
                // Forzar mayúsculas
                $building->code = strtoupper($building->code);

                // Evitar colisiones manuales
                if (self::withTrashed()->where('code', $building->code)->exists()) {
                    throw new \Exception("El código {$building->code} ya existe.");
                }
            }
        });
    }


    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}
