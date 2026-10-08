<?php
/*
 * @copyright   Copyright (C) 2010-2025 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace TeemIp\TeemIp\Extension\PhoneNumberManagement\Model;

use Combodo\iTop\Service\Events\EventData;
use TipPNObject;

class _TipPNExtension extends TipPNObject
{
    /**
     * Event to set attribute flags.
     *
     * @param EventData $oEventData
     * @return void
     */
    public function OnTipPNExtensionSetAttributesFlagsRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        $this->AddAttributeFlags('org_id', OPT_ATT_READONLY);
        $this->AddAttributeFlags('tipphonenumber_id', OPT_ATT_READONLY);
    }

}
