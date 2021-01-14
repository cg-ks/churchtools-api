<?php
declare(strict_types=1);

namespace ChurchTools\Api;

/**
 * Represents an additional, non-regular repetition date of a recurring CT Object (CTRepeatingObject)
 * 
 * @author Lukas Block
 */

class AdditionalRepeatDate extends CTObject
{
    private $id;
    private $date;
    

    /**
     * @inheritDoc
     */
    protected function handleDataBlock($blockName, $blockData): void
    {
        switch ($blockName) {
            case 'id':
                $this->id = intval($blockData);
                break;
            case 'add_date':
                $this->date = $this->parseDateTime($blockData);
                break;
            default:
                parent::handleDataBlock($blockName, $blockData);
        }
    }

    /**
     * Get the value of id
     */ 
    public function getID()
    {
        return $this->id;
    }

    /**
     * Get the value of date
     */ 
    public function getDate()
    {
        return $this->date;
    }
}
