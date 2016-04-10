<?php
/**
 * Labs64 NetLicensing - FastSpring integration
 *
 * @category   NetLicensing_Integration
 * @package    NetLicensing_FastSpring
 * @author     Labs64 NetLicensing <netlicensing@labs64.com>
 * @copyright  2016 Labs64 NetLicensing
 * @license    Apache-2.0
 * @version    1.0.0
 * @link       https://github.com/Labs64/NetLicensing-FastSpring
 */

//======================================================================
// NetLicensing Configuration
//======================================================================

// NetLicensing Connection
$nlicHost            = 'https://go.netlicensing.io';
$nlicApiUrl          = '/core/v2/rest/';
$licenseeResource    = 'licensee';
$licenseResource     = 'license';
$nlicUsername        = '';
$nlicPassword        = '';
$authorizationHeader = 'Authorization:' . 'Basic ' . base64_encode($nlicUsername . ":" . $nlicPassword);
$userAgent           = 'NetLicensing/FastSpring ' . PHP_VERSION . ' (http://netlicensing.io)' . '; ' . $_SERVER['HTTP_USER_AGENT'];

// NetLicensing Product Parameters
$productNumber         = '';
$licenseTemplateNumber = '';

// Headers regex
$patternHeader  = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
$patternVersion = '#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#';

//======================================================================
// Create Licensee
//======================================================================

// Licensee Number
$licenseeNumber = !empty($referrer) ? trim($referrer) : null;
if (!$licenseeNumber) {
    $licenseeParams = array(
        'productNumber' => $productNumber,
        'active' => 'true'
    );

    $licenseeCurl = curl_init();
    curl_setopt($licenseeCurl, CURLOPT_POST, true);
    curl_setopt($licenseeCurl, CURLOPT_URL, $nlicHost . $nlicApiUrl . $licenseeResource);
    curl_setopt($licenseeCurl, CURLOPT_POSTFIELDS, http_build_query($licenseeParams, '', '&'));
    curl_setopt($licenseeCurl, CURLOPT_HTTPHEADER, array(
        $authorizationHeader,
        'Accept:application/json'
    ));
    curl_setopt($licenseeCurl, CURLOPT_HEADER, true);
    curl_setopt($licenseeCurl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($licenseeCurl, CURLOPT_USERAGENT, $userAgent);
    $licenseeResponse = curl_exec($licenseeCurl);
    curl_close($licenseeCurl);

    // Extract headers from response
    preg_match_all($patternHeader, $licenseeResponse, $matches);
    $licenseeHeadersString = array_pop($matches[0]);
    $licenseeHeaders       = explode("\r\n", str_replace("\r\n\r\n", '', $licenseeHeadersString));

    // Remove headers from the response body
    $licenseeBody = str_replace($licenseeHeadersString, '', $licenseeResponse);

    // Extract the version and status from the first header
    $licenseeVersionAndStatus = array_shift($licenseeHeaders);
    preg_match($patternVersion, $licenseeVersionAndStatus, $matches);
    $licenseeStatusCode = $matches[2];
    $licenseeStatus     = $matches[2] . ' ' . $matches[3];

    try {
        if ($licenseeStatusCode != '200') {
            throw new Exception($licenseeStatus);
        }
    }
    catch (Exception $e) {
        print $e->getMessage();
    }

    $licenseeProperties = array();
    $jsonResponse       = json_decode($licenseeBody);

    if (!empty($jsonResponse->items)) {
        $item = is_array($jsonResponse->items->item) ? reset($jsonResponse->items->item) : $jsonResponse->items->item;
        foreach ($item->property as $property) {
            $property                              = (array) $property;
            $licenseeProperties[$property['name']] = $property['value'];
        }
    }

    $licenseeNumber = $licenseeProperties['number'];
}

//======================================================================
// Create License
//======================================================================

$licenseParams = array(
    'licenseeNumber' => $licenseeNumber,
    'licenseTemplateNumber' => $licenseTemplateNumber,
    'startDate' => 'now',
    'active' => 'true'
);

$licenseCurl = curl_init();
curl_setopt($licenseCurl, CURLOPT_POST, true);
curl_setopt($licenseCurl, CURLOPT_URL, $nlicHost . $nlicApiUrl . $licenseResource);
curl_setopt($licenseCurl, CURLOPT_POSTFIELDS, http_build_query($licenseParams, '', '&'));
curl_setopt($licenseCurl, CURLOPT_HTTPHEADER, array(
    $authorizationHeader,
    'Accept:application/json'
));
curl_setopt($licenseCurl, CURLOPT_HEADER, true);
curl_setopt($licenseCurl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($licenseCurl, CURLOPT_USERAGENT, $userAgent);
$licenseResponse = curl_exec($licenseCurl);
curl_close($licenseCurl);

// Extract headers from response
preg_match_all($patternHeader, $licenseResponse, $matches);
$licenseHeadersString = array_pop($matches[0]);
$licenseHeaders       = explode("\r\n", str_replace("\r\n\r\n", '', $licenseHeadersString));

// Remove headers from the response body
$licenseBody = str_replace($licenseHeadersString, '', $licenseResponse);

// Extract the version and status from the first header
$licenseVersionAndStatus = array_shift($licenseHeaders);
preg_match($patternVersion, $licenseVersionAndStatus, $matches);
$licenseStatusCode = $matches[2];
$licenseStatus     = $matches[2] . ' ' . $matches[3];

try {
    if ($licenseStatusCode != '200') {
        throw new Exception($licenseStatus);
    }
}
catch (Exception $e) {
    print $e->getMessage();
}

print $licenseeNumber;
