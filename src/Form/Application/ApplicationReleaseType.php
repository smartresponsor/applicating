<?php

declare(strict_types=1);

namespace App\Application\Form\Application;

use App\Application\DTO\Application\ApplicationReleaseData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ApplicationReleaseType extends AbstractType
{
    /** @param array<string, mixed> $options */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('version')
            ->add('channel')
            ->add('checksum')
            ->add('downloadUrl')
            ->add('releaseNotes', TextareaType::class, ['attr' => ['rows' => 5]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ApplicationReleaseData::class]);
    }
}
