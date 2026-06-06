<?php

declare(strict_types=1);

namespace App\Applicating\Form\Config;

use App\Applicating\Value\Form\Config\ApplicatingFrameworkConfigData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ApplicatingFrameworkConfigFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('csrfProtectionEnabled', CheckboxType::class, [
                'label' => 'FRAMEWORK_CSRF_PROTECTION_ENABLED',
                'required' => false,
            ])
            ->add('formEnabled', CheckboxType::class, [
                'label' => 'FRAMEWORK_FORM_ENABLED',
                'required' => false,
            ])
            ->add('validationEnabled', CheckboxType::class, [
                'label' => 'FRAMEWORK_VALIDATION_ENABLED',
                'required' => false,
            ])
            ->add('sessionCookieSecure', ChoiceType::class, [
                'label' => 'FRAMEWORK_SESSION_COOKIE_SECURE',
                'choices' => [
                    'Auto' => 'auto',
                    'True' => true,
                    'False' => false,
                ],
                'required' => true,
            ])
            ->add('sessionCookieSameSite', ChoiceType::class, [
                'label' => 'FRAMEWORK_SESSION_COOKIE_SAMESITE',
                'choices' => [
                    'Lax' => 'lax',
                    'Strict' => 'strict',
                    'None' => 'none',
                ],
                'required' => true,
            ])
            ->add('loginThrottleLimit', IntegerType::class, [
                'label' => 'FRAMEWORK_LOGIN_THROTTLE_LIMIT',
                'required' => true,
                'empty_data' => '5',
            ])
            ->add('loginThrottleIntervalMinutes', IntegerType::class, [
                'label' => 'FRAMEWORK_LOGIN_THROTTLE_INTERVAL_MINUTES',
                'required' => true,
                'empty_data' => '15',
            ])
            ->add('adminApiThrottleLimit', IntegerType::class, [
                'label' => 'FRAMEWORK_ADMIN_API_THROTTLE_LIMIT',
                'required' => true,
                'empty_data' => '60',
            ])
            ->add('adminApiThrottleAmount', IntegerType::class, [
                'label' => 'FRAMEWORK_ADMIN_API_THROTTLE_AMOUNT',
                'required' => true,
                'empty_data' => '60',
            ])
            ->add('adminApiThrottleIntervalMinutes', IntegerType::class, [
                'label' => 'FRAMEWORK_ADMIN_API_THROTTLE_INTERVAL_MINUTES',
                'required' => true,
                'empty_data' => '1',
            ])
            ->add('save', SubmitType::class, ['label' => 'Save pending'])
            ->add('apply', SubmitType::class, ['label' => 'Apply now', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApplicatingFrameworkConfigData::class,
        ]);
    }
}
