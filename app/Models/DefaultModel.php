<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Base model for every domain model. Keep it generic: no domain logic here.
 *
 * @method static Builder<static> query()
 * @method static static|null find(int|string $id, array<int, string> $columns = ['*'])
 * @method static static findOrFail(int|string $id, array<int, string> $columns = ['*'])
 * @method static static create(array<string, mixed> $attributes = [])
 *
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
abstract class DefaultModel extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;
}
