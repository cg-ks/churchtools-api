<?php
declare(strict_types=1);

namespace ChurchTools\Api;

/**
 * The type of repeat used for a CTRepeatingObject, which is used for all CTObjects which might be repeatable (e.g. CalendarEntries or Bookings)
 * Be carefull to not mix this class with the RepeatingType class which describes Master Data instances. This class is not an CTObject instance
 * 
 * @author Lukas Block
 */

class RepeatType
{
    private $id;
    private $frequency;
    private $optionId;
    private $endDate;
    private $additions;
    

    /**
     * @inheritDoc
     *
     * @param bool $hasDataBlock default value false
     */
    public function __construct($id=0, $frequency=NULL, $optionId=NULL, $endDate=NULL)
    {
        // TODO: Does not have exception dates
        $this->id = $id;
        $this->frequency = $frequency;
        $this->optionId = $optionId;
        $this->endDate = $endDate;
        $this->additions = [];
    }

    /**
     * @return int ID of this repeating type
     */
    public function getID(): int
    {
        return $this->id;
    }

    public function setID($id) {
        $this->id = $id;
    }
    
    public function setFrequency($frequency) {
        $this->frequency = $frequency;
    }

    public function getFrequency() {
        return $this->frequency;
    }

    public function setOptionId($optionId) {
        $this->optionId = $optionId;
    }

    public function getOptionId() {
        return $this->optionId;
    }

    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

    public function getEndDate() {
        return $this->endDate;
    }

    public function addAddition(AdditionalRepeatDate $ard) {
        $this->additions[$ard->getId()] = $ard;
    }

    public function getAdditions() {
        return $this->additions;
    }

    /**
     * Returns the last Date Time object of this repeat type. This can be
     * the end date, but if additional dates are present it might also be the
     * an additional date, which is later than the end date of the regular
     * recurrence
     *
     * @return date time object
     */
    public function getLastDate() {
        if ($this->isNoRepeat() || $this->isManualRepeat() || is_null($this->endDate)) {
            return False;
        }

        // Just get the latest date
        $lastDate = $this->endDate;

        // Check if one of the additional dates is later than the end date of the recurrence
        foreach ($this->additions as $add) {
            if ($lastDate < $add->getDate()) {
                $lastDate = $add->getDate();
            }
        } 

        return clone $lastDate;
    }

    public function getLastOccurence($startDate) {
        if ($this->isNoRepeat() || $this->isManualRepeat() || is_null($this->endDate)) {
            return False;
        }

        // TODO: This is an ugly approach, but easiest way to implement without code too much code alternation currently
        // Architectural technical debt here!
        $lastDate = $startDate;
        $lastDateTmp = clone $startDate;
        // Add one second, to get all dates later, than this date
        $lastDateTmp->modify("+1 second");
        $nextPossibleDate = $this->getNextDateAfter($startDate, $lastDateTmp);
        while ($nextPossibleDate !== False) {
            $lastDate = $nextPossibleDate;
            $lastDateTmp = clone $lastDate;
            $lastDateTmp->modify("+1 second");
            $nextPossibleDate = $this->getNextDateAfter($startDate, $lastDateTmp);
        }

        return clone $lastDate;
    }

    public function isNoRepeat(): bool
    {
        return $this->id == 0;
    }
    
    public function isDailyRepeat(): bool
    {
        return $this->id == 1;
    }

    public function isWeeklyRepeat(): bool
    {
        return $this->id == 7;
    }

    public function isMonthlyByDate(): bool
    {
        return $this->id == 31;
    }

    public function isMonthlyByWeekday(): bool
    {
        return $this->id == 32;
    }

    public function isMonthlyByXthWeekday($weekdayOfMonth): bool
    {
        return $this->isMonthlyByWeekday() && ($this->optionId == $weekdayOfMonth);
    }

    public function isMonthlyByLastWeekday(): bool
    {
        return $this->isMonthlyByXthWeekday(6);
    }

    public function isYearly(): bool
    {
        return $this->id == 365;
    }
    
    public function isManualRepeat(): bool
    {
        return $this->id == 999;
    }

    // TODO: Rename this function to getNextOccurenceAfter
    public function getNextDateAfter($startDate, $after)
    {
        // TODO: Does not take exception dates into account

        // Get possible start dates from which to start the recurrence, one is of course the parent start date
        $possibleStartDates = [];
        // Now add the start date of the parent CtRepeatingObject
        $possibleStartDates[] = $startDate;

        // The additional dates are just additional recurrencies without any repetition
        $additions = [];

        // Take care of the additional dates and additional dates with repetition
        foreach ($this->additions as $a) {
            if ($a->isRepeat()) {
                // This is also a possible start date
                $possibleStartDates[] = $a->getDate();
            } else if ($a->getDate() >= $after) {
                // Additional date
                $additions[] = $a->getDate();
            }
        }

        // Calculate all potential dates via repetition
        $closestRepetition = null;
        foreach ($possibleStartDates as $start) {
            // Calculate the next occurence after a certain date for the specific start date
            $result = $this->getNextSingleOccurenceAfter($start, $after);
            // Now check if it is closer to $after, than the results before
            // The subfunctions guarantee already, that $result is later or at the same time than $after, as such we just have to compare them
            if (($result !== false) && (is_null($closestRepetition) || ($closestRepetition >= $result))) {
                $closestRepetition = $result;
            }
        }

        // Check whether any other additional dates without repetition are closer to $after
        foreach ($additions as $a) {
            if (is_null($closestRepetition) || ($closestRepetition > $a)) {
                $closestRepetition = $a;
            }
        }

        // Return False if we were not able to find any repetition after the given date
        if (!is_null($closestRepetition)) {
            return $closestRepetition;
        } else {
            return False;
        }
    }

