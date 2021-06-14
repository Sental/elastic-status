<?php

namespace ReesSolutions\ElasticStatus\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\HTTP\Client\Curl;

class ElasticStatusCommand extends Command
{
    const DEFAULT_URL = "localhost:9200/_cluster/health?pretty&pretty";

    const PARAMS = "/_cluster/health?pretty&pretty";

    const SEARCH_SYSTEM_PATH = 'catalog/search/engine';

    const DEFAULT_HOSTNAME_PATH = 'catalog/search/elasticsearch7_server_hostname';
    const DEFAULT_PORT_PATH = 'catalog/search/elasticsearch7_server_port';
    const DEFAULT_AUTH_PATH = 'catalog/search/elasticsearch7_enable_auth';
    const DEFAULT_USERNAME_PATH = 'catalog/search/elasticsearch7_username';
    const DEFAULT_PASSWORD_PATH = 'catalog/search/elasticsearch7_password';

    const MODULE_HOSTNAME_PATH = 'rses/search/elasticsearch7_server_hostname';
    const MODULE_PORT_PATH = 'rses/search/elasticsearch7_server_port';
    const MODULE_AUTH_PATH = 'rses/search/elasticsearch7_enable_auth';
    const MODULE_USERNAME_PATH = 'rses/search/elasticsearch7_username';
    const MODULE_PASSWORD_PATH = 'rses/search/elasticsearch7_password';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Curl
     */
    private $curlClient;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Curl $curlClient
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->curlClient = $curlClient;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('rs:elastic:check')
            ->setDescription('Checks the health status of elastic search');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputStyle = new OutputFormatterStyle('cyan', 'default', ['bold']);
        $output->getFormatter()->setStyle('rses', $outputStyle);
        $url = $this->getUrl(); //self::DEFAULT_URL;
        $default = (bool) ($this->scopeConfig->getValue(self::SEARCH_SYSTEM_PATH) === 'elasticsearch7');
        try {
            $status = $this->doCurlRequest($url, $default);
        } catch (\Exception $e) {
            $status = 'Curl Exception or No response from elastic search, action recommended.';
        }
        $status = !empty($status) ? $status : 'No response from elastic search, action recommended';
        if (stripos($status, 'error') !== false) {
            $status = 'An error may have occured, please check the status.' . "\n" . $status;
        }
        $output->writeln('<info>Elastic Search Health Status:<info>');
        $output->writeln('<rses>' . $status . '</>');
    }

    private function doCurlRequest($url, $default = true)
    {

        // create curl resource
        $ch = $this->curlClient;

        if ($default) {
            $auth = (bool) $this->scopeConfig->getValue(self::DEFAULT_AUTH_PATH);
            if ($auth) {
                $user = $this->scopeConfig->getValue(self::DEFAULT_USERNAME_PATH);
                $pass = $this->scopeConfig->getValue(self::DEFAULT_PASSWORD_PATH);
                // set auth
                $ch->setCredentials($user, $pass);
            }
        } else {
            $auth = (bool) $this->scopeConfig->getValue(self::MODULE_AUTH_PATH);
            if ($auth) {
                $user = $this->scopeConfig->getValue(self::MODULE_USERNAME_PATH);
                $pass = $this->scopeConfig->getValue(self::MODULE_PASSWORD_PATH);
                // set auth
                $ch->setCredentials($user, $pass);
            }
        }

        // set url and make request
        $ch->get($url);
        // $output contains the output string
        $output = $ch->getBody();
        return $output;
    }

    private function getUrl()
    {
        $search = $this->scopeConfig->getValue(self::SEARCH_SYSTEM_PATH);
        if ($search === 'elasticsearch7') {
            $hostname = $this->scopeConfig->getValue(self::DEFAULT_HOSTNAME_PATH);
            $port = $this->scopeConfig->getValue(self::DEFAULT_PORT_PATH);
        } else {
            $hostname = $this->scopeConfig->getValue(self::MODULE_HOSTNAME_PATH);
            $port = $this->scopeConfig->getValue(self::MODULE_PORT_PATH);
        }
        $params = self::PARAMS;
        $url = $hostname.':'.$port.$params;
        return $url;
    }
}
