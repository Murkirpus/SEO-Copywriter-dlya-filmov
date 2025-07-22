<?php
// Конфигурация OpenRouter
$openrouter_api_key = 'sk-or-v1-'; // Замените на ваш API ключ OpenRouter
$app_name = 'SEO Копирайтер для товаров'; // Название вашего приложения
$site_url = 'https://yourdomain.com'; // URL вашего сайта

// ВАЖНО: Запуск сессии в самом начале
session_start();

// SEO-аналитика для товаров
function analyzeSEOMetrics($result) {
    if (!$result) return null;
    
    $title = $result['meta_title'] ?? '';
    $description = $result['meta_description'] ?? '';
    $keywords = $result['keywords'] ?? '';
    
    $titleLength = mb_strlen($title);
    $descLength = mb_strlen($description);
    $keywordsCount = count(array_filter(explode(',', $keywords)));
    
    return [
        'title' => [
            'length' => $titleLength,
            'status' => $titleLength <= 60 ? 'good' : ($titleLength <= 70 ? 'warning' : 'error'),
            'max' => 60
        ],
        'description' => [
            'length' => $descLength,
            'status' => ($descLength >= 150 && $descLength <= 160) ? 'good' : 
                       ($descLength >= 120 && $descLength <= 180) ? 'warning' : 'error',
            'min' => 150,
            'max' => 160
        ],
        'keywords' => [
            'count' => $keywordsCount,
            'status' => ($keywordsCount >= 5 && $keywordsCount <= 15) ? 'good' : 
                       ($keywordsCount >= 3 && $keywordsCount <= 20) ? 'warning' : 'error'
        ],
        'readability' => calculateReadability($result['description_section'] ?? '')
    ];
}

function calculateReadability($text) {
    $sentences = preg_split('/[.!?]+/', $text);
    $words = str_word_count($text);
    $avgWordsPerSentence = $words / max(count($sentences) - 1, 1);
    
    if ($avgWordsPerSentence <= 15) return ['score' => 'Отличная', 'status' => 'good'];
    if ($avgWordsPerSentence <= 20) return ['score' => 'Хорошая', 'status' => 'warning'];
    return ['score' => 'Сложная', 'status' => 'error'];
}

