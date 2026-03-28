<?php

declare(strict_types=1);

namespace App\Federation\Product;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

final class ScopeValidator
{
    public static function assertValid(array $s): void
    {
        $v = Validation::createValidator();
        $c = new Assert\Collection(['fields' => ['env' => new Assert\Optional(new Assert\Choice(['dev', 'staging', 'prod'])), 'tags' => new Assert\Optional(new Assert\All([new Assert\Type('string'), new Assert\Length(max: 64)])), 'features' => new Assert\Optional(new Assert\All([new Assert\Type('string')])), 'labels' => new Assert\Optional(new Assert\Type('array'))], 'allowExtraFields' => false, 'allowMissingFields' => true]);
        $viol = $v->validate($s, $c);
        if ($viol->count() > 0) {
            $m = [];
            foreach ($viol as $x) {
                $m[] = $x->getPropertyPath().': '.$x->getMessage();
            }throw new \InvalidArgumentException('Invalid scope: '.implode('; ', $m));
        }
    }
}
