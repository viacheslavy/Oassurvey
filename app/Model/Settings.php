<?php

namespace App\Model;

class Settings extends \Illuminate\Database\Eloquent\Model {
    public $timestamps = false;

    protected $table = 'tblSettings';

    protected $primaryKey = 'survey_id';
}