    public function getNextSingleOccurenceAfter($start, $after) {
        $result = null;
        // Now check which subroutine to call
        if ($this->isDailyRepeat()) {
            $result = $this->getNextDateDailyRepeatAfter($start, $after);
        } else if ($this->isWeeklyRepeat()) {
            $result = $this->getNextDateWeeklyRepeatAfter($start, $after);
        } else if ($this->isMonthlyByDate()) {
            $result = $this->getNextDateMonthlyByDateAfter($start, $after);
        } else if ($this->isMonthlyByWeekday()) {
            $result = $this->getNextDateMonthlyByWeekdayAfter($start, $after);
        } else if ($this->isYearly()) {
            $result = $this->getNextDateYearlyAfter($start, $after);
        } else if ($this->isManualRepeat() || $this->isNoRepeat()) {
            // Manual repeat and not repeat has no repetition, as such, it equals the possible start date
            // If the possible start date is later than $after set it, otherwise just continue with the next possible start date
            if ($start >= $after) {
                $result = $start;
            } else {
                return false;
            }
        } else {
            throw new \Exception("RepeatType must be of a certain repetition type!");
        }

        // Now check if the result is later than the last date to be allowed
        $lastDate = $this->getEndDate();
        if ((!is_null($lastDate)) && ($result > $lastDate)) {
            return false;
        }

        return $result;
    }

    protected function getNextDateDailyRepeatAfter($startDate, $after) : \DateTime
    {
        // First generate a clone so that we do not alter the original 
        $startDate = clone $startDate;
        // Now add the days with the frequency until we reach a date which is equal or larger than the $after variable
        while ($startDate < $after) {
            $startDate->modify('+' . $this->frequency . ' day');
        }
        return $startDate;
    }

    protected function getNextDateWeeklyRepeatAfter($startDate, $after) : \DateTime
    {
        // First generate a clone so that we do not alter the original 
        $startDate = clone $startDate;
        // Now add the days with the frequency until we reach a date which is equal or larger than the $after variable
        while ($startDate < $after) {
            $startDate->modify('+' . $this->frequency . ' week');
        }
        return $startDate;
    }

    protected function getNextDateMonthlyByDateAfter($startDate, $after) : \DateTime
    {
        // Get the start date time in parts
        $day = intval($startDate->format('j'));
        // We reduce the month number by one, so 1 is january
        $month = intval($startDate->format('n')) - 1;
        $year = intval($startDate->format('Y'));

        $hours = intval($startDate->format('G'));
        $minutes = intval($startDate->format('i'));
        $seconds = intval($startDate->format('s'));

        // Clone result becaause we do not want to change startDate parameter
        $result = clone $startDate;

        while ($result < $after) {
            // Add the time difference
            $month = $month + $this->frequency;
            // Now take care of new years (which might be multiple at the same time, if the month number is greater than 12 for example)
            while ($month >= 12) {
                $month = $month - 12;
                $year++;
            }

            // Now check of the date exists within this month
            $maxDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month+1, $year);
            if ($day > $maxDaysInMonth) {
                // The date does not exist in this month, thus skip to the next date
                continue;
            }
            
            // See: https://www.php.net/manual/de/datetime.formats.relative.php
            // Creates a new object via the following command: new \DateTime('first monday september 2020');
            $result = new \DateTime();
            // Now set the date and the time
            $result->setDate($year, $month+1, $day);
            $result->setTime($hours, $minutes, $seconds);

        }

        return $result;
    }

    protected function getNextDateMonthlyByWeekdayAfter($startDate, $after) : \DateTime
    {
        // See https://www.php.net/manual/de/datetime.format.php

        // Number of month - we use zero for january, thus substract 1
        $month = intval($startDate->format('n')) - 1;
        $year = intval($startDate->format('Y'));

        $hours = intval($startDate->format('G'));
        $minutes = intval($startDate->format('i'));
        $seconds = intval($startDate->format('s'));

        // Get the weekday
        $weekday = $startDate->format('l');
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Now clone the start date, because we do not want to alter the start date
        $result = clone $startDate;

        // Order
        $orders = array(
            1 => 'first',
            2 => 'second',
            3 => 'third',
            4 => 'fourth',
            5 => 'fifth',
            6 => 'last'
        );
        $order = $orders[$this->getOptionId()];

        while ($result < $after) {            
            // See: https://www.php.net/manual/de/datetime.formats.relative.php
            // Creates a new object via the following command: new DateTime('first monday september 2020');
            $resultTmp = new \DateTime($order . ' ' . $weekday . ' of ' . $monthNames[$month] . ' ' . $year);
            // Now set the time
            $resultTmp->setTime($hours, $minutes, $seconds);

            // If it is the fifth of some month, we have to check whether it is still in the same month, otherwise, the resultTmp might be false and should not be set
            if ((intval($resultTmp->format('n')) - 1) == $month) {
                $result = $resultTmp;
            }

            // Add the time difference just now, because we also want to catch the last and first day of the month with the starting date
            $month = $month + $this->frequency;
            // Now take care of new years (which might be multiple at the same time, if the month number is greater than 12 for example)
            while ($month >= 12) {
                $month = $month - 12;
                $year++;
            }
        }

        return $result;
    }

    protected function getNextDateYearlyAfter($startDate, $after) : \DateTime
    {
        // First generate a clone so that we do not alter the original 
        $startDate = clone $startDate;
        // Now add the days with the frequency until we reach a date which is equal or larger than the $after variable
        while ($startDate < $after) {
            $startDate->modify('+' . $this->frequency . ' year');
        }
        return $startDate;
    }

}

