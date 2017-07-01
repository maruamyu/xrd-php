<?php

include __DIR__ . '/../vendor/autoload.php';

use Maruamyu\Core\Xrd\WebFingerClient;

$resourceUri = 'acct:mirai_iro@mstdn.jp';

$client = new WebFingerClient();
$webFinger = $client->get($resourceUri);
echo str_replace('><', ">\n<", $webFinger->toXml()) . "\n";
