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

$verbose               = false;

// NetLicensing Product Parameters
$productType           = 'one-time|subscription'; # FastSpring product type; e.g. one-time or subscription
$productNumber         = '';
$licenseTemplateNumber = '';

// NetLicensing Connection
$nlicUsername          = '';
$nlicPassword          = '';

$nlicHost              = 'https://go.netlicensing.io';
$nlicApiUrl            = '/core/v2/rest/';
$licenseeResource      = 'licensee';
$licenseResource       = 'license';

$authorizationHeader   = 'Authorization:' . 'Basic ' . base64_encode($nlicUsername . ":" . $nlicPassword);
$userAgent             = 'NetLicensing/FastSpring ' . PHP_VERSION . ' (http://netlicensing.io)' . '; ' . $_SERVER['HTTP_USER_AGENT'];

// Headers regex
$patternHeader         = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
$patternVersion        = '#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#';

//======================================================================
// Create Licensee
//======================================================================

// Licensee Number
$licenseeNumber = !empty($referrer) ? trim($referrer) : null;

if (!$licenseeNumber) {

    // Prepare FastSpring variables
    $name = !empty($name) ? trim($name) : null;
    $email = !empty($email) ? trim($email) : null;

    $licenseeParams = array(
        'active' => 'true',
        'productNumber' => $productNumber,
        // 'name' => $name,
        'email' => $email
    );

    $licenseeCurl = curl_init();
    curl_setopt($licenseeCurl, CURLOPT_POST, true);
    curl_setopt($licenseeCurl, CURLOPT_URL, $nlicHost . $nlicApiUrl . $licenseeResource);
    curl_setopt($licenseeCurl, CURLOPT_POSTFIELDS, http_build_query($licenseeParams, '', '&'));
    curl_setopt($licenseeCurl, CURLOPT_HTTPHEADER, array(
        $authorizationHeader,
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
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

    if ($licenseeStatusCode != '200' && $verbose) {
        echo "LicenseeService error: " . $licenseeStatus . PHP_EOL;
        echo "LicenseeService payload: " . $licenseeBody . PHP_EOL;
        // echo "LicenseeService response: " . $licenseeResponse . PHP_EOL;
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

if ($licenseeNumber) {

    // Prepare FastSpring variables
    $product = !empty($product) ? trim($product) : null;
    $reference = !empty($reference) ? trim($reference) : null;
    $subscriptionReference = !empty($subscriptionReference) ? trim($subscriptionReference) : null;

    $licenseParams = array(
        'active' => 'true',
        'licenseeNumber' => $licenseeNumber,
        'licenseTemplateNumber' => $licenseTemplateNumber,
        'FastSpringProduct' => $product,
    );
    switch ($productType) {
        case "one-time":
            $licenseParams['FastSpringReference'] = $reference;
            break;
        case "subscription":
            $licenseParams['startDate'] = 'now';
            $licenseParams['FastSpringSubscriptionReference'] = $subscriptionReference;
            break;
    }

    $licenseCurl = curl_init();
    curl_setopt($licenseCurl, CURLOPT_POST, true);
    curl_setopt($licenseCurl, CURLOPT_URL, $nlicHost . $nlicApiUrl . $licenseResource);
    curl_setopt($licenseCurl, CURLOPT_POSTFIELDS, http_build_query($licenseParams, '', '&'));
    curl_setopt($licenseCurl, CURLOPT_HTTPHEADER, array(
        $authorizationHeader,
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
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

    if ($licenseStatusCode != '200' && $verbose) {
        echo "LicenseService error: " . $licenseStatus . PHP_EOL;
        echo "LicenseService payload: " . $licenseBody . PHP_EOL;
        // echo "LicenseService response: " . $licenseResponse . PHP_EOL;
    }
}

print $licenseeNumber;
