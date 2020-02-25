<?php

namespace App\Model;

class Respondent extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'tblRespondent';

    protected $primaryKey = 'resp_id';

    public $timestamps = false;
}