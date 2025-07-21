<?php
// Конфигурация OpenRouter
$openrouter_api_key = 'sk-or-v1-'; // Замените на ваш API ключ OpenRouter
$app_name = 'SEO Копирайтер для фильмов'; // Название вашего приложения
$site_url = 'https://yourdomain.com'; // URL вашего сайта

// SEO-аналитика
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
        'readability' => calculateReadability($result['plot_section'] ?? '')
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

// Функции экспорта
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
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($result['h1_title']) . '</h1>
    
    <h2>Сюжет фильма</h2>
    <p>' . nl2br(htmlspecialchars($result['plot_section'])) . '</p>
    
    <h2>Почему стоит посмотреть</h2>
    <p>' . nl2br(htmlspecialchars($result['why_watch_section'])) . '</p>
    
    <h2>Где и как смотреть</h2>
    <p>' . nl2br(htmlspecialchars($result['where_watch_section'])) . '</p>
    
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
    
    $txt = "SEO-КОНТЕНТ ДЛЯ ФИЛЬМА\n";
    $txt .= "========================\n\n";
    $txt .= "H1 ЗАГОЛОВОК:\n" . ($result['h1_title'] ?? '') . "\n\n";
    $txt .= "СЮЖЕТ ФИЛЬМА:\n" . ($result['plot_section'] ?? '') . "\n\n";
    $txt .= "ПОЧЕМУ СТОИТ ПОСМОТРЕТЬ:\n" . ($result['why_watch_section'] ?? '') . "\n\n";
    $txt .= "ГДЕ И КАК СМОТРЕТЬ:\n" . ($result['where_watch_section'] ?? '') . "\n\n";
    $txt .= "КЛЮЧЕВЫЕ СЛОВА:\n" . ($result['keywords'] ?? '') . "\n\n";
    $txt .= "META TITLE:\n" . ($result['meta_title'] ?? '') . "\n\n";
    $txt .= "META DESCRIPTION:\n" . ($result['meta_description'] ?? '') . "\n\n";
    $txt .= "Создано: " . date('d.m.Y H:i:s') . "\n";
    
    return $txt;
}

// ФУНКЦИЯ: Добавление результата в накопительную базу
function addToResultsHistory($result, $seoMetrics) {
    if (!$result) return;
    
    // Инициализируем историю результатов если ее нет
    if (!isset($_SESSION['results_history'])) {
        $_SESSION['results_history'] = [];
    }
    
    // Добавляем новый результат с timestamp
    $_SESSION['results_history'][] = [
        'result' => $result,
        'seo_metrics' => $seoMetrics,
        'timestamp' => time(),
        'date' => date('d.m.Y H:i')
    ];
}

