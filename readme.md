# Soundcloud API for PHP

![SoundCloud](https://img.shields.io/static/v1?style=flat-square&message=SoundCloud&color=FF3300&logo=SoundCloud&logoColor=FFFFFF&label=)
![php](https://img.shields.io/badge/PHP-v7.3-828cb7.svg?style=flat-square)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](licence.md)

A PHP Wrapper for the Soundcloud REST API endpoints.

## Installation
First, you need to add the component to your composer.json
```
composer require noweh/php-soundcloud
```
Update your packages with *composer update* or install with *composer install*.

## Usage
First, you have to create a new instance of the wrapper with the following parameters:

```
use Noweh\SoundcloudApi\Soundcloud;

$client = new SoundCloud(
    {CLIENT_ID},
    {CLIENT_SECRET},
    {CALLBACK_URL}
);
```

### Get Player Embed
To retrieve the widget embed code for any SoundCloud URL pointing to a user, set, or a playlist, do the following:
```
... // Create a new instance of client

// Required parameter
$url = 'https://soundcloud.com/......';

// Optional parameters
$maxheight = 180;
$sharing = true;
$liking = true;
$download = false;
$show_comments = true;
$show_playcount = false;
$show_user = false;

try {
    $response = $client->getPlayerEmbed($url, $maxheight, $sharing, $liking, $download, $show_comments, $show_playcount, $show_user)
} catch (Exception $e) {
    exit($e->getMessage());
}
```

### GET
```
... // Create a new instance of client

try {
    $response = $client->get('users/{CLIENT_ID}/tracks');
} catch (Exit $e) {
    exit($e->getMessage());
}
```

### POST
```
... // Create a new instance of client

try {
    $response = $client->post(
        'tracks/1/comments',
        [
            'body' => 'a new comment'
        ]
    );
} catch (Exception $e) {
    exit($e->getMessage());
}
```

### PUT
```
... // Create a new instance of client

$trackData = json_encode();

try {
    $response = $client->put(
        'tracks/1',
        [
            'title' => 'my new title'
        ]
    );
} catch (Exception $e) {
    exit($e->getMessage());
}
```

### DELETE
```
... // Create a new instance of client

try {
    $response = $client->delete('tracks/1');
} catch (Exception $e) {
    exit($e->getMessage());
}
```