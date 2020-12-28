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
