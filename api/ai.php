<?php

require_once __DIR__.'/config.local.php';
function call_ai(string $prompt, string $context = '', int $timeoutMs = 12000): array {
    // ---- cấu hình ----
    
    $API_URL = 'https://openrouter.ai/api/v1/chat/completions';
    $MODEL   = 'openai/gpt-4o-mini'; 
    $API_KEY = getenv('OPENROUTER_API_KEY')?: (defined('AI_KEY') ? AI_KEY : '');

    if (!$API_KEY) {
        return ['ok'=>false, 'answer'=>'(AI) Thiếu OPENROUTER_API_KEY.'];
    }

    // ---- payload ----
    $messages = [
        ['role'=>'system',
         'content'=>'Bạn là trợ lý cho website giải bóng. Trả lời ngắn gọn, lịch sự, tiếng Việt. '
                   .'Nếu thiếu dữ liệu từ DB, hãy nói rõ "Mình chưa có dữ liệu".' ],
        ['role'=>'user',  'content'=>"Ngữ cảnh:\n".$context],
        ['role'=>'user',  'content'=>"Câu hỏi:\n".$prompt],
    ];
    $payload = [
        'model'       => $MODEL,
        'messages'    => $messages,
        'temperature' => 0.2,
    ];

    // ---- cURL ----
    $ch = curl_init($API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST            => true,
        CURLOPT_HTTPHEADER      => [
            'Content-Type: application/json',
            'Authorization: Bearer '.$API_KEY,
            //
            'HTTP-Referer: https://your-domain-or-localhost',
            'X-Title: Tournapro Bot',
        ],
        CURLOPT_POSTFIELDS      => json_encode($payload),
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_TIMEOUT_MS      => $timeoutMs,
    ]);
    // 
    if (!defined('CURLOPT_TIMEOUT_MS')) {
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)ceil($timeoutMs/1000));
    }

    $raw  = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['ok'=>false, 'answer'=>"(AI) Lỗi kết nối: $err"];
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200) {
        return ['ok'=>false, 'answer'=>"(AI) HTTP $code – có thể quá tải hoặc key sai."];
    }

    $j = json_decode($raw, true);
    $txt = $j['choices'][0]['message']['content'] ?? '';
    if ($txt === '') {
        return ['ok'=>false, 'answer'=>'(AI) Không có nội dung trả về.'];
    }
    return ['ok'=>true, 'answer'=>$txt];
}