// Функции экспорта для товаров
function generateHTMLExport($result, $seoMetrics) {
    if (!$result) return '';
    
    $html = '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($result['meta_title']) . '</title>
    <meta name="description" content="' . htmlspecialchars($result['meta_description']) . '">
    <meta name="keywords" content="' . htmlspecialchars($result['keywords']) . '">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        h1 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        h2 { color: #667eea; margin-top: 30px; }
        .keywords { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .meta-info { background: #e3f2fd; padding: 15px; border-radius: 8px; border-left: 4px solid #2196f3; }
        .features { background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($result['h1_title']) . '</h1>
    
    <h2>Описание товара</h2>
    <p>' . nl2br(htmlspecialchars($result['description_section'])) . '</p>
    
    <h2>Характеристики и преимущества</h2>
    <div class="features">
        ' . nl2br(htmlspecialchars($result['features_section'])) . '
    </div>
    
    <h2>Отзывы и рекомендации</h2>
    <p>' . nl2br(htmlspecialchars($result['reviews_section'])) . '</p>
    
    <h2>Где купить</h2>
    <p>' . nl2br(htmlspecialchars($result['purchase_section'])) . '</p>
    
    <div class="keywords">
        <h3>Ключевые слова:</h3>
        <p>' . htmlspecialchars($result['keywords']) . '</p>
    </div>
    
    <div class="meta-info">
        <h3>SEO метаданные:</h3>
        <p><strong>Title:</strong> ' . htmlspecialchars($result['meta_title']) . '</p>
        <p><strong>Description:</strong> ' . htmlspecialchars($result['meta_description']) . '</p>
    </div>
</body>
</html>';
    
    return $html;
}

function generateTXTExport($result) {
    if (!$result) return '';
    
    $txt = "SEO-КОНТЕНТ ДЛЯ ТОВАРА\n";
    $txt .= "======================\n\n";
    $txt .= "H1 ЗАГОЛОВОК:\n" . ($result['h1_title'] ?? '') . "\n\n";
    $txt .= "ОПИСАНИЕ ТОВАРА:\n" . ($result['description_section'] ?? '') . "\n\n";
    $txt .= "ХАРАКТЕРИСТИКИ И ПРЕИМУЩЕСТВА:\n" . ($result['features_section'] ?? '') . "\n\n";
    $txt .= "ОТЗЫВЫ И РЕКОМЕНДАЦИИ:\n" . ($result['reviews_section'] ?? '') . "\n\n";
    $txt .= "ГДЕ КУПИТЬ:\n" . ($result['purchase_section'] ?? '') . "\n\n";
    $txt .= "КЛЮЧЕВЫЕ СЛОВА:\n" . ($result['keywords'] ?? '') . "\n\n";
    $txt .= "META TITLE:\n" . ($result['meta_title'] ?? '') . "\n\n";
    $txt .= "META DESCRIPTION:\n" . ($result['meta_description'] ?? '') . "\n\n";
    $txt .= "Создано: " . date('d.m.Y H:i:s') . "\n";
    
    return $txt;
}

// ФУНКЦИЯ: Добавление результата в накопительную базу
function addToResultsHistory($result, $seoMetrics) {
    if (!$result) return;
    
    if (!isset($_SESSION['results_history'])) {
        $_SESSION['results_history'] = [];
    }
    
    $_SESSION['results_history'][] = [
        'result' => $result,
        'seo_metrics' => $seoMetrics,
        'timestamp' => time(),
        'date' => date('d.m.Y H:i')
    ];
}

// ФУНКЦИЯ: Генерация Excel с накопленными результатами
function generateExcelExport($allResults = null) {
    if ($allResults === null) {
        $allResults = $_SESSION['results_history'] ?? [];
    }
    
    if (empty($allResults)) {
        return '';
    }
    
    $excel = '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Title>SEO Контент - База товаров</Title>
  <Author>SEO Копирайтер</Author>
  <Created>' . date('Y-m-d\TH:i:s\Z') . '</Created>
 </DocumentProperties>
 <Styles>
  <Style ss:ID="Header">
   <Font ss:Bold="1" ss:Size="12" ss:Color="#ffffff"/>
   <Interior ss:Color="#667eea" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="Content">
   <Font ss:Size="10"/>
   <Alignment ss:Vertical="Top" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="SEOGood">
   <Interior ss:Color="#d4edda" ss:Pattern="Solid"/>
   <Font ss:Color="#155724" ss:Bold="1" ss:Size="10"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="SEOWarning">
   <Interior ss:Color="#fff3cd" ss:Pattern="Solid"/>
   <Font ss:Color="#856404" ss:Bold="1" ss:Size="10"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="SEOError">
   <Interior ss:Color="#f8d7da" ss:Pattern="Solid"/>
   <Font ss:Color="#721c24" ss:Bold="1" ss:Size="10"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
 </Styles>
 <Worksheet ss:Name="SEO База товаров">
  <Table>
   <!-- Настройка ширины колонок -->
   <Column ss:Width="40"/>
   <Column ss:Width="200"/>
   <Column ss:Width="300"/>
   <Column ss:Width="250"/>
   <Column ss:Width="250"/>
   <Column ss:Width="200"/>
   <Column ss:Width="250"/>
   <Column ss:Width="180"/>
   <Column ss:Width="180"/>
   <Column ss:Width="80"/>
   <Column ss:Width="80"/>
   <Column ss:Width="80"/>
   <Column ss:Width="80"/>
   <Column ss:Width="120"/>
   
   <!-- ЗАГОЛОВКИ -->
   <Row ss:Height="40">
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">№</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">H1 Заголовок</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Описание товара</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Характеристики</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Отзывы</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Где купить</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Ключевые слова</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Meta Title</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Meta Description</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Title длина</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Desc длина</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Ключевики</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Читаемость</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Дата создания</Data>
    </Cell>
   </Row>';
   
   $rowNumber = 1;
   foreach ($allResults as $resultItem) {
       $result = $resultItem['result'];
       $seoMetrics = $resultItem['seo_metrics'];
       $date = $resultItem['date'];
       
       $excel .= '
   <Row ss:Height="100">
    <Cell ss:StyleID="Content">
     <Data ss:Type="Number">' . $rowNumber . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['h1_title'] ?? '') . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['description_section'] ?? '') . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['features_section'] ?? '') . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['reviews_section'] ?? '') . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['purchase_section'] ?? '') . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['keywords'] ?? '') . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['meta_title'] ?? '') . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['meta_description'] ?? '') . '</Data>
    </Cell>';
    
        if ($seoMetrics) {
            $excel .= '
    <Cell ss:StyleID="SEO' . ucfirst($seoMetrics['title']['status']) . '">
     <Data ss:Type="String">' . $seoMetrics['title']['length'] . '/' . $seoMetrics['title']['max'] . '</Data>
    </Cell>
    <Cell ss:StyleID="SEO' . ucfirst($seoMetrics['description']['status']) . '">
     <Data ss:Type="String">' . $seoMetrics['description']['length'] . '/' . $seoMetrics['description']['max'] . '</Data>
    </Cell>
    <Cell ss:StyleID="SEO' . ucfirst($seoMetrics['keywords']['status']) . '">
     <Data ss:Type="String">' . $seoMetrics['keywords']['count'] . '</Data>
    </Cell>
    <Cell ss:StyleID="SEO' . ucfirst($seoMetrics['readability']['status']) . '">
     <Data ss:Type="String">' . $seoMetrics['readability']['score'] . '</Data>
    </Cell>';
        } else {
            $excel .= '
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">-</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">-</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">-</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">-</Data>
    </Cell>';
        }
        
        $excel .= '
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . $date . '</Data>
    </Cell>
   </Row>';
   
       $rowNumber++;
   }
   
   $excel .= '
  </Table>
 </Worksheet>
</Workbook>';
    
    return $excel;
}

// ФУНКЦИЯ: Очистка истории результатов
function clearResultsHistory() {
    $_SESSION['results_history'] = [];
}

// Обработка экспорта
if (isset($_GET['export'])) {
    
    if ($_GET['export'] === 'excel') {
        $excel = generateExcelExport();
        if (!empty($excel)) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="seo-products-database-' . date('Y-m-d-H-i') . '.xls"');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo $excel;
            exit;
        } else {
            $error = 'В базе данных нет результатов для экспорта. Создайте хотя бы одно SEO-описание товара.';
        }
    }
    
    if (isset($_SESSION['last_result'])) {
        $result = $_SESSION['last_result'];
        $seoMetrics = analyzeSEOMetrics($result);
        
        if ($_GET['export'] === 'html') {
            if (ob_get_level()) {
                ob_end_clean();
            }
            $html = generateHTMLExport($result, $seoMetrics);
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="seo-product-' . date('Y-m-d-H-i') . '.html"');
            echo $html;
            exit;
        }
        
        if ($_GET['export'] === 'txt') {
            if (ob_get_level()) {
                ob_end_clean();
            }
            $txt = generateTXTExport($result);
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="seo-product-' . date('Y-m-d-H-i') . '.txt"');
            echo $txt;
            exit;
        }
    }
}

// Обработка очистки истории
if (isset($_GET['action']) && $_GET['action'] === 'clear_history') {
    clearResultsHistory();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Доступные модели OpenRouter (все модели из оригинального файла)
function getOpenRouterModels() {
    return [
        // 🆓 БЕСПЛАТНЫЕ МОДЕЛИ
        'qwen/qwen-2.5-72b-instruct:free' => [
            'name' => '🆓 Qwen 2.5 72B Instruct',
            'description' => 'Мощная бесплатная модель от Alibaba',
            'price' => 'БЕСПЛАТНО',
            'cost_1000' => '$0.00',
            'speed' => '⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'free'
        ],
        
        'meta-llama/llama-3.3-70b-instruct:free' => [
            'name' => '🆓 Llama 3.3 70B Instruct',
            'description' => 'Отличная бесплатная модель от Meta',
            'price' => 'БЕСПЛАТНО',
            'cost_1000' => '$0.00',
            'speed' => '⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'free'
        ],
        
        'deepseek/deepseek-r1:free' => [
            'name' => '🆓 DeepSeek R1',
            'description' => 'Новейшая бесплатная модель с рассуждениями',
            'price' => 'БЕСПЛАТНО',
            'cost_1000' => '$0.00',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'free'
        ],

        'mistralai/mistral-nemo:free' => [
            'name' => '🆓 Mistral Nemo',
            'description' => 'Быстрая и качественная бесплатная модель',
            'price' => 'БЕСПЛАТНО',
            'cost_1000' => '$0.00',
            'speed' => '⚡⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => false,
            'category' => 'free'
        ],

        // 💰 БЮДЖЕТНЫЕ МОДЕЛИ
        'deepseek/deepseek-chat' => [
            'name' => '💰 DeepSeek Chat',
            'description' => 'Отличное качество по низкой цене',
            'price' => '$0.14 / $0.28 за 1М токенов',
            'cost_1000' => '$0.42',
            'speed' => '⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'budget'
        ],
        
        'openai/gpt-4.1-nano' => [
            'name' => '💰 GPT-4.1 Nano',
            'description' => 'Новейшая быстрая и дешевая модель OpenAI',
            'price' => '$0.10 / $0.40 за 1М токенов',
            'cost_1000' => '$0.50',
            'speed' => '⚡⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'budget'
        ],
        
        'google/gemini-2.5-flash' => [
            'name' => '💰 Gemini 2.5 Flash',
            'description' => 'СУПЕР ПОПУЛЯРНАЯ! Топ модель по цене/качеству',
            'price' => '$0.075 / $0.30 за 1М токенов',
            'cost_1000' => '$0.375',
            'speed' => '⚡⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'budget'
        ],
        
        'qwen/qwen-2.5-72b-instruct' => [
            'name' => '💰 Qwen 2.5 72B Instruct',
            'description' => 'Мощная модель по доступной цене',
            'price' => '$0.40 / $1.20 за 1М токенов',
            'cost_1000' => '$1.60',
            'speed' => '⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'budget'
        ],
        
        'meta-llama/llama-3.3-70b-instruct' => [
            'name' => '💰 Llama 3.3 70B Instruct',
            'description' => 'Отличная модель от Meta, хорошая цена',
            'price' => '$0.59 / $0.79 за 1М токенов',
            'cost_1000' => '$1.38',
            'speed' => '⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => false,
            'category' => 'budget'
        ],

        // 🥇 ПРЕМИУМ МОДЕЛИ
        'google/gemini-2.5-pro' => [
            'name' => '🥇 Gemini 2.5 Pro',
            'description' => 'Топовая модель Google с отличными возможностями',
            'price' => '$1.25 / $5.00 за 1М токенов',
            'cost_1000' => '$6.25',
            'speed' => '⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'premium'
        ],
        
        'openai/gpt-4o' => [
            'name' => '🥇 GPT-4o',
            'description' => 'Мультимодальная модель от OpenAI',
            'price' => '$2.50 / $10.00 за 1М токенов',
            'cost_1000' => '$12.50',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => false,
            'category' => 'premium'
        ],
        
        'openai/gpt-4o-mini' => [
            'name' => '🥇 GPT-4o Mini',
            'description' => 'Быстрая и качественная мини-версия',
            'price' => '$0.15 / $0.60 за 1М токенов',
            'cost_1000' => '$0.75',
            'speed' => '⚡⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'premium'
        ],
        
        'anthropic/claude-3.5-sonnet' => [
            'name' => '🥇 Claude 3.5 Sonnet',
            'description' => 'Топовая модель от Anthropic для текста и кода',
            'price' => '$3.00 / $15.00 за 1М токенов',
            'cost_1000' => '$18.00',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => false,
            'category' => 'premium'
        ],
        
        'anthropic/claude-3-haiku' => [
            'name' => '🥇 Claude 3 Haiku',
            'description' => 'Быстрая и экономичная версия Claude',
            'price' => '$0.25 / $1.25 за 1М токенов',
            'cost_1000' => '$1.50',
            'speed' => '⚡⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'premium'
        ],

        // 🚀 НОВЕЙШИЕ И ПОПУЛЯРНЫЕ МОДЕЛИ
        'anthropic/claude-3.7-sonnet' => [
            'name' => '🚀 Claude 3.7 Sonnet',
            'description' => 'Новейшая модель Anthropic с улучшенными возможностями',
            'price' => '$3.00 / $15.00 за 1М токенов',
            'cost_1000' => '$18.00',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'newest'
        ],
        
        'anthropic/claude-sonnet-4' => [
            'name' => '🚀 Claude Sonnet 4',
            'description' => 'Революционная Claude 4 с мгновенными ответами',
            'price' => '$5.00 / $25.00 за 1М токенов',
            'cost_1000' => '$30.00',
            'speed' => '⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'newest'
        ],
        
        'anthropic/claude-opus-4' => [
            'name' => '🚀 Claude Opus 4',
            'description' => 'Топовая модель Claude 4 с максимальными возможностями',
            'price' => '$15.00 / $75.00 за 1М токенов',
            'cost_1000' => '$90.00',
            'speed' => '⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => false,
            'category' => 'newest'
        ],
        
        'x-ai/grok-3' => [
            'name' => '🚀 Grok 3.0',
            'description' => 'Мощная модель xAI с думающим режимом',
            'price' => '$2.50 / $12.50 за 1М токенов',
            'cost_1000' => '$15.00',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'newest'
        ],
        
        'x-ai/grok-4' => [
            'name' => '🚀 Grok 4.0',
            'description' => 'Новейшая модель xAI с продвинутыми рассуждениями',
            'price' => '$4.00 / $20.00 за 1М токенов',
            'cost_1000' => '$24.00',
            'speed' => '⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'newest'
        ],
        
        'deepseek/deepseek-r1' => [
            'name' => '🚀 DeepSeek R1',
            'description' => 'Революционная модель с рассуждениями. Конкурент GPT-o1',
            'price' => '$0.55 / $2.19 за 1М токенов',
            'cost_1000' => '$2.74',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'newest'
        ],
        
        'mistralai/mistral-large-2407' => [
            'name' => '🚀 Mistral Large 2407',
            'description' => 'Флагманская модель Mistral с отличным качеством',
            'price' => '$3.00 / $9.00 за 1М токенов',
            'cost_1000' => '$12.00',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => true,
            'category' => 'newest'
        ],
        
        'x-ai/grok-2-1212' => [
            'name' => '🚀 Grok 2.0',
            'description' => 'Модель от xAI с юмором и актуальными данными',
            'price' => '$2.00 / $10.00 за 1М токенов',
            'cost_1000' => '$12.00',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => false,
            'category' => 'newest'
        ],
        
        'openai/o1-mini' => [
            'name' => '🚀 GPT-o1 Mini',
            'description' => 'Модель с усиленными рассуждениями от OpenAI',
            'price' => '$3.00 / $12.00 за 1М токенов',
            'cost_1000' => '$15.00',
            'speed' => '⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => false,
            'category' => 'newest'
        ],
        
        'cohere/command-r-plus' => [
            'name' => '🚀 Command R+',
            'description' => 'Мощная модель Cohere для RAG и сложных задач',
            'price' => '$3.00 / $15.00 за 1М токенов',
            'cost_1000' => '$18.00',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => false,
            'category' => 'newest'
        ]
    ];
}

// Шаблоны промптов для категорий товаров
function getCategoryTemplates() {
    return [
        'universal' => [
            'name' => '🛍️ Универсальный',
            'description' => 'Подходит для любых товаров и категорий',
            'prompt' => "Ты профессиональный SEO-копирайтер с 10+ лет опыта в e-commerce. Получишь на вход краткое описание товара. Твоя задача — создать полное SEO-оптимизированное описание.

**ВХОДНОЙ ТЕКСТ:**
{PRODUCT_DESCRIPTION}

**ТРЕБОВАНИЯ:**
✅ Привлекательный и информативный текст на 400–500 слов
✅ Включены ключевые слова: название товара, \"купить\", \"цена\", \"отзывы\", \"характеристики\", категория
✅ Убедительные преимущества и особенности
✅ Призыв к действию к покупке

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название товара - купить по лучшей цене с доставкой]\",
  \"description_section\": \"[2-3 абзаца подробного описания товара с акцентом на пользу для покупателя]\",
  \"features_section\": \"[Основные характеристики и преимущества в удобном формате]\",
  \"reviews_section\": \"[Краткий обзор того, что говорят покупатели, положительные стороны]\",
  \"purchase_section\": \"[Где купить, гарантии, доставка, призыв к действию]\",
  \"keywords\": \"[ключевые слова через запятую включая категорию, бренд, характеристики]\",
  \"meta_title\": \"[Title до 60 символов с названием товара и призывом]\",
  \"meta_description\": \"[Description 150-160 символов с описанием и ценностью]\"
}

Отвечай ТОЛЬКО валидным JSON без markdown разметки!"
        ],
        
        'electronics' => [
            'name' => '📱 Электроника и техника',
            'description' => 'Акцент на технические характеристики, функции, инновации',
            'prompt' => "Ты профессиональный SEO-копирайтер, специализирующийся на электронике и бытовой технике. Твоя задача — создать техническое SEO-описание, которое подчеркнет все преимущества и возможности устройства.

**ВХОДНОЙ ТЕКСТ:**
{PRODUCT_DESCRIPTION}

**СТИЛЬ ДЛЯ ЭЛЕКТРОНИКИ:**
🔧 Используй технические фразы: \"передовые технологии\", \"высокая производительность\", \"инновационные функции\"
⚡ Подчеркни характеристики, функции, возможности, совместимость
📊 Акцент на качество, надежность, гарантию, сертификацию
🎯 Ключевики: \"техника\", \"электроника\", \"характеристики\", \"функции\", \"технологии\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название - современная техника купить с гарантией]\",
  \"description_section\": \"[Подробное техническое описание с акцентом на функциональность и производительность]\",
  \"features_section\": \"[Детальные технические характеристики, функции, совместимость, стандарты]\",
  \"reviews_section\": \"[Мнения экспертов и пользователей о качестве, надежности, удобстве использования]\",
  \"purchase_section\": \"[Где купить технику с гарантией, сервисное обслуживание, доставка]\",
  \"keywords\": \"[электроника, техника, характеристики, функции, + бренд и специфические термины]\",
  \"meta_title\": \"[Название + техника/электроника + купить до 60 символов]\",
  \"meta_description\": \"[Техническое описание с ключевыми особенностями, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'fashion' => [
            'name' => '👗 Мода и одежда',
            'description' => 'Акцент на стиль, качество материалов, модные тренды',
            'prompt' => "Ты профессиональный SEO-копирайтер, эксперт по моде и стилю. Твоя задача — создать стильное SEO-описание, которое передает модность, качество и привлекательность одежды.

**ВХОДНОЙ ТЕКСТ:**
{PRODUCT_DESCRIPTION}

**СТИЛЬ ДЛЯ МОДЫ:**
✨ Используй модные фразы: \"стильный образ\", \"качественные материалы\", \"модные тренды\", \"элегантный дизайн\"
👔 Подчеркни стиль, материалы, крой, размеры, сочетаемость
🎨 Акцент на внешний вид, комфорт, универсальность, качество
🎯 Ключевики: \"одежда\", \"мода\", \"стиль\", \"качество\", \"материал\", \"размер\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название - стильная одежда купить онлайн]\",
  \"description_section\": \"[Стильное описание с акцентом на внешний вид, материалы, универсальность использования]\",
  \"features_section\": \"[Материалы, размеры, цвета, особенности кроя, уход за изделием]\",
  \"reviews_section\": \"[Отзывы о качестве, посадке, комфорте, соответствии размеру]\",
  \"purchase_section\": \"[Где купить модную одежду, размерная сетка, примерка, возврат]\",
  \"keywords\": \"[одежда, мода, стиль, качество, материал, + размеры и цвета]\",
  \"meta_title\": \"[Название + одежда/мода + купить до 60 символов]\",
  \"meta_description\": \"[Модное описание с акцентом на стиль и качество, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'beauty' => [
            'name' => '💄 Красота и здоровье',
            'description' => 'Акцент на эффект, натуральность, уход, результат',
            'prompt' => "Ты профессиональный SEO-копирайтер, специалист по косметике и товарам для здоровья. Твоя задача — создать привлекательное SEO-описание, которое подчеркнет эффективность и пользу продукта.

**ВХОДНОЙ ТЕКСТ:**
{PRODUCT_DESCRIPTION}

**СТИЛЬ ДЛЯ КРАСОТЫ:**
🌟 Используй beauty-фразы: \"естественная красота\", \"эффективный уход\", \"проверенная формула\", \"видимый результат\"
💆 Подчеркни эффект, состав, применение, результаты, безопасность
✨ Акцент на пользу, натуральность, качество, подходящий тип кожи
🎯 Ключевики: \"косметика\", \"уход\", \"красота\", \"эффект\", \"натуральный\", \"результат\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название - эффективная косметика для красоты купить]\",
  \"description_section\": \"[Подробное описание эффекта, состава, способа применения и ожидаемых результатов]\",
  \"features_section\": \"[Активные компоненты, тип кожи, способ применения, объем, срок годности]\",
  \"reviews_section\": \"[Отзывы о результатах, эффективности, удобстве использования, качестве]\",
  \"purchase_section\": \"[Где купить качественную косметику, оригинальность, сертификаты, доставка]\",
  \"keywords\": \"[косметика, красота, уход, эффект, натуральный, + тип продукта и кожи]\",
  \"meta_title\": \"[Название + косметика/красота + купить до 60 символов]\",
  \"meta_description\": \"[Описание эффекта и пользы для красоты, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'home' => [
            'name' => '🏠 Дом и быт',
            'description' => 'Акцент на функциональность, комфорт, практичность',
            'prompt' => "Ты профессиональный SEO-копирайтер, специалист по товарам для дома. Твоя задача — создать практичное SEO-описание, которое подчеркнет удобство, функциональность и пользу для домашнего использования.

**ВХОДНОЙ ТЕКСТ:**
{PRODUCT_DESCRIPTION}

**СТИЛЬ ДЛЯ ДОМА:**
🏡 Используй домашние фразы: \"комфорт дома\", \"практичное решение\", \"удобство использования\", \"качество жизни\"
🔧 Подчеркни функциональность, практичность, долговечность, удобство
🌟 Акцент на пользу для дома, экономию времени, улучшение быта
🎯 Ключевики: \"дом\", \"быт\", \"удобство\", \"практичный\", \"качество\", \"функциональный\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название - практичные товары для дома купить]\",
  \"description_section\": \"[Описание функциональности, удобства использования, пользы для домашнего хозяйства]\",
  \"features_section\": \"[Размеры, материалы, способы использования, уход, совместимость, долговечность]\",
  \"reviews_section\": \"[Отзывы о практичности, качестве, удобстве, долговечности использования]\",
  \"purchase_section\": \"[Где купить качественные товары для дома, гарантия, доставка на дом]\",
  \"keywords\": \"[дом, быт, товары для дома, удобство, практичный, + конкретная категория]\",
  \"meta_title\": \"[Название + для дома/быт + купить до 60 символов]\",
  \"meta_description\": \"[Практическое описание пользы для дома, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'sport' => [
            'name' => '⚽ Спорт и активность',
            'description' => 'Акцент на производительность, качество, результат',
            'prompt' => "Ты профессиональный SEO-копирайтер, специалист по спортивным товарам. Твоя задача — создать мотивирующее SEO-описание, которое подчеркнет пользу для спорта, качество и результативность.

**ВХОДНОЙ ТЕКСТ:**
{PRODUCT_DESCRIPTION}

**СТИЛЬ ДЛЯ СПОРТА:**
🏃 Используй спортивные фразы: \"повышение результатов\", \"профессиональное качество\", \"активный образ жизни\", \"достижение целей\"
💪 Подчеркни производительность, комфорт, долговечность, профессиональность
🎯 Акцент на результаты, мотивацию, здоровье, активность
🏆 Ключевики: \"спорт\", \"фитнес\", \"тренировки\", \"активность\", \"качество\", \"результат\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название - качественные спортивные товары купить]\",
  \"description_section\": \"[Описание пользы для спорта, повышения результатов, комфорта во время тренировок]\",
  \"features_section\": \"[Технические характеристики, материалы, размеры, особенности для спорта]\",
  \"reviews_section\": \"[Отзывы спортсменов о качестве, удобстве, влиянии на результаты]\",
  \"purchase_section\": \"[Где купить качественные спорттовары, гарантия, доставка для спортсменов]\",
  \"keywords\": \"[спорт, фитнес, тренировки, спорттовары, качество, + вид спорта]\",
  \"meta_title\": \"[Название + спорт/фитнес + купить до 60 символов]\",
  \"meta_description\": \"[Спортивное описание с акцентом на результаты, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'children' => [
            'name' => '🧸 Детские товары',
            'description' => 'Акцент на безопасность, развитие, качество для детей',
            'prompt' => "Ты профессиональный SEO-копирайтер, специалист по детским товарам. Твоя задача — создать заботливое SEO-описание, которое подчеркнет безопасность, пользу для развития и качество детского товара.

**ВХОДНОЙ ТЕКСТ:**
{PRODUCT_DESCRIPTION}

**СТИЛЬ ДЛЯ ДЕТСКИХ ТОВАРОВ:**
👶 Используй детские фразы: \"безопасность ребенка\", \"развивающий эффект\", \"качественные материалы\", \"радость детей\"
🛡️ Подчеркни безопасность, экологичность, развивающий потенциал, качество
🌈 Акцент на пользу для развития, безопасность, удовольствие детей
🎯 Ключевики: \"детские товары\", \"безопасность\", \"развитие\", \"качество\", \"дети\", \"малыши\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название - безопасные детские товары купить]\",
  \"description_section\": \"[Описание пользы для детей, безопасности, развивающего эффекта, радости использования]\",
  \"features_section\": \"[Возрастные ограничения, материалы, размеры, безопасность, сертификаты]\",
  \"reviews_section\": \"[Отзывы родителей о качестве, безопасности, реакции детей, развивающем эффекте]\",
  \"purchase_section\": \"[Где купить качественные детские товары, сертификаты безопасности, доставка]\",
  \"keywords\": \"[детские товары, дети, безопасность, развитие, качество, + возрастная группа]\",
  \"meta_title\": \"[Название + детские товары + купить до 60 символов]\",
  \"meta_description\": \"[Описание пользы и безопасности для детей, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'auto' => [
            'name' => '🚗 Автотовары',
            'description' => 'Акцент на надежность, качество, совместимость с авто',
            'prompt' => "Ты профессиональный SEO-копирайтер, специалист по автомобильным товарам. Твоя задача — создать надежное SEO-описание, которое подчеркнет качество, совместимость и пользу для автомобиля.

**ВХОДНОЙ ТЕКСТ:**
{PRODUCT_DESCRIPTION}

**СТИЛЬ ДЛЯ АВТОТОВАРОВ:**
🚗 Используй авто-фразы: \"надежность в пути\", \"качественные автозапчасти\", \"совместимость с авто\", \"безопасность вождения\"
🔧 Подчеркни технические характеристики, совместимость, надежность, качество
🛣️ Акцент на безопасность, долговечность, улучшение характеристик авто
🎯 Ключевики: \"автотовары\", \"запчасти\", \"авто\", \"качество\", \"надежность\", \"совместимость\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название - качественные автотовары купить с доставкой]\",
  \"description_section\": \"[Описание назначения, совместимости с автомобилями, влияния на характеристики авто]\",
  \"features_section\": \"[Технические характеристики, совместимые марки авто, установка, гарантия]\",
  \"reviews_section\": \"[Отзывы автовладельцев о качестве, надежности, влиянии на работу авто]\",
  \"purchase_section\": \"[Где купить автотовары, проверка совместимости, гарантия, доставка]\",
  \"keywords\": \"[автотовары, запчасти, авто, автомобиль, качество, + марки авто и тип товара]\",
  \"meta_title\": \"[Название + автотовары/запчасти + купить до 60 символов]\",
  \"meta_description\": \"[Техническое описание для автомобилей, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ]
    ];
}

// Обработка POST запроса
$result = null;
$error = '';

if ($_POST && isset($_POST['product_description']) && !empty(trim($_POST['product_description']))) {
    $product_description = trim($_POST['product_description']);
    $selected_category = $_POST['category'] ?? 'universal';
    $selected_model = $_POST['model'] ?? 'qwen/qwen-2.5-72b-instruct:free';
    
    $templates = getCategoryTemplates();
    $template = $templates[$selected_category];
    
    // Подставляем описание товара в шаблон
    $prompt = str_replace('{PRODUCT_DESCRIPTION}', $product_description, $template['prompt']);
    
    // Запрос к OpenRouter API с выбранной моделью
    $data = [
        'model' => $selected_model,
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 2000,
        'temperature' => 0.7
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://openrouter.ai/api/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openrouter_api_key,
        'HTTP-Referer: ' . $site_url,
        'X-Title: ' . $app_name
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $response_data = json_decode($response, true);
        if (isset($response_data['choices'][0]['message']['content'])) {
            $ai_response = $response_data['choices'][0]['message']['content'];
            
            // Очистка от возможной markdown разметки
            $ai_response = preg_replace('/```json\s*|\s*```/', '', $ai_response);
            $ai_response = trim($ai_response);
            
            // Парсинг JSON ответа
            $result = json_decode($ai_response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = 'Ошибка парсинга JSON ответа: ' . json_last_error_msg();
            } else {
                // Сохраняем результат в сессию для экспорта
                $_SESSION['last_result'] = $result;
                
                // ДОБАВЛЯЕМ В НАКОПИТЕЛЬНУЮ БАЗУ
                $seoMetrics = analyzeSEOMetrics($result);
                addToResultsHistory($result, $seoMetrics);
            }
        } else {
            $error = 'Ошибка в ответе API: ' . (isset($response_data['error']['message']) ? $response_data['error']['message'] : 'Неизвестная ошибка');
        }
    } else {
        $response_data = json_decode($response, true);
        $error = 'Ошибка запроса (' . $http_code . '): ' . (isset($response_data['error']['message']) ? $response_data['error']['message'] : 'Неизвестная ошибка');
    }
}

$templates = getCategoryTemplates();
$models = getOpenRouterModels();
$seoMetrics = $result ? analyzeSEOMetrics($result) : null;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Копирайтер для товаров | OpenRouter AI с 24 лучшими моделями</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .openrouter-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 25px;
            margin-top: 15px;
            display: inline-block;
            font-size: 0.9rem;
        }

        .main-content {
            padding: 40px;
        }

        .input-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            background: white;
            transition: border-color 0.3s ease;
        }

        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .category-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            border-left: 4px solid #667eea;
        }

        .model-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            border-left: 4px solid #28a745;
        }

        .model-info.free {
            background: #d1ecf1;
            border-left-color: #17a2b8;
        }

        .model-info.budget {
            background: #fff3cd;
            border-left-color: #ffc107;
        }

        .model-info.premium {
            background: #f8d7da;
            border-left-color: #dc3545;
        }

        .model-info.newest {
            background: #d4edda;
            border-left-color: #28a745;
        }

        .category-info h4, .model-info h4 {
            color: #667eea;
            margin-bottom: 5px;
        }

        .model-info.free h4 {
            color: #17a2b8;
        }

        .model-info.budget h4 {
            color: #856404;
        }

        .model-info.premium h4 {
            color: #721c24;
        }

        .model-info.newest h4 {
            color: #155724;
        }

        .category-info p, .model-info p {
            color: #666;
            font-size: 14px;
        }

        .model-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .stat-item {
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            font-size: 12px;
        }

        .stat-label {
            font-weight: 600;
            color: #555;
            display: block;
        }

        .stat-value {
            color: #667eea;
            font-weight: bold;
        }

        .model-category {
            background: #17a2b8;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 10px;
        }

        .model-category.budget {
            background: #ffc107;
            color: #212529;
        }

        .model-category.premium {
            background: #dc3545;
        }

        .model-category.newest {
            background: #28a745;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .output-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
        }

        /* SEO АНАЛИТИКА */
        .seo-analytics {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #667eea;
        }

        .seo-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .metric-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .metric-item.good {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .metric-item.warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
        }

        .metric-item.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .metric-value {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .metric-value.good { color: #155724; }
        .metric-value.warning { color: #856404; }
        .metric-value.error { color: #721c24; }

        .metric-label {
            font-size: 0.9rem;
            color: #666;
        }

        /* КНОПКИ ЭКСПОРТА */
        .export-buttons {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #28a745;
        }

        .export-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .export-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(40, 167, 69, 0.3);
        }

        .export-btn.copy {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }

        .export-btn.copy:hover {
            box-shadow: 0 8px 16px rgba(0, 123, 255, 0.3);
        }

        .export-btn.excel {
            background: linear-gradient(135deg, #217346 0%, #0F5132 100%);
        }

        .export-btn.excel:hover {
            box-shadow: 0 8px 16px rgba(33, 115, 70, 0.3);
        }

        .export-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
        }

        .result-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #667eea;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-content {
            color: #555;
            line-height: 1.6;
            word-wrap: break-word;
        }

        .card-content h1 {
            color: #667eea;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .keywords-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 14px;
        }

        .meta-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }

        .meta-item {
            margin-bottom: 10px;
        }

        .meta-item:last-child {
            margin-bottom: 0;
        }

        .meta-label {
            font-weight: 600;
            color: #1976d2;
        }

        .features-list {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }

        .error {
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .empty-state {
            text-align: center;
            color: #6c757d;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #667eea;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .copy-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .copy-btn:hover {
            background: #218838;
        }

        @media (max-width: 768px) {
            .results-grid, .seo-metrics, .export-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }

            .header p {
                font-size: 1rem;
            }

            .main-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-shopping-cart"></i> SEO Копирайтер для товаров</h1>
            <p>Создавайте идеальные SEO-описания товаров с помощью 24 лучших AI моделей</p>
            <div class="openrouter-badge">
                <i class="fas fa-rocket"></i> Работает на OpenRouter.ai • 24 лучших модели
            </div>
        </div>

        <div class="main-content">
            <div class="input-section">
                <h2 class="section-title">
                    <i class="fas fa-edit"></i>
                    Создание SEO-описания товара
                </h2>

                <form method="POST" id="seoForm">
                    <div class="form-group">
                        <label for="model">🤖 Выберите AI модель:</label>
                        <select name="model" id="model" onchange="updateModelInfo()">
                            <?php 
                            $categoryNames = [
                                'free' => '🆓 БЕСПЛАТНЫЕ МОДЕЛИ',
                                'budget' => '💰 БЮДЖЕТНЫЕ МОДЕЛИ',
                                'premium' => '🥇 ПРЕМИУМ МОДЕЛИ',
                                'newest' => '🚀 НОВЕЙШИЕ МОДЕЛИ'
                            ];
                            
                            $categorizedModels = [];
                            foreach ($models as $key => $model) {
                                $categorizedModels[$model['category']][$key] = $model;
                            }
                            
                            foreach ($categoryNames as $category => $categoryName) {
                                if (isset($categorizedModels[$category])) {
                                    echo '<optgroup label="' . $categoryName . '">';
                                    foreach ($categorizedModels[$category] as $key => $model) {
                                        $selected = ($_POST['model'] ?? 'qwen/qwen-2.5-72b-instruct:free') == $key ? 'selected' : '';
                                        echo '<option value="' . $key . '" ' . $selected . '>';
                                        echo $model['name'] . ' - ' . $model['cost_1000'] . ' за 1000 описаний';
                                        echo $model['recommended'] ? ' (РЕКОМЕНДУЕТСЯ)' : '';
                                        echo '</option>';
                                    }
                                    echo '</optgroup>';
                                }
                            }
                            ?>
                        </select>
                        <div class="model-info" id="modelInfo">
                            <!-- Информация о модели будет обновляться JavaScript -->
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category">🏷️ Выберите категорию товара:</label>
                        <select name="category" id="category" onchange="updateCategoryInfo()">
                            <?php foreach ($templates as $key => $template): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($_POST['category'] ?? 'universal') == $key ? 'selected' : ''; ?>>
                                    <?php echo $template['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="category-info" id="categoryInfo">
                            <!-- Информация о категории будет обновляться JavaScript -->
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product_description">Исходное описание товара:</label>
                        <textarea 
                            name="product_description" 
                            id="product_description" 
                            rows="8" 
                            placeholder="Вставьте краткое описание товара, которое нужно переработать для SEO...&#10;&#10;Например:&#10;«iPhone 15 Pro Max - флагманский смартфон Apple с чипом A17 Pro, титановым корпусом, камерой 48MP и дисплеем 6.7 дюймов. Поддержка 5G, беспроводная зарядка, Face ID.»"
                            required><?php echo htmlspecialchars($_POST['product_description'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn" id="submitBtn">
                        <i class="fas fa-magic"></i>
                        Создать SEO-описание
                    </button>
                </form>
            </div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Генерируем SEO-описание товара...</p>
            </div>

            <?php if ($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($result): ?>
                <div class="output-section">
                    <h2 class="section-title">
                        <i class="fas fa-star"></i>
                        SEO-результат (<?php echo $templates[$_POST['category']]['name']; ?> + <?php echo $models[$_POST['model']]['name']; ?>)
                    </h2>

                    <!-- SEO АНАЛИТИКА -->
                    <?php if ($seoMetrics): ?>
                    <div class="seo-analytics">
                        <div class="card-title">
                            <i class="fas fa-chart-line"></i>
                            SEO Аналитика
                        </div>
                        <div class="seo-metrics">
                            <div class="metric-item <?php echo $seoMetrics['title']['status']; ?>">
                                <div class="metric-value <?php echo $seoMetrics['title']['status']; ?>">
                                    <?php echo $seoMetrics['title']['length']; ?>/<?php echo $seoMetrics['title']['max']; ?>
                                </div>
                                <div class="metric-label">Title (символов)</div>
                            </div>
                            <div class="metric-item <?php echo $seoMetrics['description']['status']; ?>">
                                <div class="metric-value <?php echo $seoMetrics['description']['status']; ?>">
                                    <?php echo $seoMetrics['description']['length']; ?>/<?php echo $seoMetrics['description']['max']; ?>
                                </div>
                                <div class="metric-label">Description (символов)</div>
                            </div>
                            <div class="metric-item <?php echo $seoMetrics['keywords']['status']; ?>">
                                <div class="metric-value <?php echo $seoMetrics['keywords']['status']; ?>">
                                    <?php echo $seoMetrics['keywords']['count']; ?>
                                </div>
                                <div class="metric-label">Ключевых слов</div>
                            </div>
                            <div class="metric-item <?php echo $seoMetrics['readability']['status']; ?>">
                                <div class="metric-value <?php echo $seoMetrics['readability']['status']; ?>">
                                    <?php echo $seoMetrics['readability']['score']; ?>
                                </div>
                                <div class="metric-label">Читаемость</div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- КНОПКИ ЭКСПОРТА -->
                    <div class="export-buttons">
                        <div class="card-title">
                            <i class="fas fa-download"></i>
                            Экспорт результатов
                            <?php 
                            $historyCount = isset($_SESSION['results_history']) ? count($_SESSION['results_history']) : 0;
                            if ($historyCount > 0): 
                            ?>
                                <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; margin-left: 10px;">
                                    📊 В базе: <?php echo $historyCount; ?> товаров
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="export-grid">
                            <a href="?export=html" class="export-btn" target="_blank">
                                <i class="fas fa-file-code"></i>
                                Скачать HTML
                            </a>
                            <a href="?export=txt" class="export-btn" target="_blank">
                                <i class="fas fa-file-alt"></i>
                                Скачать TXT
                            </a>
                            <?php if ($historyCount > 0): ?>
                                <a href="?export=excel" class="export-btn excel" target="_blank">
                                    <i class="fas fa-file-excel"></i>
                                    <?php if ($historyCount > 1): ?>
                                        База Excel (<?php echo $historyCount; ?> товаров)
                                    <?php else: ?>
                                        Скачать Excel
                                    <?php endif; ?>
                                </a>
                            <?php else: ?>
                                <button class="export-btn excel" disabled title="Создайте хотя бы одно SEO-описание для экспорта">
                                    <i class="fas fa-file-excel"></i>
                                    Скачать Excel (нет данных)
                                </button>
                            <?php endif; ?>
                            <button class="export-btn copy" onclick="copyAllContent()">
                                <i class="fas fa-copy"></i>
                                Копировать всё
                            </button>
                            <?php if ($historyCount > 0): ?>
                                <a href="?action=clear_history" class="export-btn" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);" onclick="return confirm('Очистить всю накопленную базу из <?php echo $historyCount; ?> товаров?')">
                                    <i class="fas fa-trash-alt"></i>
                                    Очистить базу
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="results-grid">
                        <!-- H1 Заголовок -->
                        <div class="result-card">
                            <div class="card-title">
                                <i class="fas fa-heading"></i>
                                H1 Заголовок
                            </div>
                            <div class="card-content">
                                <h1><?php echo htmlspecialchars($result['h1_title'] ?? 'Не указано'); ?></h1>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($result['h1_title'] ?? ''); ?>')">
                                    <i class="fas fa-copy"></i> Копировать
                                </button>
                            </div>
                        </div>

                        <!-- Описание товара -->
                        <div class="result-card">
                            <div class="card-title">
                                <i class="fas fa-file-text"></i>
                                Описание товара
                            </div>
                            <div class="card-content">
                                <?php echo nl2br(htmlspecialchars($result['description_section'] ?? 'Не указано')); ?>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($result['description_section'] ?? ''); ?>')">
                                    <i class="fas fa-copy"></i> Копировать
                                </button>
                            </div>
                        </div>

                        <!-- Характеристики и преимущества -->
                        <div class="result-card">
                            <div class="card-title">
                                <i class="fas fa-list-ul"></i>
                                Характеристики и преимущества
                            </div>
                            <div class="card-content">
                                <div class="features-list">
                                    <?php echo nl2br(htmlspecialchars($result['features_section'] ?? 'Не указано')); ?>
                                </div>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($result['features_section'] ?? ''); ?>')">
                                    <i class="fas fa-copy"></i> Копировать
                                </button>
                            </div>
                        </div>

                        <!-- Отзывы и рекомендации -->
                        <div class="result-card">
                            <div class="card-title">
                                <i class="fas fa-comments"></i>
                                Отзывы и рекомендации
                            </div>
                            <div class="card-content">
                                <?php echo nl2br(htmlspecialchars($result['reviews_section'] ?? 'Не указано')); ?>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($result['reviews_section'] ?? ''); ?>')">
                                    <i class="fas fa-copy"></i> Копировать
                                </button>
                            </div>
                        </div>

                        <!-- Где купить -->
                        <div class="result-card">
                            <div class="card-title">
                                <i class="fas fa-shopping-bag"></i>
                                Где купить
                            </div>
                            <div class="card-content">
                                <?php echo nl2br(htmlspecialchars($result['purchase_section'] ?? 'Не указано')); ?>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($result['purchase_section'] ?? ''); ?>')">
                                    <i class="fas fa-copy"></i> Копировать
                                </button>
                            </div>
                        </div>

                        <!-- Ключевые слова -->
                        <div class="result-card">
                            <div class="card-title">
                                <i class="fas fa-tags"></i>
                                Ключевые слова
                            </div>
                            <div class="card-content">
                                <div class="keywords-list">
                                    <?php echo htmlspecialchars($result['keywords'] ?? 'Не указано'); ?>
                                </div>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($result['keywords'] ?? ''); ?>')">
                                    <i class="fas fa-copy"></i> Копировать
                                </button>
                            </div>
                        </div>

                        <!-- Метаданные -->
                        <div class="result-card">
                            <div class="card-title">
                                <i class="fas fa-code"></i>
                                Метаданные
                            </div>
                            <div class="card-content">
                                <div class="meta-info">
                                    <div class="meta-item">
                                        <span class="meta-label">Title:</span><br>
                                        <?php echo htmlspecialchars($result['meta_title'] ?? 'Не указано'); ?>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Description:</span><br>
                                        <?php echo htmlspecialchars($result['meta_description'] ?? 'Не указано'); ?>
                                    </div>
                                </div>
                                <button class="copy-btn" onclick="copyMetadata()">
                                    <i class="fas fa-copy"></i> Копировать метаданные
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif (!$error): ?>
                <div class="output-section">
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Здесь появится ваше SEO-описание товара</h3>
                        <p>Выберите AI модель, категорию товара, введите описание и нажмите "Создать SEO-описание"</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Данные о шаблонах и моделях для JavaScript
        const templates = <?php echo json_encode($templates); ?>;
        const models = <?php echo json_encode($models); ?>;

        // Обновление информации о модели
        function updateModelInfo() {
            const select = document.getElementById('model');
            const info = document.getElementById('modelInfo');
            const selectedModel = select.value;
            const model = models[selectedModel];
            
            if (model) {
                info.className = 'model-info ' + model.category;
                info.innerHTML = `
                    <h4>${model.name} <span class="model-category ${model.category}">${model.category.toUpperCase()}</span></h4>
                    <p>${model.description}</p>
                    <div class="model-stats">
                        <div class="stat-item">
                            <span class="stat-label">Цена</span>
                            <span class="stat-value">${model.cost_1000}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Скорость</span>
                            <span class="stat-value">${model.speed}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Качество</span>
                            <span class="stat-value">${model.quality}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Токены</span>
                            <span class="stat-value">${model.price}</span>
                        </div>
                    </div>
                `;
            }
        }

        // Обновление информации о категории
        function updateCategoryInfo() {
            const select = document.getElementById('category');
            const info = document.getElementById('categoryInfo');
            const selectedCategory = select.value;
            const template = templates[selectedCategory];
            
            if (template) {
                info.innerHTML = `
                    <h4>${template.name}</h4>
                    <p>${template.description}</p>
                `;
            }
        }

        // Инициализация при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            updateModelInfo();
            updateCategoryInfo();
        });

        document.getElementById('seoForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Обрабатываем...';
            loading.classList.add('show');
        });

        // Функция копирования в буфер обмена
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                showNotification('Скопировано в буфер обмена!');
            }).catch(function(err) {
                console.error('Ошибка копирования: ', err);
            });
        }

        // Копирование метаданных
        function copyMetadata() {
            <?php if ($result): ?>
            const title = <?php echo json_encode($result['meta_title'] ?? ''); ?>;
            const description = <?php echo json_encode($result['meta_description'] ?? ''); ?>;
            const metaText = `<title>${title}</title>\n<meta name="description" content="${description}">`;
            copyToClipboard(metaText);
            <?php else: ?>
            showNotification('Сначала создайте SEO-описание!');
            <?php endif; ?>
        }

        // Копирование всего контента
        function copyAllContent() {
            <?php if ($result): ?>
            const allContent = `H1 ЗАГОЛОВОК:
<?php echo addslashes($result['h1_title'] ?? ''); ?>

ОПИСАНИЕ ТОВАРА:
<?php echo addslashes($result['description_section'] ?? ''); ?>

ХАРАКТЕРИСТИКИ И ПРЕИМУЩЕСТВА:
<?php echo addslashes($result['features_section'] ?? ''); ?>

ОТЗЫВЫ И РЕКОМЕНДАЦИИ:
<?php echo addslashes($result['reviews_section'] ?? ''); ?>

ГДЕ КУПИТЬ:
<?php echo addslashes($result['purchase_section'] ?? ''); ?>

КЛЮЧЕВЫЕ СЛОВА:
<?php echo addslashes($result['keywords'] ?? ''); ?>

META TITLE:
<?php echo addslashes($result['meta_title'] ?? ''); ?>

META DESCRIPTION:
<?php echo addslashes($result['meta_description'] ?? ''); ?>`;
            
            copyToClipboard(allContent);
            <?php else: ?>
            showNotification('Сначала создайте SEO-описание!');
            <?php endif; ?>
        }

        // Показ уведомления при успешном создании SEO-описания
        <?php if ($result && !$error): ?>
        window.addEventListener('load', function() {
            const historyCount = <?php echo isset($_SESSION['results_history']) ? count($_SESSION['results_history']) : 0; ?>;
            showNotification(`✅ SEO-описание создано и добавлено в базу! Всего товаров: ${historyCount}`, 'success');
        });
        <?php endif; ?>

        // Показ ошибки если нет данных для экспорта Excel
        <?php if (isset($_GET['export']) && $_GET['export'] === 'excel' && isset($error) && strpos($error, 'нет результатов') !== false): ?>
        window.addEventListener('load', function() {
            showNotification('❌ <?php echo addslashes($error); ?>', 'error');
        });
        <?php endif; ?>

        // Показ уведомления
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? '#28a745' : '#dc3545';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${bgColor};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 1000;
                font-size: 14px;
                opacity: 0;
                transform: translateY(-20px);
                transition: all 0.3s ease;
                max-width: 350px;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateY(0)';
            }, 100);

            const duration = type === 'error' ? 6000 : 4000; // Ошибки показываем дольше
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, duration);
        }

        // Автосохранение в localStorage
        const textarea = document.getElementById('product_description');
        const categorySelect = document.getElementById('category');
        const modelSelect = document.getElementById('model');
        
        textarea.addEventListener('input', function() {
            localStorage.setItem('product_description', this.value);
        });
        
        categorySelect.addEventListener('change', function() {
            localStorage.setItem('selected_category', this.value);
        });
        
        modelSelect.addEventListener('change', function() {
            localStorage.setItem('selected_model', this.value);
        });

        // Восстановление из localStorage
        window.addEventListener('load', function() {
            const savedText = localStorage.getItem('product_description');
            const savedCategory = localStorage.getItem('selected_category');
            const savedModel = localStorage.getItem('selected_model');
            
            if (savedText && !textarea.value) {
                textarea.value = savedText;
            }
            
            if (savedCategory) {
                categorySelect.value = savedCategory;
                updateCategoryInfo();
            }
            
            if (savedModel) {
                modelSelect.value = savedModel;
                updateModelInfo();
            }
        });
    </script>
</body>
</html>