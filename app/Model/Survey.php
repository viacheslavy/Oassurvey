<?php

namespace App\Model;

class Survey extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'tblSurvey';

    protected $primaryKey = 'survey_id';


    public function respondents() {
        return $this->hasMany('App\Model\Respondent', 'survey_id', 'survey_id');
    }

    public function getResponseCount() {
        return $this->respondents()->whereNotNull('last_dt')->count();
    }

    public function getRespondentCount() {
        return $this->respondents()->count();
    }

    public function getCompletionCount() {
        return $this->respondents()->where('survey_completed', '=', 1)->count();
    }

    public function getResponsePercentage() {
        $respondentCount = $this->getRespondentCount();
        return $respondentCount ? round($this->getResponseCount() / $respondentCount * 100, 0) : 0;
    }

    public function getResponseRate() {
        return $this->getResponseCount() . ' of ' . $this->getRespondentCount() . ' (' . $this->getResponsePercentage() . '%)';
    }

    public function getCompletionPercentage() {
        $responseCount = $this->getResponseCount();
        return $responseCount ? round($this->getCompletionCount() / $responseCount * 100,0) : 0;
    }

    public function getCompletionRate() {
        return $this->getCompletionCount() . ' of ' . $this->getResponseCount() . ' (' . $this->getCompletionPercentage() . '%)';
    }

}