<?php
include 'app/functions.php';
if (!file_exists($loginFilePath)) {http_response_code(401); echo 'Login required.'; exit;}
header("Content-Type: application/json");
//header('Content-Type: audio/x-mpegurl');
//header('Content-Disposition: attachment; filename="playlist.m3u"');
$filename = 'data.json';
$response = @file_get_contents($filename);
$data = json_decode($response, true);
$channels = $data['data']['channels'] ?? [];
if (!is_array($channels)) {http_response_code(500);echo "# Error: Invalid or missing 'list' in response\n";exit;}
if (stripos($userAgent, 'tivimate') !== false) {$liveheaders = '| X-Forwarded-For=59.178.74.184 | Origin=https://watch.tataplay.com | Referer=https://watch.tataplay.com/';} elseif  (stripos($userAgent, 'SparkleTV') !== false) {$liveheaders = '|X-Forwarded-For=59.178.74.184|Origin=https://watch.tataplay.com|Referer=https://watch.tataplay.com/';} else {$liveheaders = '|X-Forwarded-For=59.178.74.184&Origin=https://watch.tataplay.com&Referer=https://watch.tataplay.com/';}
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$port = $_SERVER['SERVER_PORT'];
$host_with_port = $host;
if (($protocol === 'http' && $port !== '80') || ($protocol === 'https' && $port !== '443')) {$host_with_port = $_SERVER['SERVER_NAME'] . ':' . $port;}
$request_uri = $_SERVER['REQUEST_URI'];
$path = dirname($request_uri);
$base_url = "{$protocol}://{$host_with_port}{$path}";
$is_apache = isApacheCompatible();
if ($is_apache) {$htaccess_path = '.htaccess';
    $stream_path = file_exists($htaccess_path) ? "manifest.mpd" : "get-mpd.php";} else {
    $stream_path = "get-mpd.php";
}
echo "#EXTM3U x-tvg-url=https://avkb.short.gy/epg.xml.gz\n\n";
foreach ($channels as $channel) {
    $channel_id = $channel['id'];    
    $channel_name = $channel['name'];
    $channel_logo = $channel['logo_url'];
    $channel_genre = $channel['primaryGenre'];
        
    $license_url = "https://tp.drmlive-01.workers.dev?id={$channel_id}";
    
    $channel_live = "{$base_url}/{$stream_path}?id={$channel_id}{$liveheaders}";

    echo "#EXTINF:-1 tvg-id=\"ts{$channel_id}\" tvg-logo=\"{$channel_logo}\" group-title=\"{$channel_genre}\",{$channel_name}\n";
    echo "#KODIPROP:inputstream.adaptive.license_type=clearkey\n";
    echo "#KODIPROP:inputstream.adaptive.license_key={$license_url}\n";
    echo "#KODIPROP:inputstream.adaptive.manifest_type=mpd\n";
    echo "#EXTVLCOPT:http-user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36\n";
    echo "{$channel_live}\n\n";
}
