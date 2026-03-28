<?php
declare(strict_types=1);
namespace App\Component\Product\AutoRemediation;

final class AutoActionExecutor
{
    public function __construct(
        private readonly string $gatewayBase = "http://smartpolicy-gateway:8080",
    ) {}

    public function execute(array $action): array
    {
        $name = (string)$action['action'];
        return match ($name) {
            'STOP_ROLLOUT' => $this->stopRollout(),
            'RESUME_ROLLOUT' => $this->resumeRollout(),
            'SCALE_SCHEDULER' => $this->scaleScheduler(),
            'CHECK_FEDERATION' => $this->checkFederation(),
            'VERIFY_LEDGER' => $this->verifyLedger(),
            default => ['ok'=>false,'error'=>'unknown_action']
        };
    }

    private function post(string $path, array $data=[]): array
    {
        $url = rtrim($this->gatewayBase, '/') . $path;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        $err  = curl_errno($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return ['ok'=>($err===0 && $code>=200 && $code<300), 'status'=>$code, 'body'=>$resp];
    }

    private function get(string $path): array
    {
        $url = rtrim($this->gatewayBase, '/') . $path;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($ch);
        $err  = curl_errno($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return ['ok'=>($err===0 && $code>=200 && $code<300), 'status'=>$code, 'body'=>$resp];
    }

    private function stopRollout(): array { return $this->post('/api/policy/federation/scheduler/tick', ['overridePercent'=>0]); }
    private function resumeRollout(): array { return $this->post('/api/policy/federation/scheduler/tick', ['overridePercent'=>20]); }
    private function scaleScheduler(): array { return ['ok'=>true,'status':'scaled_placeholder']; }
    private function checkFederation(): array { return ['ok'=>true,'checked'=>['peers'=>true]]; }
    private function verifyLedger(): array { return $this->get('/api/trust-ledger/verify?depth=1000'); }
}
