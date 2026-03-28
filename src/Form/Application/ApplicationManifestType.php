<?php

declare(strict_types=1);

namespace App\Form\Application;

use App\DTO\Application\ApplicationManifestData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ApplicationManifestType extends AbstractType
{
    /** @param array<string, mixed> $options */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('manifestVersion')
            ->add('identifier')
            ->add('capabilities', TextareaType::class, ['attr' => ['rows' => 4], 'help' => 'One capability per line.'])
            ->add('permissions', TextareaType::class, ['attr' => ['rows' => 4], 'help' => 'One permission per line.'])
            ->add('runtimeHooks', TextareaType::class, ['attr' => ['rows' => 4], 'help' => 'One hook per line.'])
            ->add('sandboxProfile')
            ->add('governanceState');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ApplicationManifestData::class]);
    }
}
