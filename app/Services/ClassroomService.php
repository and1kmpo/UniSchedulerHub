<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Building;
use Illuminate\Support\Facades\DB;

class ClassroomService
{
    public function create(array $data): Classroom
    {
        return DB::transaction(function () use ($data) {

            $building = Building::findOrFail($data['building_id']);

            $name = $this->generateName($building->id, $data['floor'], $building->code);

            return Classroom::create([
                'name'        => $name,
                'building_id' => $building->id,
                'floor'       => $data['floor'],
                'capacity'    => $data['capacity'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        });
    }

    public function update(Classroom $classroom, array $data): Classroom
    {
        return DB::transaction(function () use ($classroom, $data) {

            if (
                $data['building_id'] != $classroom->building_id ||
                $data['floor'] != $classroom->floor
            ) {
                $classroom->update(['status' => 'inactive']);

                return $this->create($data);
            }

            $classroom->update([
                'capacity'    => $data['capacity'],
                'description' => $data['description'],
                'status'      => $data['status'] ?? $classroom->status,
            ]);

            return $classroom;
        });
    }

    public function delete(Classroom $classroom): void
    {
        if ($classroom->schedules()->exists()) {
            throw new \DomainException('CLASSROOM_IN_USE');
        }

        $classroom->delete();
    }

    private function generateName($buildingId, $floor, $prefix): string
    {
        $count = Classroom::where('building_id', $buildingId)
            ->where('floor', $floor)
            ->withTrashed()
            ->count() + 1;

        $consecutive = str_pad($count, 2, '0', STR_PAD_LEFT);

        return "{$prefix}-F{$floor}-{$consecutive}";
    }
}
