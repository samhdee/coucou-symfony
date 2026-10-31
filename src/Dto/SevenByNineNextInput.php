<?php

namespace App\Dto;

use App\Enum\TimerStatus;
use App\Enum\TimerType;
use Symfony\Component\Validator\Constraints as Assert;

final class SevenByNineNextInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        #[Assert\Range(
            notInRangeMessage: 'La valeur doit être comprise entre {{ min }} et {{ max }}.',
            min: 1,
            max: 7,
        )]
        public readonly int $fromPosition,
    ) { }
}
