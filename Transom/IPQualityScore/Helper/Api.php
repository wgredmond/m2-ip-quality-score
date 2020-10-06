<?php
/**
 * Transom Group Inc.
 *
 *
 * @category    Transom
 * @package     Transom_Group
 * @copyright   Copyright (c) Transom Group. All rights reserved. (https://transom-group.com/)
 */

namespace Transom\IPQualityScore\Helper;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Psr\Log\LoggerInterface;
use Transom\IPQualityScore\Model\ConfigSettings;


class Api extends \Magento\Framework\App\Helper\AbstractHelper
{


    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Transom\IPQualityScore\Model\ConfigSettings
     */
    protected $config;

    /**
     * Api constructor.
     *
     * @param LoggerInterface $logger
     * @param ConfigSettings $config
     */
    public function __construct(LoggerInterface $logger,
                                ConfigSettings $config,
                                RemoteAddress $remoteAddress)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->remoteAddress = $remoteAddress;
    }

    public function sendLogin()
    {

        // only process order if this service is enabled
        if (!$this->config->isApiActive()) {
            return $this;
        }

        $this->logger->info('##### In Transom IPQualityScore ##### Api->sendLogin()');

        // get credentials, call IP quality score
        if ($this->config->isApiAccessKeysInAdmin()) {
            // TODO: call ip quality score api
            $apiKey = $this->config->getApiKey();
            $endpointUrl = $this->config->getApiEndPoint();
        } else {
            // TODO get creds from env
            $this->logger->info('TODO hook up dont manage credentials in admin option.');
            return $this;
        }

        $ipAddress = $this->remoteAddress->getRemoteAddress();
        $userAgent = $_SERVER ['HTTP_USER_AGENT'];
        $userLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        // Set the strictness for this query. (0 (least strict) - 3 (most strict))
        $strictness = 1;

        // You may want to allow public access points like coffee shops, schools, corporations, etc...
        $allowPublicAccessPoints = 'true';

        // Reduce scoring penalties for mixed quality IP addresses shared by good and bad users.
        $lighterPenalties = 'false';

        $this->logger->info('##### ipAddress = ' . $ipAddress);
        $this->logger->info('##### userAgent = ' . $userAgent);
        $this->logger->info('##### userLanguage = ' . $userLanguage);
        $this->logger->info('##### endpointUrl = ' . $endpointUrl);
        $this->logger->info('##### apiKey = ' . $apiKey);

        $parameters = array(
            'user_agent' => $userAgent,
            'user_language' => $userLanguage,
            'strictness' => $strictness,
            'allow_public_access_points' => $allowPublicAccessPoints,
            'lighter_penalties' => $lighterPenalties
        );

        $formattedParameters = http_build_query($parameters);
        $url = sprintf(
            $endpointUrl . 'json/ip/%s/%s?%s',
            $apiKey,
            $ipAddress,
            $formattedParameters
        );
        $this->logger->info('##### url = ' . $url);

        // Fetch The Result
        $timeout = 5;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);

        $json = curl_exec($curl);
        curl_close($curl);

        // Decode the result into an array.
        $result = json_decode($json, true);

        if (isset($result['message'])) {
            $this->logger->info('##### message = ' . $result['message']);
        }

        foreach ($result as $key => $value) {
            if (getType($value) === 'object') {
                $this->logger->info('##### object type = ' . get_class($value));
            } else {
                $this->logger->info('##### data[' . $key . '] (type=' . getType($value) . ') = ' . $value);
            }
        }

        // Check to see if our query was successful.
        if (isset($result['success']) && $result['success'] === true) {
            if (isset($result['fraud_score']) && $result['fraud_score'] === true) {
                $score = $result['fraud_score'];
                $this->logger->info('##### ##### score = ' . $score);
            }
            // country_code
            // region
            // city
            // ISP
            // ASN
            // operating_system
            // browser
            // organization
            if (isset($result['latitude']) && $result['latitude'] === true) {
                $latitude = $result['latitude'];
            }
            if (isset($result['longitude']) && $result['longitude'] === true) {
                $longitude = $result['longitude'];
            }
            // is_crawler
            // timezone
            if (isset($result['mobile']) && $result['mobile'] === true) {
                $mobile = $result['mobile'];
            }
            if (isset($result['host']) && $result['host'] === true) {
                $host = $result['host'];
            }
            if (isset($result['proxy']) && $result['proxy'] === true) {
                $proxy = $result['proxy'];
            }
            if (isset($result['vpn']) && $result['vpn'] === true) {
                $vpn = $result['vpn'];
            }
            // tor
            if (isset($result['active_vpn']) && $result['active_vpn'] === true) {
                $activeVpn = $result['active_vpn'];
            }
            // active_tor
            // recent_abuse
            // bot_status
            // request_id
        }
    }
}