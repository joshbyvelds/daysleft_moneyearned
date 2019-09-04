<?php
    // function from: https://stackoverflow.com/questions/336127/calculate-business-days
    function getWorkingDays($startDate,$endDate,$holidays){
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);


        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
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

        return $workingDays;
    }

    function getMoneyEarned($currentDate,$startDate,$holidays){

    }

    $holidays = [
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
    $now = date("Y-m-d");
    $first_day_of_school = "2019-09-03";
    $last_day_of_school  = "2020-06-25";

    $daysLeft = getWorkingDays($now, $last_day_of_school, $holidays);
    $moneyEarned = getMoneyEarned($now, $first_day_of_school, $holidays);
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
    <div class="daysleft">
        <div class="statLabel">Days Left</div>
        <div class="number"><?php echo $daysLeft ?></div>
    </div>

    <div class="moneyearned">
        <div class="statLabel">Money Earned</div>
        <div class="number"><?php echo $moneyEarned ?></div>
    </div>

    <!-- Progress bar here -->
    <!--  S|--------------|M|-------|E -->
    <script src="master.js"></script>
</body>
</html>
