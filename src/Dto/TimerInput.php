<?php

namespace App\Dto;

use App\Enum\TimerStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class TimerInput
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $label,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $value,

        #[Assert\Choice(choices: [TimerStatus::Running, TimerStatus::Paused])]
        public readonly TimerStatus $status,
    ) { }
}
