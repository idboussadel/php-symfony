<?php
namespace Calendar\Controller;

use Calendar\Model\LeapYear;

class LeapYearController
{
    public function index(int $year): string
    {
        $leapYear = new LeapYear();
        if ($leapYear->isLeapYear($year)) {
            return 'Yep, this is a leap year! ';
        }

        return 'Nope, this is not a leap year.';
    }
}