<?php
/*
IMI Kurwica WSUS Proxy
Based on Dummy WSUS by whatever127

Modified by IMI Kurwica to work as proxy for Windows Update.
*/

function getBlobFromWU($uri, $blob) {
    $file = @fopen("blobs/$blob", 'w');
    if(!$file) return false;

    $url = "http://ds.download.windowsupdate.com/v11/3/windowsupdate/$uri";
    $req = curl_init($url);

    curl_setopt($req, CURLOPT_HEADER, 0);
    curl_setopt($req, CURLOPT_FILE, $file);
    curl_setopt($req, CURLOPT_ENCODING, '');
    curl_setopt($req, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($req, CURLOPT_HTTPHEADER, array(
        'User-Agent: Windows-Update-Agent',
    ));

    $out = curl_exec($req);
    if(curl_getinfo($req, CURLINFO_HTTP_CODE) != 200) $out = false;

    curl_close($req);
    fclose($file);

    if(!$out) unlink("blobs/$blob");
    return($out);
}

function streamFile($fileName) {
    $file = @fopen($fileName, 'r');
    if(!$file) {
        http_response_code(404);
        return false;
    }

    $stat = fstat($file);
    $name = basename($fileName);

    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename=\"$name\"");
    header('Content-Length: '.$stat['size']);
    header('Last-Modified: '.gmdate('D, j M Y H:i:s T', $stat['mtime']));

    if($_SERVER['REQUEST_METHOD'] == 'HEAD') {
        fclose($file);
        return true;
    }

    do {
        echo fread($file, 4096);
    } while(!feof($file));

    fclose($file);
    return true;
}

function sendSelfUpdate($uri) {
    if(strpos($uri, 'selfupdate/wuident.cab') !== false) {
        streamFile("wuident.cab");
        die();
    }

    $uri = preg_replace('/\?.*/', '', $uri);
    $blob = sha1(strtolower($uri));

    if(!file_exists('blobs')) {
        mkdir('blobs');
    }

    if(!file_exists("blobs/$blob")) {
        $blobDownloaded = getBlobFromWU($uri, $blob);
        if(!$blobDownloaded) {
            http_response_code(404);
            die();
        }
    }

    streamFile("blobs/$blob");
    die();
}

function getBaseUrl() {
    $baseUrl = '';
    if(isset($_SERVER['HTTPS'])) {
        $baseUrl .= 'https://';
    } else {
        $baseUrl .= 'http://';
    }

    $baseUrl .=  $_SERVER['HTTP_HOST'];
    return $baseUrl;
}

function printUsage() {
    $demoUri = getBaseUrl().explode('?', strtolower($_SERVER['REQUEST_URI']))[0];
    echo <<<EOD
<html>
    <head>
        <title>IMI Kurwica WSUS Proxy</title>
    </head>
    <body>
        <h1>IMI Kurwica WSUS Proxy</h1>

        <h2>Usage</h2>
        <p>To connect to this service you need to enter the following address to
        the WSUS configuration in the group policy:</p>
        <p><code>$demoUri?</code></p>

        <p>Please note that the <b>?</b> has to be appended at the end of the
        URL because of limitations this implementation of WSUS server.</p>
    </body>
</html>
EOD;
}

$uriExploded = explode('?', strtolower($_SERVER['REQUEST_URI']));
$uri = isset($uriExploded[1]) ? $uriExploded[1] : "" ;
$uri = str_replace('/selfupdate/', 'selfupdate/', $uri);

if(strpos($uri, 'selfupdate') !== false) {
    sendSelfUpdate($uri);
    die();
} elseif($uri == '' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    printUsage();
    die();
}

header('Content-Type: text/xml');

$reportingService = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <soap:Body>
        <ReportEventBatchResponse xmlns="http://www.microsoft.com/SoftwareDistribution">
            <ReportEventBatchResult>true</ReportEventBatchResult>
        </ReportEventBatchResponse>
    </soap:Body>
</soap:Envelope>
XML;

$internalError = <<<XML
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
    <s:Body>
        <s:Fault>
            <faultcode xmlns:a="http://schemas.microsoft.com/net/2005/12/windowscommunicationfoundation/dispatcher">a:InternalServiceFault</faultcode>
            <faultstring xml:lang="en-US">The server was unable to process the request due to an internal error.</faultstring>
        </s:Fault>
    </s:Body>
</s:Envelope>
XML;

if(isset($_SERVER['HTTP_SOAPACTION'])) {
    $action = $_SERVER['HTTP_SOAPACTION'];
} else {
    http_response_code(500);
    echo $internalError;
    die();
}

if($action == '"http://www.microsoft.com/SoftwareDistribution/ReportEventBatch"') {
    echo $reportingService;
    die();
}

$postData = file_get_contents("php://input");
$server = 'https://fe2.update.microsoft.com/v6';
$url = $server.$uri;
$req = curl_init($url);

curl_setopt($req, CURLOPT_HEADER, 0);
curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($req, CURLOPT_POST, 1);
curl_setopt($req, CURLOPT_ENCODING, '');
curl_setopt($req, CURLOPT_POSTFIELDS, $postData);
curl_setopt($req, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($req, CURLOPT_HTTPHEADER, array(
    'User-Agent: Windows-Update-Agent',
    'Content-Type: text/xml; charset=utf-8',
    "SOAPAction: $action",
));

$out = curl_exec($req);
curl_close($req);

if($action == '"http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService/SyncUpdates"') {
    $out = str_replace('MajorVersion="6" MinorVersion="1" SuiteMask="64" /&gt;', 'MajorVersion="6" MinorVersion="1" /&gt;', $out);
    $out = str_replace('/&gt;&lt;b.LicenseDword Value="Kernel-ProductInfo" Comparison="EqualTo" Data="65" /&gt;&lt;/And', '/&gt;&lt;/And', $out);
    $out = str_replace(" WHERE SMBIOSAssetTag = '7783-7084-3265-9085-8269-3286-77'", "", $out);
}

echo $out;
