<?php

declare(strict_types=1);

namespace App\Applicating\Service\Config;

use App\Administering\Service\Config\ConfigApplyService;
use App\Administering\Service\Config\ConfigFileWriterService;
use App\Administering\ServiceInterface\Config\AdministrationConfigToolServiceInterface;
use App\Administering\Value\Config\AdministrationConfigToolDescriptor;
use App\Applicating\Form\Config\ApplicatingFrameworkConfigFormType;
use App\Applicating\Value\Form\Config\ApplicatingFrameworkConfigData;
use Symfony\Component\Yaml\Yaml;

final readonly class ApplicatingFrameworkConfigService implements AdministrationConfigToolServiceInterface
{
    public function __construct(
        private string $projectDir,
        private ConfigApplyService $applyService,
        private ConfigFileWriterService $fileWriter,
    ) {
    }

    public function descriptor(): AdministrationConfigToolDescriptor
    {
        return new AdministrationConfigToolDescriptor(
            applicationCode: 'Applicating',
            toolCode: 'applicating.framework',
            label: 'Applicating Framework',
            description: 'Safe Symfony framework flags and rate-limiter settings stored in applicating_framework.yaml.',
            formClass: ApplicatingFrameworkConfigFormType::class,
            serviceClass: self::class,
            requiredPermission: 'administration.config.update',
            editableFields: [
                'csrfProtectionEnabled',
                'formEnabled',
                'validationEnabled',
                'sessionCookieSecure',
                'sessionCookieSameSite',
                'loginThrottleLimit',
                'loginThrottleIntervalMinutes',
                'adminApiThrottleLimit',
                'adminApiThrottleAmount',
                'adminApiThrottleIntervalMinutes',
            ],
            sensitiveFields: [],
            readableFiles: ['config/packages/applicating_framework.yaml'],
            writableFiles: ['config/packages/applicating_framework.yaml'],
            metadata: [
                'section' => 'Configuration',
                'kind' => 'framework',
            ],
            secretNames: [],
            applyStrategy: 'component_yaml',
        );
    }

    public function loadData(): object
    {
        $data = new ApplicatingFrameworkConfigData();
        $manifest = $this->frameworkManifest();
        $framework = is_array($manifest['framework'] ?? null) ? $manifest['framework'] : [];

        $data->csrfProtectionEnabled = !empty($framework['csrf_protection']['enabled'] ?? true) ? '1' : '0';
        $data->formEnabled = !empty($framework['form']['enabled'] ?? true) ? '1' : '0';
        $data->validationEnabled = !empty($framework['validation']['enabled'] ?? true) ? '1' : '0';
        $data->sessionCookieSecure = (string) ($framework['session']['cookie_secure'] ?? $data->sessionCookieSecure);
        $data->sessionCookieSameSite = (string) ($framework['session']['cookie_samesite'] ?? $data->sessionCookieSameSite);
        $data->loginThrottleLimit = (string) ($framework['rate_limiter']['applicating_login']['limit'] ?? $data->loginThrottleLimit);
        $data->loginThrottleIntervalMinutes = $this->intervalToMinutes((string) ($framework['rate_limiter']['applicating_login']['interval'] ?? '15 minutes'));
        $data->adminApiThrottleLimit = (string) ($framework['rate_limiter']['applicating_admin_api']['limit'] ?? $data->adminApiThrottleLimit);
        $data->adminApiThrottleAmount = (string) ($framework['rate_limiter']['applicating_admin_api']['rate']['amount'] ?? $data->adminApiThrottleAmount);
        $data->adminApiThrottleIntervalMinutes = $this->intervalToMinutes((string) ($framework['rate_limiter']['applicating_admin_api']['rate']['interval'] ?? '1 minute'));

        return $data;
    }

    public function save(object $data, array $context = []): array
    {
        $payload = $this->assertData($data);
        $values = $this->stateRows($payload, 'pending');
        $masked = [
            'framework_csrf_protection_enabled' => $payload->csrfProtectionEnabled,
            'framework_form_enabled' => $payload->formEnabled,
            'framework_validation_enabled' => $payload->validationEnabled,
            'framework_session_cookie_secure' => $payload->sessionCookieSecure,
            'framework_session_cookie_same_site' => $payload->sessionCookieSameSite,
            'framework_login_throttle_limit' => $payload->loginThrottleLimit,
            'framework_login_throttle_interval_minutes' => $payload->loginThrottleIntervalMinutes,
            'framework_admin_api_throttle_limit' => $payload->adminApiThrottleLimit,
            'framework_admin_api_throttle_amount' => $payload->adminApiThrottleAmount,
            'framework_admin_api_throttle_interval_minutes' => $payload->adminApiThrottleIntervalMinutes,
        ];

        return $this->applyService->save($this->descriptor(), (string) ($context['actor'] ?? 'system'), $values, $masked, []);
    }

    public function apply(object $data, array $context = []): array
    {
        $payload = $this->assertData($data);
        $patch = $this->frameworkPatch($payload);
        $write = $this->fileWriter->write(
            $this->projectDir.'/../Applicating',
            'config/packages/applicating_framework.yaml',
            $patch,
            $this->descriptor()->writableFiles,
        );

        $status = 'applied' === $write['status'] ? 'applied' : 'failed';
        $values = $this->stateRows($payload, $status);

        return $this->applyService->apply(
            $this->descriptor(),
            (string) ($context['actor'] ?? 'system'),
            $values,
            $patch,
            [],
            [[
                'path' => $write['path'],
                'backup_path' => $write['backup_path'],
                'status' => $write['status'],
                'message' => $write['message'],
            ]],
            [],
            'applied' === $write['status'] ? null : $write['message'],
            $status,
        );
    }

    private function assertData(object $data): ApplicatingFrameworkConfigData
    {
        if (!$data instanceof ApplicatingFrameworkConfigData) {
            throw new \InvalidArgumentException('Applicating framework config expects ApplicatingFrameworkConfigData.');
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function frameworkManifest(): array
    {
        $path = $this->projectDir.'/../Applicating/config/packages/applicating_framework.yaml';
        $parsed = is_file($path) ? Yaml::parseFile($path) : [];

        return is_array($parsed) ? $parsed : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function frameworkPatch(ApplicatingFrameworkConfigData $data): array
    {
        return [
            'framework' => [
                'secret' => '%env(APP_SECRET)%',
                'http_method_override' => false,
                'handle_all_throwables' => true,
                'form' => [
                    'enabled' => '1' === $data->formEnabled,
                    'csrf_protection' => [
                        'enabled' => '1' === $data->csrfProtectionEnabled,
                    ],
                ],
                'validation' => [
                    'enabled' => '1' === $data->validationEnabled,
                    'email_validation_mode' => 'html5',
                ],
                'session' => [
                    'handler_id' => null,
                    'cookie_secure' => 'true' === $data->sessionCookieSecure ? true : ('false' === $data->sessionCookieSecure ? false : 'auto'),
                    'cookie_samesite' => $data->sessionCookieSameSite,
                ],
                'router' => [
                    'utf8' => true,
                ],
                'rate_limiter' => [
                    'applicating_login' => [
                        'policy' => 'sliding_window',
                        'limit' => (int) $data->loginThrottleLimit,
                        'interval' => sprintf('%d minutes', max(1, (int) $data->loginThrottleIntervalMinutes)),
                    ],
                    'applicating_admin_api' => [
                        'policy' => 'token_bucket',
                        'limit' => (int) $data->adminApiThrottleLimit,
                        'rate' => [
                            'interval' => sprintf('%d minutes', max(1, (int) $data->adminApiThrottleIntervalMinutes)),
                            'amount' => (int) $data->adminApiThrottleAmount,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{fieldType:string, secret:bool, current:?string, pending:?string, masked:?string, status:string}>
     */
    private function stateRows(ApplicatingFrameworkConfigData $data, string $status): array
    {
        return [
            'framework_csrf_protection_enabled' => ['fieldType' => 'checkbox', 'secret' => false, 'current' => $data->csrfProtectionEnabled, 'pending' => $data->csrfProtectionEnabled, 'masked' => null, 'status' => $status],
            'framework_form_enabled' => ['fieldType' => 'checkbox', 'secret' => false, 'current' => $data->formEnabled, 'pending' => $data->formEnabled, 'masked' => null, 'status' => $status],
            'framework_validation_enabled' => ['fieldType' => 'checkbox', 'secret' => false, 'current' => $data->validationEnabled, 'pending' => $data->validationEnabled, 'masked' => null, 'status' => $status],
            'framework_session_cookie_secure' => ['fieldType' => 'choice', 'secret' => false, 'current' => $data->sessionCookieSecure, 'pending' => $data->sessionCookieSecure, 'masked' => null, 'status' => $status],
            'framework_session_cookie_same_site' => ['fieldType' => 'choice', 'secret' => false, 'current' => $data->sessionCookieSameSite, 'pending' => $data->sessionCookieSameSite, 'masked' => null, 'status' => $status],
            'framework_login_throttle_limit' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->loginThrottleLimit, 'pending' => $data->loginThrottleLimit, 'masked' => null, 'status' => $status],
            'framework_login_throttle_interval_minutes' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->loginThrottleIntervalMinutes, 'pending' => $data->loginThrottleIntervalMinutes, 'masked' => null, 'status' => $status],
            'framework_admin_api_throttle_limit' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->adminApiThrottleLimit, 'pending' => $data->adminApiThrottleLimit, 'masked' => null, 'status' => $status],
            'framework_admin_api_throttle_amount' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->adminApiThrottleAmount, 'pending' => $data->adminApiThrottleAmount, 'masked' => null, 'status' => $status],
            'framework_admin_api_throttle_interval_minutes' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->adminApiThrottleIntervalMinutes, 'pending' => $data->adminApiThrottleIntervalMinutes, 'masked' => null, 'status' => $status],
        ];
    }

    private function intervalToMinutes(string $interval): string
    {
        if (preg_match('/(\\d+)\\s+minute/', $interval, $matches)) {
            return (string) $matches[1];
        }

        return '1';
    }
}
