<?php

namespace App\Repository;

use App\Model\User;
use Illuminate\Database\Query\Builder;

class SurveyRepository {

    protected $c;

    public function __construct($c)
    {
        $this->c = $c;
    }

    public static function findByUsernameAndPassword($username, $password) {

    }

    public function findSingleSurvey($surveyId, $accountId) {
//        $sql = "
//            SELECT
//              survey_id,
//              survey_name,
//              survey_active,
//              survey_created_dt,
//              (SELECT COUNT(*) FROM tblRespondent WHERE account_id=:account_id AND survey_id=:survey_id AND last_dt IS NOT NULL) AS response_count,
//              (SELECT COUNT(*) FROM tblRespondent WHERE account_id=:account_id2 AND survey_id=:survey_id2) AS respondent_count,
//              (SELECT COUNT(*) FROM tblRespondent WHERE account_id=:account_id3 AND survey_id=:survey_id3 AND survey_completed=1) AS complete_count
//              FROM tblSurvey
//              WHERE account_id=:account_id4 AND survey_id=:survey_id4";
        $survey = $this->c->get('db')->table('tblSurvey')
            ->selectRaw('
                survey_id,
                survey_name,
                survey_active,
                
                (SELECT COUNT(*) FROM tblRespondent WHERE account_id=:account_id AND survey_id=:survey_id AND last_dt IS NOT NULL) AS response_count,
                (SELECT COUNT(*) FROM tblRespondent WHERE account_id=:account_id2 AND survey_id=:survey_id2) AS respondent_count,
                (SELECT COUNT(*) FROM tblRespondent WHERE account_id=:account_id3 AND survey_id=:survey_id3 AND survey_completed=1) AS complete_count
                ')
            ->whereRaw('account_id=:account_id4')
            ->whereRaw('survey_id=:survey_id4')
            ->setBindings([
                'account_id' => $accountId,
                'account_id2' => $accountId,
                'account_id3' => $accountId,
                'account_id4' => $accountId,
                'survey_id' => $surveyId,
                'survey_id2' => $surveyId,
                'survey_id3' => $surveyId,
                'survey_id4' => $surveyId,
            ])
            ->get();

        return $survey;
    }
}