<?php

declare(strict_types=1);

namespace App\Applicating\Form\Application;

use App\Applicating\DTO\Application\ApplicationUpsertData;
use App\Applicating\Enum\ApplicationAccessLevel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ApplicationType extends AbstractType
{
    /** @param array<string, mixed> $options */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nameEntity', null, ['label' => 'Application nameEntity'])
            ->add('slug', null, ['label' => 'Slug'])
            ->add('packageName', null, ['label' => 'Package nameEntity'])
            ->add('developerName', null, ['label' => 'Developer nameEntity'])
            ->add('listingSummary', TextareaType::class, [
                'label' => 'Listing summary',
                'attr' => ['rows' => 4],
                'help' => 'Short marketplace summary used in listings and catalog views.',
            ])
            ->add('accessLevel', ChoiceType::class, [
                'label' => 'Access level',
                'choices' => [
                    'Public' => ApplicationAccessLevel::Public->value,
                    'Private' => ApplicationAccessLevel::Private->value,
                    'Tenant Restricted' => ApplicationAccessLevel::TenantRestricted->value,
                ],
                'placeholder' => 'Choose access level',
            ])
            ->add('billingCode', null, ['label' => 'Billing code', 'required' => false])
            ->add('sandboxProfile', null, ['label' => 'Sandbox profile'])
            ->add('enabledByDefault', CheckboxType::class, ['required' => false, 'label' => 'Enabled by default']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApplicationUpsertData::class,
        ]);
    }
}
