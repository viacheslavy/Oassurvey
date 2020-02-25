<?php

namespace App\Classes;

use PDO;

class Crosstab extends Database {

    /**
     * @param $queryBy
     * @return string
     */
    private function byToCust( $queryBy ){
        /*
         * query by
         */
        switch( $queryBy ) {
            case 'office':
                $cust = 'cust_6';
                break;
            case 'category':
                $cust = 'cust_5';
                break;
            case 'title':
                $cust = 'cust_3';
                break;
            case 'department':
                $cust = 'cust_4';
                break;
            case 'group':
            default:
                $cust = 'cust_2';
                break;
        }
        return $cust;
    }

    /**
     * @param $percentage
     * @return string
     */
    public function generateColor( $percentage ){
        $colors = [
            0 =>  '103,189,124',
            5 => '118,193,125',
            10 => '133,197,127',
            15 => '148,201,128',
            20 => '163,205,130',
            25 => '179,209,131',
            30 => '194,213,132',
            35 => '209,217,134',
            40 => '224,221,135',
            45 => '239,225,137',
            50 => '254,229,138',
            55 => '253,217,135',
            60 => '252,204,132',
            65 => '252,192,130',
            70 => '251,180,127',
            75 => '250,168,124',
            80 => '249,155,121',
            85 => '248,143,118',
            90 => '248,131,116',
            95 => '247,118,113',
            100 => '246,106,110',
        ];
        $color = $colors[ 0 ];
        if( isset( $colors[ $percentage ] ) ){
            $color = $colors[ $percentage ];
        }
        return $color . ',1';
    }

    /**
     * @param $min
     * @param $max
     * @param $value
     * @return float|int
     */
    public function calculatePercent( $min, $max, $value){
        if( $max == 0 || $value == 0 ){
            return 0;
        }
        $percentage = round( ( $value / $max ) * 100, 2 );
        if( $percentage > 100 ){
            $percentage = 100;
        } else if( $percentage < 0 ){
            $percentage = 0;
        }
        $percentage = ceil( $percentage / 5 ) * 5;
        return $percentage;
    }

