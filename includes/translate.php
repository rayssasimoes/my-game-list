<?php
/**
 * Função simples de tradução usando MyMemory API (gratuita e sem necessidade de chave)
 */
function translateText($text, $targetLang = 'pt-BR', $sourceLang = 'en') {
    if (empty($text)) {
        return $text;
    }
    
    // Criar chave de cache baseada no texto
    $cacheKey = 'translate_' . md5($text . $targetLang);
    
    // Verificar cache de sessão
    if (isset($_SESSION[$cacheKey])) {
        return $_SESSION[$cacheKey];
    }
    
    try {
        // API MyMemory - gratuita, sem necessidade de chave
        $url = 'https://api.mymemory.translated.net/get';
        $params = [
            'q' => $text,
            'langpair' => $sourceLang . '|' . $targetLang
        ];
        
        $ch = curl_init($url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            
            if (isset($data['responseData']['translatedText'])) {
                $translatedText = $data['responseData']['translatedText'];
                
                // Armazenar em cache
                $_SESSION[$cacheKey] = $translatedText;
                
                return $translatedText;
            }
        }
    } catch (Exception $e) {
        error_log("Erro na tradução: " . $e->getMessage());
    }
    
    // Se falhar, retornar texto original
    return $text;
}
