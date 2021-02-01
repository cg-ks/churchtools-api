<?php
declare(strict_types=1);

namespace ChurchTools\Api;

/**
 * A single resource booking of a specific Resource
 *
 * @author André Schild
 */
class Booking extends CTRepeatingObject
{
    private $id;
    private $preTime;
    private $postTime;
    private $resourceID;
    private $statusID;
    private $location;
    private $remarks;
    private $title;
    private $personID;
    private $createDate;
    private $modifiedDate;
    private $version;
    private $calendarEntryID;
    private $calendarID;

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
            case 'minpre':
                $this->preTime    = intval($blockData);
                break;
            case 'minpost':
                $this->postTime   = intval($blockData);
                break;
            case 'resource_id':
                $this->resourceID = intval($blockData);
                break;
            case 'status_id':
                $this->statusID   = intval($blockData);
                break;
            case 'person_id':
                $this->personID = intval($blockData);
                break;
            case 'location':
                $this->location   = $blockData;
                break;
            case 'note':
                $this->remarks       = $blockData;
                break;
            case 'text':
                $this->title       = $blockData;
                break;
            case 'version':
                $this->version       = $blockData;
                break;
            case 'create_date':
                $this->createDate    = $this->parseDateTime($blockData);
                break;
            case 'modified_date':
                $this->modifiedDate      = $this->parseDateTime($blockData);
                break;
            case 'cc_cal_id':
                $this->calendarEntryID      = intval($blockData);
                break;
            case 'category_id':
                $this->calendarID      = intval($blockData);
                break;
            default:
                parent::handleDataBlock($blockName, $blockData);
        }
    }

    /**
     * @return int ID of this booking
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @return the amount of minutes this resource should be reserved before the event starts
     */
    public function getPreTime(): int
    {
        return $this->preTime;
    }

    /**
     * @return the amount of minutes this resource should be reserved after the event ends
     */
    public function getPostTime(): int
    {
        return $this->postTime;
    }

    /**
     * @return int ID of the resource we are booking
     */
    public function getResourceID(): int
    {
        return $this->resourceID;
    }

    /**
     * @return int ID of the status of the booking
     */
    public function getStatusID(): int
    {
        return $this->statusID;
    }

    /**
     * @return string|null Location of the booking
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @return string|null note of the booking
     */
    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    /**
     * Experimental feature to set the remark. This does not directly update churchtools.
     * To sync this local instance with churchtools, call sync.
     */
    public function setRemarks(string $remarks)
    {
        $this->remarks = $remarks;
    }

    /**
     * @return string title of calenda entry
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int the id of the person who did the booking
     */
    public function getPersonID(): int
    {
        return $this->personID;
    }
    
    /**
     * @return the version this booking information is based on
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return DateTime the time this booking has been created
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    public function isConfirmed(): bool
    {
        return BookingStatusType::isConfirmed($this->statusID);
    }
    
    public function isWaitingConfirmation(): bool
    {
        return BookingStatusType::isWaitingConfirmation($this->statusID);
    }

    public function isRejected(): bool
    {
        return BookingStatusType::isRejected($this->statusID == 3);
    }

    public function isDeleted(): bool
    {
        return BookingStatusType::isDeleted($this->statusID == 99);
    }
    
    public function getCalendarEntryID(): ?int
    {
        return $this->calendarEntryID;
    }
    
    public function getCalendarID(): ?int
    {
        return $this->calendarID;
    }

    public function toUpdateArray() {
        $modifiedDate = new \DateTime();
        
        // The param array of all class variables
        $paramArray = [
            'startdate' => $this->getStartDate()->format('Y-m-d H:i:s'),
            'enddate' => $this->getEndDate()->format('Y-m-d H:i:s'),
            'id' => $this->getID(),
            'old_id' => $this->getID(),
            'resource_id' => $this->getResourceID(),
            'person_id' => $this->getPersonID(),
            'status_id' => $this->getStatusID(),
            'text' => $this->getTitle(),
            'location' => $this->getLocation(),
            'note'  => $this->remarks,
            'modified_date' => $modifiedDate->format('Y-m-d H:i:s'),
            'create_date' => $this->getCreateDate()->format('Y-m-d H:i:s'),
            'version' => $this->getVersion(),
            'currentEvent_id' => $this->getID(),
            'station_id' => $this->rawDataBlocks['station_id']
        ];

        // Now add the repeat information
        $repeatArray = $this->repeatType->toUpdateArray();
        $paramArray = array_merge($paramArray, $repeatArray);

        return $paramArray;
    }

    public function toUpdateArrayWithCalendarEntry() {
        // This booking is associated with a calendar entry
        $modifiedDate = new \DateTime();

        // This time it is like updating the event and not the booking itself
        $calStartDate = $this->parseDateTime($this->rawDataBlocks["cal_startdate"]);
        $calEndDate = $this->parseDateTime($this->rawDataBlocks["cal_enddate"]);

        $minpre = ($calStartDate->getTimestamp() - $this->getStartDate()->getTimestamp())/60;
        $minpost = ($this->getEndDate()->getTimestamp() - $calEndDate->getTimestamp())/60;

        // The param array of all class variables
        $paramArray = [
            'startdate' => $calStartDate->format('Y-m-d H:i:s'),
            'enddate' => $calEndDate->format('Y-m-d H:i:s'),
            'id' => $this->getCalendarEntryID(),
            'cc_cal_id' => $this->getCalendarEntryID(),
            // The old id is still the old booking id
            'old_id' => $this->getID(),
            'person_id' => $this->getPersonID(),
            'text' => $this->getTitle(),
            'bezeichnung' => $this->getTitle(),
            'modified_date' => $modifiedDate->format('Y-m-d H:i:s'),
            'create_date' => $this->getCreateDate()->format('Y-m-d H:i:s'),
            'version' => $this->getVersion(),
            'category_id' => $this->getCalendarID(),
            'currentEvent_id' => $this->getCalendarEntryID(),
            'station_id' => $this->rawDataBlocks['station_id'],
            'cal_startdate' => $calStartDate->format('Y-m-d H:i:s'),
            'cal_enddate' => $calEndDate->format('Y-m-d H:i:s'),
            'booking_id' => $this->getID(),
            'bookings' => array($this->getID() => array(
                'id' => $this->getID(),
                'resource_id' => $this->getResourceID(),
                'status_id' => $this->getStatusID(),
                'location' => $this->getLocation(),
                'note'  => $this->remarks,      
                'minpre' => $minpre,
                'minpost' => $minpost
            )
        )
        ];


        // Now add the repeat information
        $repeatArray = $this->repeatType->toUpdateArray();
        $paramArray = array_merge($paramArray, $repeatArray);

        return $paramArray;     
    }

    /**
     * Experimental feature to sync this local instance and all its changes back to churchtools
     * @param RestAPI The churchtools RestAPI instance that should perform the sync call
     */
    public function sync($api) {
        $paramArray = NULL;

        if (is_null($this->getCalendarID())) {
            // No calendar associated with this
            $paramArray = $this->toUpdateArray();
            // The default parameters for a update booking call
            $paramArray2 = array(
                'name' => "Booking",
                'neu' => 'false',
                'func' => 'updateBooking',
            );
            // Add the raw data blocks
            $paramArray = array_merge($paramArray, $paramArray2);
        } else {
            // This booking has a calendar associated with it
            $paramArray = $this->toUpdateArrayWithCalendarEntry();
            // The default parameters for a update booking call
            $paramArray2 = array(
                'name' => "Event",
                'neu' => 'false',
                'func' => 'updateEvent',
            );
            // Add the raw data blocks
            $paramArray = array_merge($paramArray, $paramArray2);
        }
        // Now call the API
        $rawData = $api->callApi('churchresource/ajax', $paramArray);
        return $rawData;
    }
}
