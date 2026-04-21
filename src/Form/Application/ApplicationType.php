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
            ->add('name')
            ->add('slug')
            ->add('packageName')
            ->add('developerName')
            ->add('listingSummary', TextareaType::class, ['attr' => ['rows' => 4]])
            ->add('accessLevel', ChoiceType::class, [
                'choices' => [
                    'Public' => ApplicationAccessLevel::Public->value,
                    'Private' => ApplicationAccessLevel::Private->value,
                    'Tenant Restricted' => ApplicationAccessLevel::TenantRestricted->value,
                ],
            ])
            ->add('billingCode')
            ->add('sandboxProfile')
            ->add('enabledByDefault', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApplicationUpsertData::class,
        ]);
    }
}
