<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyTargetTons extends Model
{

    protected $fillable = ['plant', 'target', 'month', 'year', 'working_days'];
}
