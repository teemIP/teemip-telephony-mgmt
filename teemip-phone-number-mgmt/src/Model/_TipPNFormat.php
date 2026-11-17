<?php
/*
 * @copyright   Copyright (C) 2010-2026 TeemIp
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

namespace TeemIp\TeemIp\Extension\PhoneNumberManagement\Model;

use Combodo\iTop\Service\Events\EventData;
use Dict;
use MetaModel;
use Typology;

/**
 * Display format (ITU-T E.123) of the national numbers of a country that have a given length and start with given digits
 */
class _TipPNFormat extends Typology
{
    /**
     * Handler for EVENT_DB_CHECK_TO_WRITE event
     *   . Pattern must contain as many digits as the national number
     *   . Leading digits must be shorter than the national number
     *   . National number and calling code must not exceed the 15 digits allowed by E.164
     *
     * @param EventData $oEventData
     * @return void
     * @throws \ArchivedObjectException
     * @throws \CoreException
     */
    public function OnTipPNFormatCheckToWriteRequestedByPhoneNumberMgmt(EventData $oEventData): void
    {
        $iLength = (int)$this->Get('length');
        if ($iLength < 1) {
            $this->AddCheckIssue(Dict::S('UI:PNManagement:Action:New:TipPNFormat:WrongLength'));
            return;
        }
        if (substr_count($this->Get('pattern'), '#') != $iLength) {
            $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPNFormat:PatternDoesNotMatchLength', $iLength));
            return;
        }
        if (strlen($this->Get('leading_digits') ?? '') >= $iLength) {
            $this->AddCheckIssue(Dict::S('UI:PNManagement:Action:New:TipPNFormat:LeadingDigitsTooLong'));
            return;
        }
        $oPNCountryCode = MetaModel::GetObject('TipPNCountryCode', $this->Get('tippncountrycode_id'), false /* MustBeFound */);
        if (($oPNCountryCode !== null) && (strlen($oPNCountryCode->Get('calling_code')) + $iLength > 15)) {
            $this->AddCheckIssue(Dict::Format('UI:PNManagement:Action:New:TipPNFormat:NumberTooLong', $oPNCountryCode->Get('calling_code')));
        }
    }
}
