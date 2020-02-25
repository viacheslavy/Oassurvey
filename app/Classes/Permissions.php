<?php

namespace App\Classes;

class Permissions
{
    /**
     * @var array
     */
    protected $surveyIds;

    public function __construct()
    {
        $this->surveyIds = [];
    }

    /**
     * @return array
     */
    public function getSurveyIds()
    {
        return $this->surveyIds;
    }

    /**
     * @param array $surveyIds
     */
    public function setSurveyIds($surveyIds)
    {
        $this->surveyIds = $surveyIds;
    }

    public function addSurveyId($surveyId, $perms)
    {
        $this->surveyIds[$surveyId] = $perms;
    }

}