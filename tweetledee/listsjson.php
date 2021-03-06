<?php

/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   listsjson.php -- User list tweets formatted as JSON
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/

/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user list feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/listsjson.php?list=<list-slug>
==> User List JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/listsjson.php?list=<list-slug>&c=100
    - 'list' - the list name for the specified user (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/listsjson.php?list=theblacklist
    - 'user' - specify the Twitter user whose favorites you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/listsjson.php?list=<list-slug>&user=cooluser
    - 'xrt' - exclude retweets in the returned data (set to 1 to exclude, default = include retweets)
    - 'cache_interval' - specify the duration of the cache interval in seconds (default = 90sec)
--------------------------------------------------------------------------------------------------*/
/*******************************************************************
 *  Includes
 ********************************************************************/
require 'tldlib/debug.php';
// Matt Harris' Twitter OAuth library
require 'tldlib/tmhOAuth.php';
require 'tldlib/tmhUtilities.php';

// include user keys
require 'tldlib/keys/tweetledee_keys.php';

// include Geoff Smith's utility functions
require 'tldlib/tldUtilities.php';

// include Christian Varga's twitter cache
require 'tldlib/tldCache.php';

require 'tldlib/parametersProcessing.php';

/*******************************************************************
 *  Defaults
 ********************************************************************/

$parameters = load_parameters(["c", "user", "exclude_retweets", "list", "cache_interval"]);
extract($parameters);
if (!isset($parameters['list'])) {
    die("Error: missing user list name in your request.  Please use the 'list' parameter in your request.");
}
$include_retweets = !$exclude_retweets;
/*******************************************************************
 *  OAuth
 ********************************************************************/

$tldCache = new tldCache([
    'consumer_key'        => $my_consumer_key,
    'consumer_secret'     => $my_consumer_secret,
    'user_token'          => $my_access_token,
    'user_secret'         => $my_access_token_secret,
    'curl_ssl_verifypeer' => false
], $cache_interval);

// request the user information
$data = $tldCache->auth_request();

// Parse information from response
if (!isset($parameters['screen_name'])) {
    $screen_name = $data['screen_name'];
}
$fullName = $data['name'];
$twitterAvatarUrl = $data['profile_image_url_https'];

/*******************************************************************
 *  Request
 ********************************************************************/

$userListObj = $tldCache->user_request([
    'url' => '1.1/lists/statuses',
    'params' => [
        'include_entities' => true,
        'count' => $count,
        'owner_screen_name' => $screen_name,
        'slug' => $list_name,
        'include_rts' => $include_retweets,
    ]
]);

header('Content-Type: application/json');
echo json_encode($userListObj);
