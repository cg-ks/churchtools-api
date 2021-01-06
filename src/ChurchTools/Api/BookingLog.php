<?php
declare(strict_types=1);

namespace ChurchTools\Api;

/**
 * Booking Logs
 *
 * @author Lukas Block
 */
class BookingLog extends CTObject
{
    private $id;
    private $level;
    private $date;
    private $text;
    private $bookingID;
    private $personID;
    

    /**
     * @inheritDoc
     *
     * @param bool $hasDataBlock default value false
     */
    public function __construct(array $rawData, bool $hasDataBlock = false)
    {
        parent::__construct($rawData, $hasDataBlock);
    }

    /**
     * @inheritDoc
     */
    protected function handleDataBlock($blockName, $blockData): void
    {
        switch ($blockName) {
            case 'id':
                $this->id         = intval($blockData);
                break;
            case 'level':
                $this->level       = intval($blockData);
                break;
            case 'person_id':
                $this->personID       = intval($blockData);
                break;
            case 'txt':
                $this->text        = $blockData;
                break;
            case 'booking_id':
                $this->bookingID        = intval($blockData);
                break;
            case "datum":
                $this->date = $this->parseDateTime($blockData);
                break;
            default:
                parent::handleDataBlock($blockName, $blockData);
        }
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of level
     */ 
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Get the value of date
     */ 
    public function getDate()
    {
        return clone $this->date;
    }

    /**
     * Get the value of text
     */ 
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get the value of bookingID
     */ 
    public function getBookingID()
    {
        return $this->bookingID;
    }

    /**
     * Get the value of personID
     */ 
    public function getPersonID()
    {
        return $this->personID;
    }
}
