<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTargetTons extends Model
{
    protected $fillable = [
        'plant',
        'area',
        'month',
        'year',
        'day',
        'target',
    ];
}
