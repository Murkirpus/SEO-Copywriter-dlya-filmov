<?php
// Конфигурация
$openai_api_key = 'sk-proj-';

// Доступные модели OpenAI
function getOpenAIModels() {
    return [
        'gpt-4.1-nano' => [
            'name' => '🏆 GPT-4.1 Nano',
            'description' => 'Самая быстрая и дешевая! Идеально для SEO',
            'price' => '$0.10 / $0.40 за 1М токенов',
            'cost_1000' => '$0.60',
            'speed' => '⚡⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => true
        ],
        'gpt-4.1-mini' => [
            'name' => '🥈 GPT-4.1 Mini',
            'description' => 'Отличное качество, быстрая обработка',
            'price' => '$0.15 / $0.60 за 1М токенов',
            'cost_1000' => '$1.20',
            'speed' => '⚡⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => false
        ],
        'gpt-4o-mini' => [
            'name' => '📊 GPT-4o Mini',
            'description' => 'Предыдущее поколение, стабильная',
            'price' => '$0.15 / $0.60 за 1М токенов',
            'cost_1000' => '$3.00',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => false
        ],
        'gpt-4.1' => [
            'name' => '💎 GPT-4.1',
            'description' => 'Премиум качество, лучшие тексты',
            'price' => '$0.40 / $1.60 за 1М токенов',
            'cost_1000' => '$8.00',
            'speed' => '⚡⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => false
        ],
        'gpt-4o' => [
            'name' => '🔥 GPT-4o',
            'description' => 'Высокое качество, дорогая',
            'price' => '$2.50 / $10.00 за 1М токенов',
            'cost_1000' => '$50.00',
            'speed' => '⚡⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => false
        ],
        'gpt-4-turbo' => [
            'name' => '⚙️ GPT-4 Turbo',
            'description' => 'Старая модель, очень дорогая',
            'price' => '$10.00 / $30.00 за 1М токенов',
            'cost_1000' => '$200.00',
            'speed' => '⚡',
            'quality' => '⭐⭐⭐⭐⭐',
            'recommended' => false
        ],
        'gpt-4' => [
            'name' => '🐌 GPT-4',
            'description' => 'Устаревшая, крайне дорогая',
            'price' => '$30.00 / $60.00 за 1М токенов',
            'cost_1000' => '$450.00',
            'speed' => '⚡',
            'quality' => '⭐⭐⭐⭐',
            'recommended' => false
        ]
    ];
}

// Шаблоны промптов по жанрам
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
$result = null;
$error = '';

if ($_POST && isset($_POST['film_description']) && !empty(trim($_POST['film_description']))) {
    $film_description = trim($_POST['film_description']);
    $selected_genre = $_POST['genre'] ?? 'universal';
    $selected_model = $_POST['model'] ?? 'gpt-4.1-nano';
    
    $templates = getGenreTemplates();
    $template = $templates[$selected_genre];
    
    // Подставляем описание фильма в шаблон
    $prompt = str_replace('{FILM_DESCRIPTION}', $film_description, $template['prompt']);
    
    // Запрос к OpenAI API с выбранной моделью
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
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openai_api_key
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
            }
        } else {
            $error = 'Ошибка в ответе API';
        }
    } else {
        $error = 'Ошибка запроса: ' . $http_code;
    }
}

$templates = getGenreTemplates();
$models = getOpenAIModels();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Копирайтер для фильмов | AI-помощник с выбором моделей и жанров</title>
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

        .model-info.expensive {
            background: #fff3cd;
            border-left-color: #ffc107;
        }

        .model-info.very-expensive {
            background: #f8d7da;
            border-left-color: #dc3545;
        }

        .genre-info h4, .model-info h4 {
            color: #667eea;
            margin-bottom: 5px;
        }

        .model-info h4 {
            color: #28a745;
        }

        .model-info.expensive h4 {
            color: #856404;
        }

        .model-info.very-expensive h4 {
            color: #721c24;
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

        .genre-preview {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            border: 1px solid #667eea20;
        }

        .genre-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .feature-tag {
            background: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: #667eea;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .results-grid {
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

            .genre-features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-robot"></i> SEO Копирайтер для фильмов</h1>
            <p>Выберите модель AI и жанр для создания идеальных SEO-текстов</p>
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
                            <?php foreach ($models as $key => $model): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($_POST['model'] ?? 'gpt-4.1-nano') == $key ? 'selected' : ''; ?>>
                                    <?php echo $model['name']; ?> - <?php echo $model['cost_1000']; ?> за 1000 текстов
                                    <?php echo $model['recommended'] ? ' (РЕКОМЕНДУЕТСЯ)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
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
                        <p>Выберите жанр, введите описание фильма и нажмите "Создать SEO-текст"</p>
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
                // Определяем класс по цене
                let priceClass = '';
                let costNum = parseFloat(model.cost_1000.replace('$', ''));
                if (costNum > 100) {
                    priceClass = 'very-expensive';
                } else if (costNum > 10) {
                    priceClass = 'expensive';
                }
                
                info.className = 'model-info ' + priceClass;
                info.innerHTML = `
                    <h4>${model.name}</h4>
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

        // Показ уведомления
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #28a745;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 1000;
                font-size: 14px;
                opacity: 0;
                transform: translateY(-20px);
                transition: all 0.3s ease;
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
            }, 3000);
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