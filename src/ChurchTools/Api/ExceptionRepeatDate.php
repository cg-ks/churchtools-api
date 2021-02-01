<?php
declare(strict_types=1);

namespace ChurchTools\Api;

/**
 * Represents an additional exception date of a recurring CT Object (CTRepeatingObject)
 * 
 * @author Lukas Block
 */

class ExceptionRepeatDate extends CTObject
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
            case 'except_date_start':
                $this->date = $this->parseDateTime($blockData);
                break;
            case 'except_date_end':
                $this->date = $this->parseDateTime($blockData);
                break;
            default:
                parent::handleDataBlock($blockName, $blockData);
        }
    }

    public function toUpdateArray() {
        return array(
            "id" => $this->id,
            "except_date_start" => $this->date->format('Y-m-d H:i:s'),
            "except_date_end" => $this->date->format('Y-m-d H:i:s'),
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
}
