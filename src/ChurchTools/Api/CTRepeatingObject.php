<?php
declare(strict_types=1);

namespace ChurchTools\Api;

/**
 * A single repeating ct object, which is used for all CTObjects which might be repeatable (e.g. CalendarEntries or Bookings)
 * Be carefull to not mix this class with the RepeatingType class which describes Master Data instances.
 * 
 * @author Lukas Block
 */

class CTRepeatingObject extends CTObject
{
    private $repeatType;
    

    /**
     * @inheritDoc
     *
     * @param bool $hasDataBlock default value false
     */
    public function __construct(array $rawData, bool $hasDataBlock = false)
    {
        $this->repeatType = new RepeatType();
        parent::__construct($rawData, $hasDataBlock);
    }

    /**
     * @inheritDoc
     */
    protected function handleDataBlock($blockName, $blockData): void
    {
        switch ($blockName) {
            case 'repeat_id':
                $this->repeatType->setID(intval($blockData));
                break;
            case 'repeat_frequence':
                $this->repeatType->setFrequency(intval($blockData));
                break;
            case 'repeat_option_id':
                $this->repeatType->setOptionId(intval($blockData));
                break;
            case 'repeat_until':
                $this->repeatType->setEndDate($this->parseDateTime($blockData));
            break;
            case 'additions':
                foreach ($blockData as $ard) {
                    $this->repeatType->addAddition(new AdditionalRepeatDate(array("data" => $ard)));
                }
            default:
                parent::handleDataBlock($blockName, $blockData);
        }
    }

    public function getRepeatType() {
        return $this->repeatType;
    }

}
