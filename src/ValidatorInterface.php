<?php

namespace App;

interface ValidatorInterface
{
    public function validate(array $course): array;
}
