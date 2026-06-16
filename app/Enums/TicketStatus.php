<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';
}
