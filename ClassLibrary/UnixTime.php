<?php

class UnixTime{
    var $value;

    // construtor
    function UnixTime($value){
        $this->value = $value;
    }

    // Adiciona horas e minutos ao timestamp
    function AddTime($hours, $minutes = 0)
    {
        $time = $this->value;

        $hour = date("H", $time);
        $minute = date("i", $time);
        $second = date("s", $time);
        $month = date("m", $time);
        $day = date("d", $time);
        $year = date("Y", $time);
        $result = mktime(($hour + $hours), ($minute + $minutes), $second, $month, $day, $year);

        return $result;
    }

    // Adiciona meses ao timestamp
    function AddMonths($months)
    {
        $time = $this->value;

        $hour = date("H", $time);
        $minute = date("i", $time);
        $second = date("s", $time);
        $month = date("m", $time);
        $day = date("d", $time);
        $year = date("Y", $time);
        $result = mktime($hour, $minute, $second, ($month + $months), $day, $year);

        return $result;
    }

    static function ConvertToTime($hours) {
        $integerPart = (int)$hours;
        $fractionalPart = $hours-$integerPart;

        $minutes = $fractionalPart*60;
        return str_pad($integerPart, 2, '0', STR_PAD_LEFT).":".str_pad(round($minutes), 2, '0', STR_PAD_LEFT);
    }

    static function Diff($time1, $time2, $precision = 6) {
        // If not numeric then convert texts to unix timestamps
        if (!is_int($time1)) {
            $time1 = strtotime($time1);
        }
        if (!is_int($time2)) {
            $time2 = strtotime($time2);
        }

        // If time1 is bigger than time2
        // Then swap time1 and time2
        if ($time1 > $time2) {
            $ttime = $time1;
            $time1 = $time2;
            $time2 = $ttime;
        }

        // Set up intervals and diffs arrays
        $intervals = array('year','month','day','hour','minute','second');
        $diffs = array();

        // Loop thru all intervals
        foreach ($intervals as $interval) {
            // Set default diff to 0
            $diffs[$interval] = 0;
            // Create temp time from time1 and interval
            $ttime = strtotime("+1 " . $interval, $time1);
            // Loop until temp time is smaller than time2
            while ($time2 >= $ttime) {
                $time1 = $ttime;
                $diffs[$interval]++;
                // Create new temp time from time1 and interval
                $ttime = strtotime("+1 " . $interval, $time1);
            }
        }

        $count = 0;
        $times = array();
        // Loop thru all diffs
        foreach ($diffs as $interval => $value) {
            // Break if we have needed precission
            if ($count >= $precision) {
                break;
            }
            // Add value and interval 
            // if value is bigger than 0
            if ($value > 0) {
                // Add s if value is not 1
                if ($value != 1) {
                    $interval .= "s";
                }
                // Add value and interval to times array
                $times[] = $value . " " . $interval;
                $count++;
            }
        }

        // Return string with times
        // return implode(", ", $times);
        return $diffs;
    }
}

?>
