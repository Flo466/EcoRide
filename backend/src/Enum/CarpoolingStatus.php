<?php

// src/Enum/CarpoolingStatus.php
namespace App\Enum;

enum CarpoolingStatus: string
{
    case OPEN = 'open';
    case FULL = 'full';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
}
