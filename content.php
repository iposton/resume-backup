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
    // Validate every field consumed by the PHP template before output begins.
    $object = static function ($value, array $strings, string $path): void {
        if (!is_array($value)) {
            throw new RuntimeException('Expected object: ' . $path);
        }
        foreach ($strings as $key) {
            if (!isset($value[$key]) || !is_string($value[$key])) {
                throw new RuntimeException('Expected text: ' . $path . '.' . $key);
            }
        }
    };
    $list = static function ($value, string $path): array {
        if (!is_array($value) || array_values($value) !== $value) {
            throw new RuntimeException('Expected list: ' . $path);
        }
        return $value;
    };
    $parts = static function ($value, string $path) use ($list, $object): void {
        foreach ($list($value, $path) as $part) {
            $object($part, ['text'], $path);
            foreach (['url', 'style'] as $key) {
                if (isset($part[$key]) && !is_string($part[$key])) {
                    throw new RuntimeException('Expected text: ' . $path . '.' . $key);
                }
            }
        }
    };
    foreach (['site', 'about', 'resume', 'contact', 'chat'] as $section) {
        $object($content[$section] ?? null, [], $section);
    }
    $site = $content['site'];
    $object($site, ['title', 'home_url', 'header_heading', 'heading'], 'site');
    $object($site['tabs'] ?? null, ['about', 'resume', 'contact'], 'site.tabs');
    $object($site['avatar'] ?? null, ['url', 'alt'], 'site.avatar');
    $object($site['email'] ?? null, ['url', 'label'], 'site.email');
    foreach ($list($site['social'] ?? null, 'site.social') as $social) {
        $object($social, ['url', 'icon', 'label'], 'site.social');
    }
    foreach ($list($content['about']['blocks'] ?? null, 'about.blocks') as $block) {
        $object($block, ['type'], 'about.blocks');
        if ($block['type'] === 'paragraph') {
            $object($block, ['text'], 'about.blocks');
        } elseif ($block['type'] === 'accordion') {
            $object($block, ['title'], 'about.blocks');
            foreach ($list($block['items'] ?? null, 'about.blocks.items') as $item) {
                $parts($item, 'about.blocks.items');
            }
        } else {
            throw new RuntimeException('Unknown about block type.');
        }
    }
    $resume = $content['resume'];
    $object($resume, ['heading', 'intro', 'navigation_title', 'navigation_label', 'skills_caption', 'skills_unrated'], 'resume');
    $object($resume['labels'] ?? null, ['about', 'work', 'edu', 'skills', 'refs'], 'resume.labels');
    $object($resume['download'] ?? null, ['url', 'label'], 'resume.download');
    foreach ($list($resume['jobs'] ?? null, 'resume.jobs') as $job) {
        $object($job, ['dates'], 'resume.jobs');
        $parts($job['heading'] ?? null, 'resume.jobs.heading');
        foreach ($list($job['bullets'] ?? null, 'resume.jobs.bullets') as $bullet) {
            $parts($bullet, 'resume.jobs.bullets');
        }
    }
    foreach ($list($resume['education'] ?? null, 'resume.education') as $school) {
        $object($school, ['heading', 'dates', 'description'], 'resume.education');
    }
    foreach ($list($resume['skills'] ?? null, 'resume.skills') as $group) {
        $object($group, ['title'], 'resume.skills');
        foreach ($list($group['items'] ?? null, 'resume.skills.items') as $skill) {
            $object($skill, ['name'], 'resume.skills.items');
            if (isset($skill['color']) && !is_string($skill['color'])) {
                throw new RuntimeException('Expected text: resume.skills.items.color');
            }
        }
    }
    foreach ($list($resume['references'] ?? null, 'resume.references') as $reference) {
        $object($reference, ['author', 'quote'], 'resume.references');
        foreach (['url', 'date', 'relationship'] as $key) {
            if (isset($reference[$key]) && !is_string($reference[$key])) {
                throw new RuntimeException('Expected text: resume.references.' . $key);
            }
        }
    }
    $object($content['contact'], ['success', 'error', 'honeypot_label', 'submit', 'sending'], 'contact');
    $object($content['contact']['labels'] ?? null, ['name', 'email', 'message'], 'contact.labels');
    $object($content['chat'], ['title', 'description', 'welcome', 'label', 'placeholder', 'submit'], 'chat');
    foreach ($list($content['chat']['examples'] ?? null, 'chat.examples') as $example) {
        if (!is_string($example)) {
            throw new RuntimeException('Expected text: chat.examples');
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