    /**
     * @param $surveyID
     * @param $crosstabCategories
     * @return array
     */
    public function getData( $surveyID, $queryTotal, $queryBy, $selectBranch, $selectGroup, $selectCategory ){
        $data = [];
        $data[ 'head' ][ 'scale' ][ 'min' ] = 0;
        $data[ 'head' ][ 'scale' ][ 'max' ] = 0;
        $data[ 'head' ][ 'hour' ][ 'min' ] = 0;
        $data[ 'head' ][ 'hour' ][ 'max' ] = 0;
        $data[ 'body' ] = [];
        //
        $cust = $this->byToCust( $queryBy );
        if( $selectCategory ){
            $pageID = $selectCategory;
        } else if( $selectGroup ){
            $pageID = $selectGroup;
        } else if( $selectBranch ){
            $pageID = $selectBranch;
        } else {
            return [];
        }
        $DBH = new Account();
        $pageID = $DBH->parent_page_id( $pageID );
        $this->Connect();
        /*
         * groups
         */
        $sqlGroups = "SELECT  r.$cust AS cust
        FROM   `tblQuestion` q
        JOIN `tblAnswer` a
          ON a.question_id = q.question_id
        JOIN `tblRespondent` r
          ON a.resp_id = r.resp_id
        WHERE  q.survey_id = :survey_id
          AND r.survey_completed=1
          GROUP BY r.$cust
          ORDER BY r.$cust ASC";
        $STH = $this->db->prepare($sqlGroups);
        $STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
        $STH->execute();
        $groups = $STH->fetchAll(PDO::FETCH_ASSOC);
        /*
         * data
         */
        foreach( $groups as $group ){
            $tempPageID = $pageID;
            $parentQuestionID = 1;
            $i = 0;
            while($parentQuestionID != 0) {
                $i++;
                $preSql = "SELECT 
                    p.page_id, 
                    p.question_id_parent, 
                    q.page_id AS temp 
                FROM `tblPage` p 
                    LEFT JOIN `tblQuestion` q 
                    ON p.question_id_parent = q.question_id 
                    WHERE p.page_id =:page_id";
                $STH = $this->db->prepare($preSql);
                $STH->bindValue(':page_id', $tempPageID, PDO::PARAM_INT);
                $STH->execute();
                $result = $STH->fetchAll(PDO::FETCH_ASSOC);
                $tempPageID = $result[0]['temp'];
                $parentQuestionID = $result[0]['question_id_parent'];
                if($parentQuestionID > 0) {
                    $sql[$i] = "SELECT answer_value 
	                             FROM   tblAnswer a".$i." 
	                             WHERE  question_id = ".$parentQuestionID." 
	                                    AND a".$i.".resp_id = a.resp_id";
                }
            }

            //
            $currentPct = "a.answer_value";

            //hours calculation
            $sqlHours = ", sum(((".$currentPct."))";

            for($j=1; $j<$i; $j++) {
                $sqlHours .= "*((".$sql[$j].")/100)";
            }

            $sqlHours .= ") as hours";

            //compensation calculation

            //Get total hours per respondent
            $sqTot = "SELECT SUM(answer_value) 
						FROM tblAnswer aa 
						JOIN tblQuestion qq ON aa.question_id = qq.question_id 
						JOIN tblPage pp ON qq.page_id = pp.page_id 
						WHERE  question_id_parent = 0 AND aa.resp_id = a.resp_id
						";

            //multiplier of all categories except top layer
            $sqlSalary = '';
            if($i == 1) {
                $sqlSalary .= ", sum((".$currentPct.")";
                $sqlSalary .= "/(".$sqTot.")";
            } else {
                $sqlSalary .= ", sum(((".$currentPct.")/100)";
            }

            for($j=1; $j<$i; $j++) {
                if($j == ($i-1)) {
                    $sqlSalary .= "*((".$sql[$j].")/(".$sqTot."))";
                } else {
                    $sqlSalary .= "*((".$sql[$j].")/100)";
                }
            }

            $sqlSalary .= "*r.resp_total_compensation";

            $sqlSalary .= ") as salary";

            //
            $sqlFull = "
            SELECT  
            q.page_id AS page_id,
            r.$cust AS cust, 
            q.question_desc AS category,
              COUNT(a.resp_id) AS count
              $sqlHours
              $sqlSalary
            FROM `tblQuestion` q 
            JOIN `tblAnswer` a 
              ON a.question_id = q.question_id 
            JOIN `tblRespondent` r 
              ON a.resp_id = r.resp_id 
            WHERE q.survey_id = :survey_id
              AND q.page_id = :page_id
              AND r.survey_completed=1
              AND r.$cust = :group
            GROUP BY q.question_desc
            ORDER BY q.question_desc ASC";
            //echo "\n\n $sqlFull \n\n";
            $STH = $this->db->prepare($sqlFull);
            $STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
            $STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
            $STH->bindValue(':group', $group[ 'cust' ], PDO::PARAM_STR);
            $STH->execute();
            $result = $STH->fetchAll(PDO::FETCH_ASSOC);
            //print_r( $result );
            $this->dataResultToTemplate( $data, $result, $groups );
        }
        /*
         * return
         */
        //print_r( $result );
        $this->db = null;
        //var_dump( $data );
        return $data;
    }

    /**
     * @param $result
     * @param $groups
     * @return array
     */
    private function dataResultToTemplate( &$data, $result, $groups ){
        foreach( $result as $row ){
            $groupData = &$data['body'][$row['category']]['data'][$row['cust']];
            @$groupData['hour'] += (float)$row['hours'];
            @$groupData['value'] += (float)$row['salary'];
            @$groupData['total_compensation'] += (float)$row['salary'];
            $bodyData = &$data[ 'body' ][ $row[ 'category' ] ];
            @$bodyData['participants'][$row['cust']] = (int)$row['count'];
            @$bodyData['total'] += (float)$row[ 'salary' ];
            @$bodyData['total_hours'] += (float)$row['hours'];
            foreach ($groups as $group) {
                if (!$group['cust']) {
                    continue;
                }
                if (@$data['body'][$row['category']]['data'][$group['cust']]) {
                    continue;
                }
                $groupData = &$data['body'][$row['category']]['data'][$group['cust']];
                $groupData['hour'] = 0;
                $groupData['value'] = 0;
                $groupData['total_compensation'] = 0;
                $bodyData = &$data['body'][$row['category']];
                $bodyData['participants'][$group['cust']] = 0;
                $bodyData['total'] = 0;
                $bodyData['total_hours'] = 0;
            }
        }
        if (isset($data['body'])) {
            foreach ($data['body'] as $category) {
                foreach ($category['data'] as $group) {
                    if ($group['value'] < $data['head']['scale']['min']) {
                        $data['head']['scale']['min'] = (float)$group['value'];
                    }
                    if ($group['value'] > $data['head']['scale']['max']) {
                        $data['head']['scale']['max'] = (float)$group['value'];
                    }
                    //
                    if ($group['hour'] < $data['head']['hour']['min']) {
                        $data['head']['hour']['min'] = (float)$group['hour'];
                    }
                    if ($group['hour'] > $data['head']['hour']['max']) {
                        $data['head']['hour']['max'] = (float)$group['hour'];
                    }
                }
            }
        }
        return $data;
    }

}