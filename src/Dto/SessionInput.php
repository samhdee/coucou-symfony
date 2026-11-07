<?php

namespace App\Dto;

use App\Enum\TimerStatus;
use App\Enum\TimerType;
use Symfony\Component\Validator\Constraints as Assert;

final class SessionInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $fromTimer,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $value,

        public readonly TimerType $type,
    ) { }
}
