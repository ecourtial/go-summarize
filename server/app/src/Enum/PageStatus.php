<?php

namespace App\Enum;

enum PageStatus: string
{
    case WAITING_FOR_DECISION = 'PENDING';
    case DISCARDED = 'DISCARDED';
    case TO_READ = 'TO_READ';
    case TO_SUMMARIZE = 'TO_SUMMARIZE';
    case DONE = 'DONE';
}
