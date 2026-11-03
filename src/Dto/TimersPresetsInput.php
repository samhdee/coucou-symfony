<?php

namespace App\Dto;

use App\Enum\TimerStatus;
use App\Enum\TimerType;
use Symfony\Component\Validator\Constraints as Assert;

final class TimersPresetsInput
{
    public function __construct(
        public readonly TimerType $type,
    ) { }
}
