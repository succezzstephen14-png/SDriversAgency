<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasApplicationId
{
    protected static function bootHasApplicationId(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->application_id)) {
                $model->application_id = $model->generateApplicationId();
            }
        });
    }

    public function generateApplicationId(): string
    {
        do {
            $applicationId = $this->getApplicationIdPrefix() . '-' . strtoupper(Str::random(12));
        } while (static::where('application_id', $applicationId)->exists());

        return $applicationId;
    }

    protected function getApplicationIdPrefix(): string
    {
        return 'APP';
    }
}
