<?php

declare(strict_types=1);

namespace App\Applicating\Form\Application;

use App\Applicating\DTO\Application\TenantApplicationAssignmentData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TenantApplicationAssignmentType extends AbstractType
{
    /** @param array<string, mixed> $options */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tenantKey')
            ->add('installedVersion')
            ->add('enabled', CheckboxType::class, ['required' => false])
            ->add('billingActive', CheckboxType::class, ['required' => false])
            ->add('accessPolicy', TextareaType::class, ['attr' => ['rows' => 6]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => TenantApplicationAssignmentData::class]);
    }
}
