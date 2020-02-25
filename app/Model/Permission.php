<?php

namespace App\Model;

use App\Model\Traits\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model {
    use HasCompositePrimaryKey;

    const CAN_VIEW_SURVEY = 'Survey';

    protected $primaryKey = ['user_id', 'name', 'value'];
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'value',
    ];
}