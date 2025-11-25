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
    
    // Garantir sessão ativa (para usar cache)
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }

    // Verificar cache de sessão
    if (isset($_SESSION[$cacheKey])) {
        $cached = $_SESSION[$cacheKey];
        // Se o cache contém uma mensagem de erro da API, limpar e continuar para reprocessar
        if (is_string($cached) && preg_match('/QUERY\s+LENGTH\s+LIMIT|MAX\s+ALLOWED\s+QUERY|LENGTH\s+LIMIT\s+EXCEEDED/i', $cached)) {
            error_log('translateText: cache de tradução contém mensagem de erro; limpando cache e reprocessando');
            unset($_SESSION[$cacheKey]);
        } elseif (is_string($cached) && $cached === $text && mb_strlen($text, 'UTF-8') > 60) {
            // Se o cache simplesmente contém o mesmo texto original (provavelmente fallback de tentativa anterior),
            // limpar e reprocessar para tentar uma nova tradução.
            error_log('translateText: cache contém texto idêntico ao original; limpando cache para retraduzir');
            unset($_SESSION[$cacheKey]);
        } else {
            return $cached;
        }
    }
    
    try {
        // API MyMemory - gratuita, sem necessidade de chave
        $url = 'https://api.mymemory.translated.net/get';

        // MyMemory costuma limitar o tamanho da query a ~500 caracteres.
        // Se o texto for maior, dividimos em pedaços seguros e traduzimos sequencialmente.
        $maxChunk = 480; // margem abaixo de 500

        // Usar funções multibyte
        $textLen = mb_strlen($text, 'UTF-8');

        $chunks = [];

        if ($textLen <= $maxChunk) {
            $chunks[] = $text;
        } else {
            // Tentar dividir por sentenças para manter coerência
            $sentences = preg_split('/(?<=[\.\?!])\s+/u', $text);

            $current = '';
            foreach ($sentences as $sent) {
                if ($current === '') {
                    $current = $sent;
                } else {
                    // Se adicionar esta sentença extrapola, fechar chunk e começar novo
                    if (mb_strlen($current . ' ' . $sent, 'UTF-8') > $maxChunk) {
                        $chunks[] = $current;
                        $current = $sent;
                    } else {
                        $current .= ' ' . $sent;
                    }
                }
            }

            if ($current !== '') {
                // Ainda pode exceder por sentenças muito longas — quebrar por palavras
                if (mb_strlen($current, 'UTF-8') > $maxChunk) {
                    $words = preg_split('/\s+/u', $current);
                    $piece = '';
                    foreach ($words as $w) {
                        if ($piece === '') {
                            $piece = $w;
                        } else {
                            if (mb_strlen($piece . ' ' . $w, 'UTF-8') > $maxChunk) {
                                $chunks[] = $piece;
                                $piece = $w;
                            } else {
                                $piece .= ' ' . $w;
                            }
                        }
                    }
                    if ($piece !== '') $chunks[] = $piece;
                } else {
                    $chunks[] = $current;
                }
            }
        }

        $translated = '';

        // Preparar código de idioma para a API (usar apenas a parte principal, ex: 'pt' ao invés de 'pt-BR')
        $apiTarget = preg_match('/^[a-z]{2}/i', $targetLang) ? substr($targetLang, 0, 2) : $targetLang;
        $apiSource = preg_match('/^[a-z]{2}/i', $sourceLang) ? substr($sourceLang, 0, 2) : $sourceLang;

        foreach ($chunks as $c) {
            $attempts = 0;
            $maxAttempts = 2;
            $chunkTranslated = null;

            while ($attempts < $maxAttempts) {
                $attempts++;

                $params = [
                    'q' => $c,
                    'langpair' => $apiSource . '|' . $apiTarget
                ];

                $ch = curl_init($url . '?' . http_build_query($params));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 12);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);

                if ($curlErr) {
                    error_log("MyMemory cURL error (attempt {$attempts}): " . $curlErr);
                }

                if ($httpCode === 200 && $response) {
                    $data = json_decode($response, true);
                    if (isset($data['responseData']['translatedText'])) {
                        $chunkTranslated = $data['responseData']['translatedText'];
                        // Detectar mensagens de erro embutidas na resposta (ex.: QUERY LENGTH LIMIT EXCEEDED)
                        if (preg_match('/QUERY\s+LENGTH\s+LIMIT|MAX\s+ALLOWED\s+QUERY|LENGTH\s+LIMIT\s+EXCEEDED/i', $chunkTranslated)) {
                            error_log('MyMemory retornou mensagem de limite para um chunk; abortando tradução e retornando original');
                            return $text;
                        }
                        break; // sucesso neste chunk
                    } else {
                        error_log('MyMemory: resposta inesperada ao traduzir chunk (attempt ' . $attempts . ')');
                    }
                } else {
                    error_log('MyMemory HTTP ' . $httpCode . ' ao traduzir chunk (attempt ' . $attempts . ')');
                }

                // Pequena espera antes de tentar novamente (só se for tentar outra vez)
                if ($attempts < $maxAttempts) {
                    usleep(200000); // 200ms
                }
            }

            if ($chunkTranslated === null) {
                // Não conseguimos traduzir este pedaço — abortar e retornar o texto original
                error_log('MyMemory: falha ao traduzir um chunk após tentativas');
                return $text;
            }

            $translated .= $chunkTranslated;
        }

        // Armazenar em cache
        $_SESSION[$cacheKey] = $translated;
        return $translated;

    } catch (Exception $e) {
        error_log("Erro na tradução: " . $e->getMessage());
    }
    
    // Se falhar, retornar texto original
    return $text;
}
