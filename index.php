<html>
<head>
    <title>
        Test FB images
    </title>

</head>
<body>
<?php
include_once 'FacebookAlbums.php';

$fb = new FacebokAlbums();
Facebook::$CURL_OPTS[CURLOPT_CAINFO] = getcwd().'/fb_ca_chain_bundle.crt';

$fb->getAllLatest(30);
?>
</body>
</html>