// ФУНКЦИЯ: Генерация Excel с накопленными результатами
function generateExcelExport($allResults = null) {
    // Если передали конкретный результат - используем только его
    if ($allResults === null) {
        $allResults = $_SESSION['results_history'] ?? [];
    }
    
    if (empty($allResults)) return '';
    
    // Создаем XML файл в формате Excel
    $excel = '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Title>SEO Контент - Накопительная база</Title>
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
 <Worksheet ss:Name="SEO База данных">
  <Table>
   <!-- Настройка ширины колонок -->
   <Column ss:Width="40"/>
   <Column ss:Width="180"/>
   <Column ss:Width="300"/>
   <Column ss:Width="300"/>
   <Column ss:Width="200"/>
   <Column ss:Width="250"/>
   <Column ss:Width="180"/>
   <Column ss:Width="180"/>
   <Column ss:Width="80"/>
   <Column ss:Width="80"/>
   <Column ss:Width="80"/>
   <Column ss:Width="80"/>
   <Column ss:Width="120"/>
   
   <!-- ЗАГОЛОВКИ (первая строка) -->
   <Row ss:Height="40">
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">№</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">H1 Заголовок</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Сюжет фильма</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Почему стоит посмотреть</Data>
    </Cell>
    <Cell ss:StyleID="Header">
     <Data ss:Type="String">Где и как смотреть</Data>
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
   
   // Добавляем все результаты как отдельные строки
   $rowNumber = 1;
   foreach ($allResults as $resultItem) {
       $result = $resultItem['result'];
       $seoMetrics = $resultItem['seo_metrics'];
       $date = $resultItem['date'];
       
       $excel .= '
   
   <!-- ДАННЫЕ (строка ' . $rowNumber . ') -->
   <Row ss:Height="100">
    <Cell ss:StyleID="Content">
     <Data ss:Type="Number">' . $rowNumber . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['h1_title'] ?? '') . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['plot_section'] ?? '') . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['why_watch_section'] ?? '') . '</Data>
    </Cell>
    <Cell ss:StyleID="Content">
     <Data ss:Type="String">' . htmlspecialchars($result['where_watch_section'] ?? '') . '</Data>
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
    
        // Добавляем SEO метрики если есть
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
if (isset($_GET['export']) && isset($_SESSION['last_result'])) {
    $result = $_SESSION['last_result'];
    $seoMetrics = analyzeSEOMetrics($result);
    
    if ($_GET['export'] === 'html') {
        $html = generateHTMLExport($result, $seoMetrics);
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="seo-content-' . date('Y-m-d-H-i') . '.html"');
        echo $html;
        exit;
    }
    
    if ($_GET['export'] === 'txt') {
        $txt = generateTXTExport($result);
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="seo-content-' . date('Y-m-d-H-i') . '.txt"');
        echo $txt;
        exit;
    }
    
    // ЭКСПОРТ НАКОПИТЕЛЬНОЙ БАЗЫ В EXCEL
    if ($_GET['export'] === 'excel') {
        $excel = generateExcelExport();
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="seo-database-' . date('Y-m-d-H-i') . '.xls"');
        echo $excel;
        exit;
    }
}

// Обработка очистки истории
if (isset($_GET['action']) && $_GET['action'] === 'clear_history') {
    clearResultsHistory();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Доступные модели OpenRouter
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

// Шаблоны промптов по жанрам (такие же как в оригинале)
function getGenreTemplates() {
    return [
        'universal' => [
            'name' => '🎬 Универсальный',
            'description' => 'Подходит для любых жанров и смешанных фильмов',
            'prompt' => "Ты профессиональный SEO-копирайтер с 10+ лет опыта. Получишь на вход краткое описание фильма. Твоя задача — переписать его для SEO-оптимизации.

**ВХОДНОЙ ТЕКСТ:**
{FILM_DESCRIPTION}

**ТРЕБОВАНИЯ:**
✅ Расширенный текст на 300–400 слов
✅ Включены ключевые слова: название фильма, год, \"смотреть онлайн\", \"фильм бесплатно\", жанр
✅ Эмоциональный и живой стиль
✅ Призыв к действию

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название фильма (год) - смотреть онлайн бесплатно]\",
  \"plot_section\": \"[1-2 абзаца о сюжете фильма с акцентом на интригу и основной конфликт]\",
  \"why_watch_section\": \"[1-2 абзаца почему стоит посмотреть, включая качество актерской игры, режиссуру, визуальные эффекты]\",
  \"where_watch_section\": \"[Где смотреть онлайн бесплатно с призывом к действию]\",
  \"keywords\": \"[ключевые слова через запятую включая жанр, актеров, режиссера]\",
  \"meta_title\": \"[Title до 60 символов с названием и годом]\",
  \"meta_description\": \"[Description 150-160 символов с эмоциональным описанием]\"
}

Отвечай ТОЛЬКО валидным JSON без markdown разметки!"
        ],
        
        'action' => [
            'name' => '💥 Боевик/Экшен',
            'description' => 'Акцент на адреналин, динамику, спецэффекты',
            'prompt' => "Ты профессиональный SEO-копирайтер, специализирующийся на боевиках и экшн-фильмах. Твоя задача — создать захватывающее SEO-описание, которое передает весь адреналин и динамику фильма.

**ВХОДНОЙ ТЕКСТ:**
{FILM_DESCRIPTION}

**СТИЛЬ ДЛЯ БОЕВИКОВ:**
🔥 Используй динамичные фразы: \"захватывающий экшен\", \"головокружительные трюки\", \"нон-стоп действие\"
💥 Подчеркни спецэффекты, каскадерские трюки, батальные сцены
⚡ Акцент на адреналин, напряжение, зрелищность
🎯 Ключевики: \"боевик\", \"экшен\", \"трюки\", \"погони\", \"взрывы\", \"сражения\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название фильма (год) - захватывающий боевик смотреть онлайн]\",
  \"plot_section\": \"[Динамичное описание сюжета с акцентом на экшен-сцены, погони, сражения и напряженные моменты]\",
  \"why_watch_section\": \"[Почему стоит смотреть: упор на зрелищность, спецэффекты, каскадерскую работу, динамику действия]\",
  \"where_watch_section\": \"[Призыв смотреть экшен онлайн бесплатно с упором на качество и адреналин]\",
  \"keywords\": \"[боевик, экшен, трюки, спецэффекты, + актеры и сюжетные ключевики]\",
  \"meta_title\": \"[Название + год + 'боевик смотреть онлайн' до 60 символов]\",
  \"meta_description\": \"[Захватывающее описание с динамичными фразами, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'comedy' => [
            'name' => '😂 Комедия',
            'description' => 'Акцент на юмор, легкость, позитивные эмоции',
            'prompt' => "Ты профессиональный SEO-копирайтер, мастер по продвижению комедий. Твоя задача — создать веселое и привлекательное SEO-описание, которое заразит читателя позитивом и желанием посмеяться.

**ВХОДНОЙ ТЕКСТ:**
{FILM_DESCRIPTION}

**СТИЛЬ ДЛЯ КОМЕДИЙ:**
😂 Используй позитивные фразы: \"заразительный юмор\", \"море смеха\", \"отличное настроение\"
🎭 Подчеркни комедийные ситуации, шутки, харизму актеров
☀️ Акцент на легкость, позитив, развлечение, отдых
🎯 Ключевики: \"комедия\", \"юмор\", \"смешно\", \"весело\", \"позитив\", \"развлечение\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название фильма (год) - веселая комедия смотреть онлайн]\",
  \"plot_section\": \"[Легкое описание сюжета с акцентом на забавные ситуации, комедийные моменты и юмористические элементы]\",
  \"why_watch_section\": \"[Почему стоит смотреть: упор на юмор, позитив, отличное настроение, талант комедийных актеров]\",
  \"where_watch_section\": \"[Призыв смотреть комедию онлайн для поднятия настроения и получения массы позитива]\",
  \"keywords\": \"[комедия, юмор, смешно, весело, + актеры и тематические ключевики]\",
  \"meta_title\": \"[Название + год + 'комедия смотреть онлайн' до 60 символов]\",
  \"meta_description\": \"[Веселое описание с позитивными фразами, обещание смеха, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'drama' => [
            'name' => '🎭 Драма',
            'description' => 'Акцент на эмоции, глубину, человеческие отношения',
            'prompt' => "Ты профессиональный SEO-копирайтер, специалист по драматическим фильмам. Твоя задача — создать глубокое и эмоциональное SEO-описание, которое передает всю силу человеческих переживаний.

**ВХОДНОЙ ТЕКСТ:**
{FILM_DESCRIPTION}

**СТИЛЬ ДЛЯ ДРАМ:**
💫 Используй эмоциональные фразы: \"трогательная история\", \"глубокие переживания\", \"пронзительная драма\"
❤️ Подчеркни человеческие отношения, внутренние конфликты, личностный рост
🎭 Акцент на эмоции, психологию, жизненные уроки, смысл
🎯 Ключевики: \"драма\", \"эмоции\", \"переживания\", \"отношения\", \"жизненная история\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название фильма (год) - трогательная драма смотреть онлайн]\",
  \"plot_section\": \"[Эмоциональное описание сюжета с акцентом на внутренние переживания героев, отношения, жизненные испытания]\",
  \"why_watch_section\": \"[Почему стоит смотреть: упор на глубину сюжета, актерское мастерство, эмоциональное воздействие, жизненные уроки]\",
  \"where_watch_section\": \"[Призыв смотреть драму онлайн для получения глубоких эмоций и переживаний]\",
  \"keywords\": \"[драма, эмоции, переживания, отношения, + актеры и тематические ключевики]\",
  \"meta_title\": \"[Название + год + 'драма смотреть онлайн' до 60 символов]\",
  \"meta_description\": \"[Эмоциональное описание с глубокими фразами, обещание переживаний, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'horror' => [
            'name' => '😱 Ужасы/Триллер',
            'description' => 'Акцент на атмосферу страха, напряжение, мистику',
            'prompt' => "Ты профессиональный SEO-копирайтер, специалист по фильмам ужасов и триллерам. Твоя задача — создать атмосферное SEO-описание, которое передает весь ужас и напряжение, заставляя зрителей хотеть испытать острые ощущения.

**ВХОДНОЙ ТЕКСТ:**
{FILM_DESCRIPTION}

**СТИЛЬ ДЛЯ УЖАСОВ:**
🔥 Используй атмосферные фразы: \"леденящий ужас\", \"напряженная атмосфера\", \"мистический триллер\"
👻 Подчеркни саспенс, мистику, неожиданные повороты, атмосферу страха
😰 Акцент на напряжение, острые ощущения, адреналин от страха
🎯 Ключевики: \"ужасы\", \"триллер\", \"страх\", \"мистика\", \"саспенс\", \"напряжение\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название фильма (год) - леденящий ужасы смотреть онлайн]\",
  \"plot_section\": \"[Атмосферное описание сюжета с акцентом на мистические элементы, напряженные моменты, неразгаданные тайны]\",
  \"why_watch_section\": \"[Почему стоит смотреть: упор на атмосферу, саспенс, качество ужасов, неожиданные повороты сюжета]\",
  \"where_watch_section\": \"[Призыв смотреть ужасы онлайн для любителей острых ощущений и мистической атмосферы]\",
  \"keywords\": \"[ужасы, триллер, страх, мистика, саспенс, + актеры и тематические ключевики]\",
  \"meta_title\": \"[Название + год + 'ужасы смотреть онлайн' до 60 символов]\",
  \"meta_description\": \"[Атмосферное описание с мистическими фразами, обещание ужаса, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'scifi' => [
            'name' => '🚀 Фантастика',
            'description' => 'Акцент на технологии, будущее, научные концепции',
            'prompt' => "Ты профессиональный SEO-копирайтер, эксперт по научной фантастике. Твоя задача — создать захватывающее SEO-описание, которое передает весь масштаб научно-фантастического мира и технологических чудес.

**ВХОДНОЙ ТЕКСТ:**
{FILM_DESCRIPTION}

**СТИЛЬ ДЛЯ ФАНТАСТИКИ:**
🌌 Используй футуристические фразы: \"захватывающая фантастика\", \"технологии будущего\", \"научные открытия\"
🚀 Подчеркни спецэффекты, технологии, инопланетян, космос, будущее
⚡ Акцент на инновации, научные концепции, визуальные эффекты
🎯 Ключевики: \"фантастика\", \"sci-fi\", \"технологии\", \"будущее\", \"космос\", \"спецэффекты\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название фильма (год) - захватывающая фантастика смотреть онлайн]\",
  \"plot_section\": \"[Описание сюжета с акцентом на научно-фантастические элементы, технологии, космические приключения или футуристический мир]\",
  \"why_watch_section\": \"[Почему стоит смотреть: упор на визуальные эффекты, научные концепции, масштаб вселенной, инновационность]\",
  \"where_watch_section\": \"[Призыв смотреть фантастику онлайн для погружения в мир будущего и технологий]\",
  \"keywords\": \"[фантастика, sci-fi, технологии, будущее, космос, + актеры и научные ключевики]\",
  \"meta_title\": \"[Название + год + 'фантастика смотреть онлайн' до 60 символов]\",
  \"meta_description\": \"[Футуристическое описание с научными фразами, обещание приключений, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'romance' => [
            'name' => '💕 Романтика',
            'description' => 'Акцент на любовь, отношения, эмоциональную связь',
            'prompt' => "Ты профессиональный SEO-копирайтер, специалист по романтическим фильмам. Твоя задача — создать нежное и трогательное SEO-описание, которое передает всю красоту любви и романтических отношений.

**ВХОДНОЙ ТЕКСТ:**
{FILM_DESCRIPTION}

**СТИЛЬ ДЛЯ РОМАНТИКИ:**
💖 Используй романтические фразы: \"трогательная любовная история\", \"нежные чувства\", \"романтическая сказка\"
🌹 Подчеркни отношения, чувства, эмоциональную связь, красоту любви
✨ Акцент на романтику, нежность, эмоции, счастливые моменты
🎯 Ключевики: \"романтика\", \"любовь\", \"отношения\", \"чувства\", \"мелодрама\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название фильма (год) - трогательная романтика смотреть онлайн]\",
  \"plot_section\": \"[Нежное описание сюжета с акцентом на развитие отношений, романтические моменты, эмоциональную связь героев]\",
  \"why_watch_section\": \"[Почему стоит смотреть: упор на красоту любви, эмоциональность, актерскую химию, романтическую атмосферу]\",
  \"where_watch_section\": \"[Призыв смотреть романтику онлайн для получения нежных эмоций и веры в любовь]\",
  \"keywords\": \"[романтика, любовь, отношения, чувства, мелодрама, + актеры и романтические ключевики]\",
  \"meta_title\": \"[Название + год + 'романтика смотреть онлайн' до 60 символов]\",
  \"meta_description\": \"[Романтическое описание с нежными фразами, обещание любви, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ],
        
        'animation' => [
            'name' => '🎨 Мультфильм',
            'description' => 'Акцент на анимацию, семейные ценности, магию',
            'prompt' => "Ты профессиональный SEO-копирайтер, специалист по анимационным фильмам. Твоя задача — создать яркое и волшебное SEO-описание, которое передает всю магию анимации и подходит как детям, так и взрослым.

**ВХОДНОЙ ТЕКСТ:**
{FILM_DESCRIPTION}

**СТИЛЬ ДЛЯ МУЛЬТФИЛЬМОВ:**
🌈 Используй яркие фразы: \"волшебный мультфильм\", \"красочная анимация\", \"семейное приключение\"
✨ Подчеркни качество анимации, семейные ценности, магию, приключения
🎭 Акцент на развлечение для всей семьи, позитив, волшебство
🎯 Ключевики: \"мультфильм\", \"анимация\", \"семейный\", \"приключения\", \"волшебство\"

**ВАЖНО:** Верни результат ТОЛЬКО в формате JSON без дополнительного текста:

{
  \"h1_title\": \"[Название фильма (год) - волшебный мультфильм смотреть онлайн]\",
  \"plot_section\": \"[Яркое описание сюжета с акцентом на приключения, волшебные элементы, дружбу и семейные ценности]\",
  \"why_watch_section\": \"[Почему стоит смотреть: упор на качество анимации, семейные ценности, развлечение для всех возрастов, позитивные эмоции]\",
  \"where_watch_section\": \"[Призыв смотреть мультфильм онлайн всей семьей для получения волшебных эмоций]\",
  \"keywords\": \"[мультфильм, анимация, семейный, приключения, волшебство, + персонажи и тематические ключевики]\",
  \"meta_title\": \"[Название + год + 'мультфильм смотреть онлайн' до 60 символов]\",
  \"meta_description\": \"[Волшебное описание с яркими фразами, обещание приключений, 150-160 символов]\"
}

Отвечай ТОЛЬКО валидным JSON!"
        ]
    ];
}

// Обработка POST запроса
session_start();
$result = null;
$error = '';

if ($_POST && isset($_POST['film_description']) && !empty(trim($_POST['film_description']))) {
    $film_description = trim($_POST['film_description']);
    $selected_genre = $_POST['genre'] ?? 'universal';
    $selected_model = $_POST['model'] ?? 'qwen/qwen-2.5-72b-instruct:free';
    
    $templates = getGenreTemplates();
    $template = $templates[$selected_genre];
    
    // Подставляем описание фильма в шаблон
    $prompt = str_replace('{FILM_DESCRIPTION}', $film_description, $template['prompt']);
    
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
        'HTTP-Referer: ' . $site_url, // Опционально
        'X-Title: ' . $app_name // Опционально
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

$templates = getGenreTemplates();
$models = getOpenRouterModels();
$seoMetrics = $result ? analyzeSEOMetrics($result) : null;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Копирайтер для фильмов | OpenRouter AI с 500+ моделями</title>
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

        .genre-info {
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

        .genre-info h4, .model-info h4 {
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

        .genre-info p, .model-info p {
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

        /* НОВЫЙ СТИЛЬ ДЛЯ EXCEL */
        .export-btn.excel {
            background: linear-gradient(135deg, #217346 0%, #0F5132 100%);
        }

        .export-btn.excel:hover {
            box-shadow: 0 8px 16px rgba(33, 115, 70, 0.3);
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
            <h1><i class="fas fa-robot"></i> SEO Копирайтер для фильмов</h1>
            <p>Создавайте идеальные SEO-тексты с помощью 500+ AI моделей</p>
            <div class="openrouter-badge">
                <i class="fas fa-rocket"></i> Работает на OpenRouter.ai • 22 лучших модели
            </div>
        </div>

        <div class="main-content">
            <div class="input-section">
                <h2 class="section-title">
                    <i class="fas fa-edit"></i>
                    Создание SEO-контента
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
                                        echo $model['name'] . ' - ' . $model['cost_1000'] . ' за 1000 текстов';
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
                        <label for="genre">🎭 Выберите жанр фильма:</label>
                        <select name="genre" id="genre" onchange="updateGenreInfo()">
                            <?php foreach ($templates as $key => $template): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($_POST['genre'] ?? 'universal') == $key ? 'selected' : ''; ?>>
                                    <?php echo $template['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="genre-info" id="genreInfo">
                            <!-- Информация о жанре будет обновляться JavaScript -->
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="film_description">Исходное описание фильма:</label>
                        <textarea 
                            name="film_description" 
                            id="film_description" 
                            rows="8" 
                            placeholder="Вставьте краткое описание фильма, которое нужно переработать для SEO...&#10;&#10;Например:&#10;«Джон Уик 4 (2023) - боевик о легендарном киллере, который продолжает свою борьбу против Высшего стола. В четвертой части франшизы герой Киану Ривза отправляется в путешествие по миру в поисках способа победить могущественную организацию.»"
                            required><?php echo htmlspecialchars($_POST['film_description'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn" id="submitBtn">
                        <i class="fas fa-magic"></i>
                        Создать SEO-текст
                    </button>
                </form>
            </div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Генерируем SEO-текст...</p>
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
                        SEO-результат (<?php echo $templates[$_POST['genre']]['name']; ?> + <?php echo $models[$_POST['model']]['name']; ?>)
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
                                    📊 В базе: <?php echo $historyCount; ?> фильмов
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
                            <!-- ОБНОВЛЕННАЯ КНОПКА EXCEL -->
                            <a href="?export=excel" class="export-btn excel" target="_blank">
                                <i class="fas fa-file-excel"></i>
                                <?php if ($historyCount > 1): ?>
                                    База Excel (<?php echo $historyCount; ?> фильмов)
                                <?php else: ?>
                                    Скачать Excel
                                <?php endif; ?>
                            </a>
                            <button class="export-btn copy" onclick="copyAllContent()">
                                <i class="fas fa-copy"></i>
                                Копировать всё
                            </button>
                            <?php if ($historyCount > 0): ?>
                                <a href="?action=clear_history" class="export-btn" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);" onclick="return confirm('Очистить всю накопленную базу из <?php echo $historyCount; ?> фильмов?')">
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

                        <!-- Сюжет -->
                        <div class="result-card">
                            <div class="card-title">
                                <i class="fas fa-book"></i>
                                Сюжет фильма
                            </div>
                            <div class="card-content">
                                <?php echo nl2br(htmlspecialchars($result['plot_section'] ?? 'Не указано')); ?>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($result['plot_section'] ?? ''); ?>')">
                                    <i class="fas fa-copy"></i> Копировать
                                </button>
                            </div>
                        </div>

                        <!-- Почему стоит посмотреть -->
                        <div class="result-card">
                            <div class="card-title">
                                <i class="fas fa-star"></i>
                                Почему стоит посмотреть
                            </div>
                            <div class="card-content">
                                <?php echo nl2br(htmlspecialchars($result['why_watch_section'] ?? 'Не указано')); ?>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($result['why_watch_section'] ?? ''); ?>')">
                                    <i class="fas fa-copy"></i> Копировать
                                </button>
                            </div>
                        </div>

                        <!-- Где смотреть -->
                        <div class="result-card">
                            <div class="card-title">
                                <i class="fas fa-play-circle"></i>
                                Где и как смотреть
                            </div>
                            <div class="card-content">
                                <?php echo nl2br(htmlspecialchars($result['where_watch_section'] ?? 'Не указано')); ?>
                                <button class="copy-btn" onclick="copyToClipboard('<?php echo addslashes($result['where_watch_section'] ?? ''); ?>')">
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
                        <i class="fas fa-file-alt"></i>
                        <h3>Здесь появится ваш SEO-текст</h3>
                        <p>Выберите AI модель, жанр, введите описание фильма и нажмите "Создать SEO-текст"</p>
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

        // Обновление информации о жанре
        function updateGenreInfo() {
            const select = document.getElementById('genre');
            const info = document.getElementById('genreInfo');
            const selectedGenre = select.value;
            const template = templates[selectedGenre];
            
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
            updateGenreInfo();
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
            showNotification('Сначала создайте SEO-текст!');
            <?php endif; ?>
        }

        // Копирование всего контента
        function copyAllContent() {
            <?php if ($result): ?>
            const allContent = `H1 ЗАГОЛОВОК:
<?php echo addslashes($result['h1_title'] ?? ''); ?>

СЮЖЕТ ФИЛЬМА:
<?php echo addslashes($result['plot_section'] ?? ''); ?>

ПОЧЕМУ СТОИТ ПОСМОТРЕТЬ:
<?php echo addslashes($result['why_watch_section'] ?? ''); ?>

ГДЕ И КАК СМОТРЕТЬ:
<?php echo addslashes($result['where_watch_section'] ?? ''); ?>

КЛЮЧЕВЫЕ СЛОВА:
<?php echo addslashes($result['keywords'] ?? ''); ?>

META TITLE:
<?php echo addslashes($result['meta_title'] ?? ''); ?>

META DESCRIPTION:
<?php echo addslashes($result['meta_description'] ?? ''); ?>`;
            
            copyToClipboard(allContent);
            <?php else: ?>
            showNotification('Сначала создайте SEO-текст!');
            <?php endif; ?>
        }

        // Показ уведомления при успешном создании SEO-текста
        <?php if ($result && !$error): ?>
        window.addEventListener('load', function() {
            const historyCount = <?php echo isset($_SESSION['results_history']) ? count($_SESSION['results_history']) : 0; ?>;
            showNotification(`✅ SEO-текст создан и добавлен в базу! Всего фильмов: ${historyCount}`, 'success');
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
                max-width: 300px;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateY(0)';
            }, 100);

            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 4000);
        }

        // Автосохранение в localStorage
        const textarea = document.getElementById('film_description');
        const genreSelect = document.getElementById('genre');
        const modelSelect = document.getElementById('model');
        
        textarea.addEventListener('input', function() {
            localStorage.setItem('film_description', this.value);
        });
        
        genreSelect.addEventListener('change', function() {
            localStorage.setItem('selected_genre', this.value);
        });
        
        modelSelect.addEventListener('change', function() {
            localStorage.setItem('selected_model', this.value);
        });

        // Восстановление из localStorage
        window.addEventListener('load', function() {
            const savedText = localStorage.getItem('film_description');
            const savedGenre = localStorage.getItem('selected_genre');
            const savedModel = localStorage.getItem('selected_model');
            
            if (savedText && !textarea.value) {
                textarea.value = savedText;
            }
            
            if (savedGenre) {
                genreSelect.value = savedGenre;
                updateGenreInfo();
            }
            
            if (savedModel) {
                modelSelect.value = savedModel;
                updateModelInfo();
            }
        });
    </script>
</body>
</html>
