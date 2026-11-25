<?php
declare(strict_types=1);

function patrona_modal_youtube_id(string $url): string
{
    $parts = parse_url($url);
    if (!is_array($parts) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
        return '';
    }

    $host = strtolower($parts['host'] ?? '');
    $path = trim($parts['path'] ?? '', '/');
    $id = '';
    if ($host === 'youtu.be') {
        $id = explode('/', $path)[0];
    } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
        if ($path === 'watch') {
            parse_str($parts['query'] ?? '', $query);
            $id = $query['v'] ?? '';
        } elseif (preg_match('~^(?:embed|shorts)/([^/]+)$~', $path, $match)) {
            $id = $match[1];
        }
    }

    return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/D', $id) ? $id : '';
}

function patrona_modal_local_media(string $path): string
{
    return preg_match('~^/?assets/(?:[a-zA-Z0-9_%.-]+/)*[a-zA-Z0-9_% .-]+$~D', $path)
        && !str_contains(rawurldecode($path), '..') ? $path : '';
}

function patrona_modal_is_video(string $path): bool
{
    return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'mp4';
}

function patrona_modal_link(string $url): string
{
    $url = trim($url);
    if (preg_match('~^https?://~i', $url) && filter_var($url, FILTER_VALIDATE_URL)) return $url;
    if (preg_match('~^(?:#[a-zA-Z0-9_-]+|/(?!/)[a-zA-Z0-9_./?&=%#-]+)$~D', $url)) return $url;
    if (preg_match('~^index\.php(?:#[a-zA-Z0-9_-]+)?$~D', $url)) return '/'.$url;
    return '#';
}
