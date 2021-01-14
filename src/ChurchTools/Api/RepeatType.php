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
        if ($this->isNoRepeat()) {
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
        return $this->statusID == 999;
    }
}
