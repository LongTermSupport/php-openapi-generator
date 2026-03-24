<?php

declare(strict_types=1);

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;

trait ValidatorTrait
{
    /** @param array<array-key, mixed> $data */
    protected function validate(array $data, Constraint $constraint): void
    {
        $validator  = Validation::createValidator();
        $violations = $validator->validate($data, $constraint);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }
    }
}
