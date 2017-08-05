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

    // format US aaaa/mm/jj
    // format Fr jj/mm/aaaa

    //
    function isValidDateTime($date)
    {
        if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return true;
            }
        }

        return false;
    }

    public function FrToUs(){

        return date('Y-m-d');
    }

    public function UsToFr($date){

        if($this->isValidDateTime($date)){
            $date = strtotime($date);
            $date = date("d/m/Y", $date);
            return  $date;
        }



    }




}