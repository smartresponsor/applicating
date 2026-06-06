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
            ->add('tenantKey', null, ['label' => 'Tenant key'])
            ->add('installedVersion', null, ['label' => 'Installed version'])
            ->add('enabled', CheckboxType::class, ['required' => false, 'label' => 'Enabled'])
            ->add('billingActive', CheckboxType::class, ['required' => false, 'label' => 'Billing active'])
            ->add('accessPolicy', TextareaType::class, [
                'label' => 'Access policy',
                'attr' => ['rows' => 6],
                'help' => 'JSON policy blob used by the tenant runtime.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => TenantApplicationAssignmentData::class]);
    }
}
