<?php

declare(strict_types=1);

namespace App\Applicating\Service\Config;

use App\Administering\Service\Config\AdministrationConfigApplyService;
use App\Administering\Service\Config\AdministrationConfigFileWriterService;
use App\Administering\ServiceInterface\Config\AdministrationConfigToolServiceInterface;
use App\Administering\Value\Config\AdministrationConfigToolDescriptor;
use App\Applicating\Form\Config\ApplicationFrameworkConfigData;
use App\Applicating\Form\Config\ApplicationFrameworkConfigFormType;
use Symfony\Component\Yaml\Yaml;

final readonly class ApplicationFrameworkConfigService implements AdministrationConfigToolServiceInterface
{
    public function __construct(
        private string $projectDir,
        private AdministrationConfigApplyService $applyService,
        private AdministrationConfigFileWriterService $fileWriter,
    ) {
    }

    public function descriptor(): AdministrationConfigToolDescriptor
    {
        return new AdministrationConfigToolDescriptor(
            applicationCode: 'Applicating',
            toolCode: 'applicating.framework',
            label: 'Applicating Framework',
            description: 'Safe Symfony framework flags and rate-limiter settings stored in application_framework.yaml.',
            formClass: ApplicationFrameworkConfigFormType::class,
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
            readableFiles: ['config/packages/application_framework.yaml'],
            writableFiles: ['config/packages/application_framework.yaml'],
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
        $data = new ApplicationFrameworkConfigData();
        $manifest = $this->frameworkManifest();
        $framework = $this->arrayValue($manifest, 'framework');
        $csrfProtection = $this->arrayValue($framework, 'csrf_protection');
        $form = $this->arrayValue($framework, 'form');
        $validation = $this->arrayValue($framework, 'validation');
        $session = $this->arrayValue($framework, 'session');
        $rateLimiter = $this->arrayValue($framework, 'rate_limiter');
        $loginLimiter = $this->arrayValue($rateLimiter, 'applicating_login');
        $adminApiLimiter = $this->arrayValue($rateLimiter, 'applicating_admin_api');
        $adminApiRate = $this->arrayValue($adminApiLimiter, 'rate');

        $data->csrfProtectionEnabled = !empty($csrfProtection['enabled'] ?? true);
        $data->formEnabled = !empty($form['enabled'] ?? true);
        $data->validationEnabled = !empty($validation['enabled'] ?? true);
        $data->sessionCookieSecure = $this->scalarString($session['cookie_secure'] ?? null, $data->sessionCookieSecure);
        $data->sessionCookieSameSite = $this->scalarString($session['cookie_samesite'] ?? null, $data->sessionCookieSameSite);
        $data->loginThrottleLimit = $this->scalarString($loginLimiter['limit'] ?? null, $data->loginThrottleLimit);
        $data->loginThrottleIntervalMinutes = $this->intervalToMinutes($this->scalarString($loginLimiter['interval'] ?? null, '15 minutes'));
        $data->adminApiThrottleLimit = $this->scalarString($adminApiLimiter['limit'] ?? null, $data->adminApiThrottleLimit);
        $data->adminApiThrottleAmount = $this->scalarString($adminApiRate['amount'] ?? null, $data->adminApiThrottleAmount);
        $data->adminApiThrottleIntervalMinutes = $this->intervalToMinutes($this->scalarString($adminApiRate['interval'] ?? null, '1 minute'));

        return $data;
    }

    public function save(object $data, array $context = []): array
    {
        $payload = $this->assertData($data);
        $values = $this->stateRows($payload, 'pending');
        $masked = [
            'framework_csrf_protection_enabled' => $this->boolFlag($payload->csrfProtectionEnabled),
            'framework_form_enabled' => $this->boolFlag($payload->formEnabled),
            'framework_validation_enabled' => $this->boolFlag($payload->validationEnabled),
            'framework_session_cookie_secure' => $payload->sessionCookieSecure,
            'framework_session_cookie_same_site' => $payload->sessionCookieSameSite,
            'framework_login_throttle_limit' => $payload->loginThrottleLimit,
            'framework_login_throttle_interval_minutes' => $payload->loginThrottleIntervalMinutes,
            'framework_admin_api_throttle_limit' => $payload->adminApiThrottleLimit,
            'framework_admin_api_throttle_amount' => $payload->adminApiThrottleAmount,
            'framework_admin_api_throttle_interval_minutes' => $payload->adminApiThrottleIntervalMinutes,
        ];

        return $this->normalizeResult($this->applyService->save($this->descriptor(), $this->actor($context), $values, $masked, []));
    }

    public function apply(object $data, array $context = []): array
    {
        $payload = $this->assertData($data);
        $patch = $this->frameworkPatch($payload);
        $write = $this->fileWriter->write(
            $this->projectDir.'/../Applicating',
            'config/packages/application_framework.yaml',
            $patch,
            $this->descriptor()->writableFiles,
        );

        $status = 'applied' === $write['status'] ? 'applied' : 'failed';
        $values = $this->stateRows($payload, $status);

        return $this->normalizeResult($this->applyService->apply(
            $this->descriptor(),
            $this->actor($context),
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
        ));
    }

    private function assertData(object $data): ApplicationFrameworkConfigData
    {
        if (!$data instanceof ApplicationFrameworkConfigData) {
            throw new \InvalidArgumentException('Applicating framework config expects ApplicatingFrameworkConfigData.');
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function frameworkManifest(): array
    {
        $path = $this->projectDir.'/../Applicating/config/packages/application_framework.yaml';
        $parsed = is_file($path) ? Yaml::parseFile($path) : [];

        return $this->stringKeyedArray($parsed);
    }

    /**
     * @return array<string, mixed>
     */
    private function frameworkPatch(ApplicationFrameworkConfigData $data): array
    {
        return [
            'framework' => [
                'secret' => '%env(APP_SECRET)%',
                'http_method_override' => false,
                'handle_all_throwables' => true,
                'form' => [
                    'enabled' => $data->formEnabled,
                    'csrf_protection' => [
                        'enabled' => $data->csrfProtectionEnabled,
                    ],
                ],
                'validation' => [
                    'enabled' => $data->validationEnabled,
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
    private function stateRows(ApplicationFrameworkConfigData $data, string $status): array
    {
        return [
            'framework_csrf_protection_enabled' => ['fieldType' => 'checkbox', 'secret' => false, 'current' => $this->boolFlag($data->csrfProtectionEnabled), 'pending' => $this->boolFlag($data->csrfProtectionEnabled), 'masked' => null, 'status' => $status],
            'framework_form_enabled' => ['fieldType' => 'checkbox', 'secret' => false, 'current' => $this->boolFlag($data->formEnabled), 'pending' => $this->boolFlag($data->formEnabled), 'masked' => null, 'status' => $status],
            'framework_validation_enabled' => ['fieldType' => 'checkbox', 'secret' => false, 'current' => $this->boolFlag($data->validationEnabled), 'pending' => $this->boolFlag($data->validationEnabled), 'masked' => null, 'status' => $status],
            'framework_session_cookie_secure' => ['fieldType' => 'choice', 'secret' => false, 'current' => $data->sessionCookieSecure, 'pending' => $data->sessionCookieSecure, 'masked' => null, 'status' => $status],
            'framework_session_cookie_same_site' => ['fieldType' => 'choice', 'secret' => false, 'current' => $data->sessionCookieSameSite, 'pending' => $data->sessionCookieSameSite, 'masked' => null, 'status' => $status],
            'framework_login_throttle_limit' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->loginThrottleLimit, 'pending' => $data->loginThrottleLimit, 'masked' => null, 'status' => $status],
            'framework_login_throttle_interval_minutes' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->loginThrottleIntervalMinutes, 'pending' => $data->loginThrottleIntervalMinutes, 'masked' => null, 'status' => $status],
            'framework_admin_api_throttle_limit' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->adminApiThrottleLimit, 'pending' => $data->adminApiThrottleLimit, 'masked' => null, 'status' => $status],
            'framework_admin_api_throttle_amount' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->adminApiThrottleAmount, 'pending' => $data->adminApiThrottleAmount, 'masked' => null, 'status' => $status],
            'framework_admin_api_throttle_interval_minutes' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->adminApiThrottleIntervalMinutes, 'pending' => $data->adminApiThrottleIntervalMinutes, 'masked' => null, 'status' => $status],
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function arrayValue(array $data, string $key): array
    {
        return $this->stringKeyedArray($data[$key] ?? null);
    }

    /** @return array<string, mixed> */
    private function stringKeyedArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $normalized[$key] = $item;
            }
        }

        return $normalized;
    }

    /** @param array<string, mixed> $context */
    private function actor(array $context): string
    {
        return $this->scalarString($context['actor'] ?? null, 'system');
    }

    private function scalarString(mixed $value, string $fallback): string
    {
        return is_scalar($value) ? (string) $value : $fallback;
    }

    private function boolFlag(bool $value): string
    {
        return $value ? '1' : '0';
    }

    /**
     * Narrows the shared Administering result to the public config-tool contract.
     *
     * @param array{status:string, messages:list<string>, masked_changes:array<string, mixed>, file_changes:list<array<string, mixed>>, secret_changes:list<array<string, mixed>>} $result
     *
     * @return array{status:string, messages:list<string>, masked_changes:array<string, string>, file_changes:array<int, array<string, mixed>>, secret_changes:array<int, array<string, mixed>>}
     */
    private function normalizeResult(array $result): array
    {
        $maskedChanges = [];
        foreach ($result['masked_changes'] as $key => $value) {
            if (is_string($value)) {
                $maskedChanges[$key] = $value;
            }
        }

        return [
            'status' => $result['status'],
            'messages' => $result['messages'],
            'masked_changes' => $maskedChanges,
            'file_changes' => $result['file_changes'],
            'secret_changes' => $result['secret_changes'],
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
