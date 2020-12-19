<?php
declare(strict_types=1);

namespace ChurchTools\Api;

/**
 * A meeting request in a single calendarentry
 *
 * @author Lukas Block
 */
class MeetingRequest extends CTObject
{
    // TODO: Add in CT API - Best to copy whole object and then check later on with git diff
    private $id;
    private $calendarEntryId;
    private $personId;
    private $confirmed;

    /**
     * @overridedoc
     */
    protected function handleDataBlock($blockName, $blockData): void
    {
        switch ($blockName) {
            case 'id':
                $this->id = intval($blockData);
            break;
            case 'cal_id':
                $this->calendarEntryId = intval($blockData);
                break;
            case 'person_id':
                $this->personId = intval($blockData);
                break;
            case 'zugesagt_yn':
                $this->confirmed = $blockData;
                break;
            default:
                parent::handleDataBlock($blockName, $blockData);
        }
    }

    /**
     * @return \int id of this meeting request
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @return \int id of the parent calendar entry
     */
    public function getCalendarEntryID(): int
    {
        return $this->calendarEntryId;
    }

    /**
     * @return \int id of the person who was asked to join the meeting
     */
    public function getPersonID(): int
    {
        return $this->personId;
    }

    /**
     * @return \bool Whether the person has already confirmed the meeting request
     */
    public function isConfirmed(): bool
    {
        return boolval($this->confirmed);
    }

    /**
     * @return The confirmation status - null if not answered yet, false, if denied and true if confirmed
     */
    public function getConfirmationStatus()
    {
        if (is_null($this->confirmed)) {
            return null;
        } else {
            return $this->isConfirmed;
        }
    }

   
}
