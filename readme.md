# SoundCloud API for PHP

![SoundCloud](https://img.shields.io/static/v1?style=flat-square&message=SoundCloud&color=FF3300&logo=SoundCloud&logoColor=FFFFFF&label=)
![PHP](https://img.shields.io/badge/PHP-v7.3-828cb7.svg?style=flat-square&logo=php)
[![MIT Licensed](https://img.shields.io/github/license/noweh/php-soundcloud)](licence.md)
[![last version](https://img.shields.io/packagist/v/noweh/php-soundcloud)](https://packagist.org/packages/noweh/php-soundcloud)

A PHP Wrapper for the SoundCloud REST API endpoints.

## Installation
First, you need to add the component to your composer.json
```
composer require noweh/php-soundcloud
```
Update your packages with *composer update* or install with *composer install*.

## Usage

For the calls to be valid, you must follow a few steps :


First, you have to create a new instance of the wrapper with the following parameters:

```
use Noweh\SoundcloudApi\Soundcloud;

$client = new SoundCloud(
    {CLIENT_ID},
    {CLIENT_SECRET},
    {CALLBACK_URL}
);
```

⚠️ Since [July 2021](https://developers.soundcloud.com/blog/security-updates-api), most calls to SoundCloud REST API requires an `access_token`.

⚠️ `{CALLBACK_URL}` must be the same as the one indicated in your SoundCloud account.

Second, you have to redirect the user to the SoundCloud login page:
```
...
header("Location: " . $client->getAuthorizeUrl('a_custom_param_to_retrieve_in_callback'));
exit();
```

On your callback URL, you can call GET/POST/PUT/DELETE methods. The `access_token` will be automatically generated with the `code` parameter present in this URL.

If you want to use API calls in another page, you have to set manually this data:
```
use Noweh\SoundcloudApi\Soundcloud;

$client = new SoundCloud(
    {CLIENT_ID},
    {CLIENT_SECRET},
    {CALLBACK_URL}
);

$client->setCode('3-134981-158678512-IwAXqypKWlDJCF');

// API Call
...
```


### Get Player Embed
### This call does not require an access_token.

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
