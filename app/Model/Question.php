<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {
    protected $table = 'tblQuestion';

    protected $primaryKey = 'question_id';

    public $timestamps = false;
}