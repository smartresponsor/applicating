<?php

declare(strict_types=1);

namespace App\Applicating\Form;

use App\Applicating\DTO\ApplicationManifestDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds and validates the manifest-edit form bound to the lifecycle manifest DTO.
 *
 * @extends AbstractType<ApplicationManifestDTO>
 */
final class ApplicationManifestType extends AbstractType
{
    /** @param array<string, mixed> $options */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('manifestVersion', null, ['label' => 'Manifest version'])
            ->add('identifier', null, ['label' => 'Identifier'])
            ->add('capabilities', TextareaType::class, [
                'label' => 'Capabilities',
                'attr' => ['rows' => 4],
                'help' => 'One capability per line.',
            ])
            ->add('permissions', TextareaType::class, [
                'label' => 'Permissions',
                'attr' => ['rows' => 4],
                'help' => 'One permission per line.',
            ])
            ->add('runtimeHooks', TextareaType::class, [
                'label' => 'Runtime hooks',
                'attr' => ['rows' => 4],
                'help' => 'One hook per line.',
            ])
            ->add('sandboxProfile', null, ['label' => 'Sandbox profile'])
            ->add('governanceState', null, ['label' => 'Governance state']);
    }

    /**
     * Binds submitted manifest fields to the validated application manifest DTO contract.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ApplicationManifestDTO::class]);
    }
}
