<?php
    $debug = false;
    $hoursPerWorkday = 3.5;
    $moneyPerHour = 14.17;

    // Notes:
    /*
     * First Paycheck 2019: 26.5 hrs | $378.89
     *
     */


    $holidays = [
        "2019-09-27",
        "2019-10-11",
        "2019-10-14",
        "2019-11-15",
        "2019-12-23",
        "2019-12-24",
        "2019-12-25",
        "2019-12-26",
        "2019-12-27",
        "2019-12-28",
        "2019-12-29",
        "2019-12-30",
        "2019-12-31",
        "2020-01-01",
        "2020-01-02",
        "2020-01-03",
        "2020-01-24",
        "2020-02-14",
        "2020-02-17",
        "2020-03-16",
        "2020-03-17",
        "2020-03-18",
        "2020-03-19",
        "2020-03-20",
        "2020-04-10",
        "2020-04-13",
        "2020-04-24",
        "2020-05-18",
        "2020-06-05"
    ];

    date_default_timezone_set("America/Toronto");
    $now = date("Y-m-d");
    $first_day_of_school = "2019-09-03";
    $last_day_of_school  = "2020-06-25";
    $first_payday  = "2019-09-19";
    $next_payday = "";
    $last_payday = "";

    function todayIsHoliday(){
        global $now, $holidays;
        return in_array($now, $holidays);
    }

    // function from: https://stackoverflow.com/questions/336127/calculate-business-days
    function getWorkingDays($startDate,$endDate,$holidays){
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);


        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to include both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }
        else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)

            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            }
            else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
    //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0 )
        {
            $workingDays += $no_remaining_days;
        }

        //We subtract the holidays
        foreach($holidays as $holiday){
            $time_stamp=strtotime($holiday);
            //If the holiday doesn't fall in weekend
            if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
                $workingDays--;
        }

        // If is not the weekend or a holiday and it is before your shift starts.. add a day back in
        if ($the_last_day_of_week < 6 && !todayIsHoliday() && date('H') < 15){
            $workingDays++;
        }

        return $workingDays;
    }

    function getMoneyEarned($currentDate,$startDate,$holidays){
        global $hoursPerWorkday,$moneyPerHour,$debug;

        $currentDate= strtotime($currentDate);
        $startDate = strtotime($startDate);
        $days = ($currentDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $currentDate);

        if($debug) {
            echo "$ First Day of the Week:" . $the_first_day_of_week . "<br />";
            echo "$ The Last Day of the Week:" . $the_last_day_of_week . "<br />";
        }


        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }
        else {
            if ($the_first_day_of_week == 7) {
                $no_remaining_days--;
                if ($the_last_day_of_week == 6) {
                    $no_remaining_days--;
                }
            }
            else {
                $no_remaining_days -= 2;
            }
        }

        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0 )
        {
            $workingDays += $no_remaining_days;
        }

        //We subtract the holidays
        foreach($holidays as $holiday){
            $time_stamp=strtotime($holiday);
            //If the holiday doesn't fall in weekend
            if ($startDate <= $time_stamp && $time_stamp <= $currentDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
                $workingDays--;
        }

        if ($the_last_day_of_week < 6 && !todayIsHoliday()  && date('H') < 15){
            $workingDays--;
        }


        if ($debug) {
            echo "# Hours:" . (($workingDays * 3.5) + moneyModifer()) . "<br />";
            echo "$ Working Days:" . $workingDays . "<br />";
            echo "$ Hours Per Workday:" . $hoursPerWorkday . "<br />";
            echo "$ Money Per Hour:" . $moneyPerHour . "<br />";
        }

        return sprintf("%0.2f", round(($workingDays * $hoursPerWorkday * $moneyPerHour) + (moneyModifer() * $moneyPerHour),2));

    }

    function moneyModifer(){
        $result = 0;
        $list = [
            1,
            1,
            -2,
        ];

        for($i=0; $i < count($list); $i++){
            $result += $list[$i];
        }

        return $result;
    }

    function getNextPayday($now, $first_payday) {
        $currentDate = strtotime($now);
        $paydate = strtotime($first_payday);

        $days = ((($currentDate - $paydate) / 86400) % 14) - 1;

        return $days;
    }

    function getLastPayday($now, $daysUntilPayday) {
        $currentDate = strtotime($now);
        $lastPayDayTimestamp = ($currentDate + (($daysUntilPayday + 1) * 86400)) - (22 * 86400);
        $lastPayDate = date( "Y-m-d", $lastPayDayTimestamp);
        return $lastPayDate;
    }

    $daysLeft = getWorkingDays($now, $last_day_of_school, $holidays);
    $totalDays = getWorkingDays($first_day_of_school, $last_day_of_school, $holidays);
    $moneyEarned = getMoneyEarned($now, $first_day_of_school, $holidays);
    $daysUntilPayday = getNextPayday($now, $first_payday);
    $last_payday = getLastPayday($now, $daysUntilPayday);
    $moneyEarnedPay = getMoneyEarned($last_payday, $first_day_of_school, $holidays);


    $percent = ($totalDays - $daysLeft) / $totalDays;


?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Days Left & Money Earned</title>
    <link rel="stylesheet" href="master.css">
</head>
<body>
    <div class="wrapper">
        <div class="left"></div>
        <div class="right"></div>


        <div class="daysleft">
            <div class="statLabel">Days Left</div>
            <div class="number"><?php echo $daysLeft ?></div>
        </div>

        <div class="moneyearned">
            <div class="statLabel">Money Earned</div>
            <div class="number">$<?php echo $moneyEarned ?></div>
        </div>

        <div class="payday">
            <div class="statLabel">Days until Payday</div>
            <div class="number"><?php if($daysUntilPayday === 0){ echo "<span>$$ Payday Today! $$</span>"; }else{ echo $daysUntilPayday; } ?></div>
        </div>

        <div class="moneyearnedpayday">
            <div class="statLabel">Money Banked</div>
            <div class="number">$<?php echo $moneyEarnedPay ?></div>
        </div>

        <div class="track">
            <div class="mm_run" style="left:<?php echo (ceil($percent * 358) - 76); ?>px;"></div>
        </div>

        <footer>
            Copyright 2019 - Byvelds Multimedia
        </footer>
    </div>


    <!-- Progress bar here -->
    <!--  S|--------------|M|-------|E -->
    <script src="master.js"></script>
</body>
</html>
