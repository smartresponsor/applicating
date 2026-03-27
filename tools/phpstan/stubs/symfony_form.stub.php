<?php

declare(strict_types=1);

namespace Symfony\Component\Form;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractType
{
}

interface FormBuilderInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function add(string $child, ?string $type = null, array $options = []): self;
}

interface FormInterface
{
    public function handleRequest(Request $request): static;

    public function isSubmitted(): bool;

    public function isValid(): bool;

    public function createView(): FormView;
}

final class FormView
{
}

namespace Symfony\Component\Form\Extension\Core\Type;

final class TextType
{
}
