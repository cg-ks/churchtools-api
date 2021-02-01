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
    private $repeat = false;
    

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
            case 'with_repeat_yn':
                $this->repeat = boolval($blockData);
                break;
            default:
                parent::handleDataBlock($blockName, $blockData);
        }
    }

    public function toUpdateArray() {
        return array(
            "id" => $this->id,
            "add_date" => $this->date->format('Y-m-d H:i:s'),
            "with_repeat_yn" => strval($this->repeat)
        );
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

    /**
     * Get the value of repeat
     */ 
    public function isRepeat()
    {
        return $this->repeat;
    }
}
