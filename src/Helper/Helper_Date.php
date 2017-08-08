<?php
namespace  App\Helper;
/**
 * Created by PhpStorm.
 * User: PC-Wanidol
 * Date: 05/08/2017
 * Time: 13:47
 */
class Helper_Date
{


    //15-01-2017 -> 2017-01-15

    // format US aaaa-mm-jj
    // format Fr jj-mm-aaaa

    //
    function isValidDate($date)
    {
//        var_dump($date);die();
        if (strlen($date) == 0) {
            return false;
        } else{
            $tempDate = explode('-', $date);
            // checkdate(month, day, year)
            return checkdate($tempDate[1], $tempDate[2], $tempDate[0]);
        }
    }

    public function convertFRtoUS($date){ //save to db

        if ($this->isValidDate($date)){
            return date('Y/m/d',strtotime($date));//yyyy/mm/dd
        }

    }

    public function convertUsToFr($date){//from db to interface ymd to dmy

        if ($this->isValidDate($date)) {
            return $date = date("d/m/Y", strtotime($date));
        }
    }




}