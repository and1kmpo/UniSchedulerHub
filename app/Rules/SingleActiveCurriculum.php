<?php

namespace App\Rules;

use App\Models\Curriculum;
use Illuminate\Contracts\Validation\Rule;

class SingleActiveCurriculum implements Rule
{
    protected $programId;
    protected $ignoreId;

    public function __construct($programId, $ignoreId = null)
    {
        $this->programId = $programId;
        $this->ignoreId  = $ignoreId;
    }

    public function passes($attribute, $value)
    {
        return ! Curriculum::where('program_id', $this->programId)
            ->where('is_active', true)
            ->when(
                $this->ignoreId,
                fn($q) =>
                $q->where('id', '!=', $this->ignoreId)
            )
            ->exists();
    }

    public function message()
    {
        return 'There is already an active curriculum for this program.';
    }
}
