<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    const NOT_STARTED = 'NOT_STARTED';
    const IN_PROGRESS = 'IN_PROGRESS';
    const READY_FOR_TEST = 'READY_FOR_TEST';
    const COMPLETED = 'COMPLETED';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'project_id',
        'user_id',
    ];
}
