<?php
namespace TouchGrass\Services;

final class OllamaService {
    public function __construct(private string $baseUrl, private string $model, private int $timeout=120) {}
    public function health(): array {
        $result = $this->request('/api/tags', null, 8);
        return ['ok' => $result['ok'], 'models' => $result['data']['models'] ?? []];
    }
    public function chat(string $system, string $prompt): array {
        $payload = ['model'=>$this->model,'stream'=>false,'keep_alive'=>'10m','options'=>['temperature'=>0.35,'num_ctx'=>4096,'num_predict'=>256], 'messages'=>[
            ['role'=>'system','content'=>$system], ['role'=>'user','content'=>$prompt]
        ]];
        $result = $this->request('/api/chat', $payload, $this->timeout);
        if (!$result['ok']) return ['ok'=>false,'error'=>$result['error'] ?? 'AI unavailable'];
        $text = $result['data']['message']['content'] ?? '';
        if (!is_string($text) || trim($text) === '') {
            return ['ok'=>false,'error'=>'The local model returned an empty response. Please try again.'];
        }
        return ['ok'=>true,'text'=>$text, 'model'=>$this->model];
    }
    private function request(string $path, ?array $payload, int $timeout): array {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>$timeout, CURLOPT_CONNECTTIMEOUT=>5, CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);
        if ($payload !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload)); }
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($body === false || $status >= 400 || $status === 0) {
            if ($error !== '') {
                $message = str_contains($error, 'timed out')
                    ? "Local AI generation exceeded {$timeout} seconds. Try again with shorter notes or a smaller Ollama model."
                    : 'Local AI request failed: ' . $error;
                return ['ok'=>false,'error'=>$message];
            }
            return ['ok'=>false,'error'=>'Local AI returned HTTP ' . $status . '. Check the Ollama service and try again.'];
        }
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return ['ok'=>false,'error'=>'Local AI returned an invalid response. Please try again.'];
        }
        return ['ok'=>true,'data'=>$data];
    }
}
