<?php

/******* user configuration begin ******/
# NetLicensing authorization
$userName = '';
$password = '';

# NetLicensing product parameters
$productNumber = '';
$licenseTemplateNumber = '';
/******* user configuration end ********/

/* NetLicensing connection params */
$nlHost = 'https://go.netlicensing.io';
$nlApiUrlPart = '/core/v2/rest/';
$authorizationHeader = 'Authorization:' . 'Basic ' . base64_encode($userName . ":" . $password);

# Headers regex
$pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';

# Licensee Number
$licenseeNumber = !empty($referrer) ? trim($referrer) : null;

if(!$licenseeNumber){
    /* create licensee */
    $licenseeResource = 'licensee';

    $licenseeParams = array(
        'productNumber' => $productNumber,
        'active' => 'true'
    );

    $licenseeCurl = curl_init();
    curl_setopt($licenseeCurl, CURLOPT_POST, true);
    curl_setopt($licenseeCurl, CURLOPT_URL, $nlHost . $nlApiUrlPart . $licenseeResource);
    curl_setopt($licenseeCurl, CURLOPT_POSTFIELDS, http_build_query($licenseeParams, '', '&'));
    curl_setopt($licenseeCurl, CURLOPT_HTTPHEADER, array($authorizationHeader, 'Accept:application/json'));
    curl_setopt($licenseeCurl, CURLOPT_HEADER, true);
    curl_setopt($licenseeCurl, CURLOPT_RETURNTRANSFER, true);
    $licenseeResponse = curl_exec($licenseeCurl);
    curl_close($licenseeCurl);

# Extract headers from response
    preg_match_all($pattern, $licenseeResponse, $matches);
    $licenseeHeadersString = array_pop($matches[0]);
    $licenseeHeaders = explode("\r\n", str_replace("\r\n\r\n", '', $licenseeHeadersString));

# Remove headers from the response body
    $licenseeBody = str_replace($licenseeHeadersString, '', $licenseeResponse);

# Extract the version and status from the first header
    $licenseeVersionAndStatus = array_shift($licenseeHeaders);
    preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $licenseeVersionAndStatus, $matches);
    $licenseeStatusCode = $matches[2];
    $licenseeStatus = $matches[2] . ' ' . $matches[3];

    try {
        if ($licenseeStatusCode != '200') {
            throw new Exception($licenseeStatus);
        }
    } catch (Exception $e) {
        print $e->getMessage();
    }

    $licenseeProperties = array();
    $jsonResponse = json_decode($licenseeBody);

    if (!empty($jsonResponse->items)) {
        $item = is_array($jsonResponse->items->item) ? reset($jsonResponse->items->item) : $jsonResponse->items->item;
        foreach ($item->property as $property) {
            $property = (array)$property;
            $licenseeProperties[$property['name']] = $property['value'];
        }
    }
    /* end creating licensee */

    $licenseeNumber = $licenseeProperties['number'];
}

/* create license */
$licenseResource = 'license';

$licenseParams = array(
    'licenseeNumber' => $licenseeNumber,
    'licenseTemplateNumber' => $licenseTemplateNumber,
    'startDate' => 'now',
    'active' => 'true'
);

$licenseCurl = curl_init();
curl_setopt($licenseCurl, CURLOPT_POST, true);
curl_setopt($licenseCurl, CURLOPT_URL, $nlHost . $nlApiUrlPart . $licenseResource);
curl_setopt($licenseCurl, CURLOPT_POSTFIELDS, http_build_query($licenseParams, '', '&'));
curl_setopt($licenseCurl, CURLOPT_HTTPHEADER, array($authorizationHeader, 'Accept:application/json'));
curl_setopt($licenseCurl, CURLOPT_HEADER, true);
curl_setopt($licenseCurl, CURLOPT_RETURNTRANSFER, true);
$licenseResponse = curl_exec($licenseCurl);
curl_close($licenseCurl);

# Extract headers from response
preg_match_all($pattern, $licenseResponse, $matches);
$licenseHeadersString = array_pop($matches[0]);
$licenseHeaders = explode("\r\n", str_replace("\r\n\r\n", '', $licenseHeadersString));

# Remove headers from the response body
$licenseBody = str_replace($licenseHeadersString, '', $licenseResponse);

# Extract the version and status from the first header
$licenseVersionAndStatus = array_shift($licenseHeaders);
preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $licenseVersionAndStatus, $matches);
$licenseStatusCode = $matches[2];
$licenseStatus = $matches[2] . ' ' . $matches[3];

try {
    if ($licenseStatusCode != '200') {
        throw new Exception($licenseStatus);
    }
} catch (Exception $e) {
    print $e->getMessage();
}

/* end creating license */

print $licenseeNumber;