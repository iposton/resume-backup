<?php
// Load editable copy before sending any HTML so errors return an HTTP 500.
try {
    $path = __DIR__ . '/content.json';
    if (!is_readable($path)) {
        throw new RuntimeException('content.json is missing or unreadable.');
    }
    $content = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($content)) {
        throw new RuntimeException('content.json must contain a JSON object.');
    }
    foreach (['site', 'about', 'resume', 'contact'] as $section) {
        if (!isset($content[$section]) || !is_array($content[$section])) {
            throw new RuntimeException('Missing content section: ' . $section);
        }
    }
} catch (Throwable $error) {
    http_response_code(500);
    exit('Unable to load content.json: ' . htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}
function escapeContent(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function contentUrl(string $value): string {
    // Allow web links, email links, and local paths; never executable URL schemes.
    if (preg_match('/^\s*(?:javascript|data|vbscript):/i', $value) ||
        (preg_match('/^[a-z][a-z0-9+.-]*:/i', $value) && !preg_match('/^(?:https?|mailto):/i', $value))) {
        return '#';
    }
    return escapeContent($value);
}
function renderParts(array $parts): void {
    foreach ($parts as $part) {
        $text = escapeContent($part['text']);
        if (isset($part['url'])) {
            echo '<a href="' . contentUrl($part['url']) . '" target="_blank" rel="noopener noreferrer">' . $text . '</a>';
        } elseif (in_array($part['style'] ?? '', ['em', 'strong'], true)) {
            $tag = $part['style'];
            echo '<' . $tag . '>' . $text . '</' . $tag . '>';
        } else {
            echo $text;
        }
    }
}
