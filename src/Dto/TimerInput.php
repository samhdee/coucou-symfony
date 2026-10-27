<?php

namespace App\Dto;

use App\Enum\TimerStatus;
use App\Enum\TimerType;
use Symfony\Component\Validator\Constraints as Assert;

final class TimerInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $value,

        public readonly TimerType $type,

        #[Assert\Choice(choices: [TimerStatus::Running, TimerStatus::Paused])]
        public readonly TimerStatus $status,
    ) { }
}
