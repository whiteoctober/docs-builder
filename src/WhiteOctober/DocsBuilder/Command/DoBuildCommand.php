<?php

namespace WhiteOctober\DocsBuilder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Github\ResultPager;
use WhiteOctober\DocsBuilder\Configuration as Config;

class DoBuildCommand extends Command
{
    /**
     * @var OutputInterface
     */
    protected $output;

    protected $config = array();

    protected $docsFile = "";

    /**
     * @var \Github\Client;
     */
    protected $client;

    protected function configure()
    {
        $this->
            setName("wo:docs-build")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $this->processAppConfig();

        $this->client = $this->getGithubClient();

        $docs = $this->findDocsFiles();
        if (!count($docs)) {
            $output->writeln("<info>No {$this->docsFiles} files found, exiting");

            return;
        }

        $this->processDocsFiles($docs);
    }

    protected function processAppConfig()
    {
        $configPath = $this->getApplication()->getCalledFrom() . "/config.yml";
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Config file not found, create config.yml");
        }

        $configContents = Yaml::parse(file_get_contents($configPath));

        $config = new Config\ConfigFileConfiguration();
        $processor = new Processor();

        try {
            $processedConfig = $processor->processConfiguration($config, $configContents);
        }
        catch (\Exception $e) {
            $this->output->writeln("<error>Your config.yml file appears to be incorrect</error>");
            $this->output->writeln("<error>{$e->getMessage()}</error>");

            exit;
        }

        $this->config = $processedConfig;
        $this->docsFile = $processedConfig["docs_file"];
    }

    protected function getGithubClient()
    {
        $client = new \Github\Client();

        // Authenticate
        // TODO add username/password configuration here too
        $gh = $this->config["github"];
        if (isset($gh["api_token"]) && strlen($gh["api_token"])) {
            $client->authenticate($gh["api_token"], "", \Github\Client::AUTH_URL_TOKEN);

            return $client;
        }

        $this->output->writeln("<error>No credentials available for authentication, cannot continue</error>");

        exit;
    }

    protected function findDocsFiles()
    {
        $docs = array();
        $pager = new ResultPager($this->client);

        if (strlen($this->config["github"]["organisation"])) {
            $user = $this->config["github"]["organisation"];
            $repos = $pager->fetchAll($this->client->api("organization"), "repositories", $this->config["github"]["organisation"]);
        }
        else {
            $user = $this->config["github"]["username"];
            $repos = $pager->fetchAll($this->client->api("user"), "repositories", $this->config["github"]["username"]);
        }
        foreach ($repos as $repoArr) {
            $repoName = $repoArr["name"];
            $this->output->writeln("<info>" . $repoName . "</info>");

            // Check for a {$this->docsFile} file
            try {
                $file = $this->client->api("repo")->contents()->show($user, $repoName, $this->docsFile);
                $this->output->writeln("<comment>{$this->docsFile} present</comment>");

                $docs[$repoName] = $file["content"];
                break;
            }
            catch (\Github\Exception\RuntimeException $e) {
                $this->output->writeln("<error>Not found</error>");
            }
        }

        return $docs;
    }

    protected function processDocsFiles($docs)
    {
        $config = new Config\DocFileConfiguration();
        $processor = new Processor();

        foreach ($docs as $repoName => $content) {
            $this->output->writeln("<info>Processing {$repoName}");
            $yaml = base64_decode($content);

            try {
                $content = Yaml::parse($yaml);
            }
            catch (ParseException $e) {
                $this->output->writeln("<error>Could not parse {$this->docsFile} for {$repoName}");
                continue;
            }

            try {
                $processedConfig = $processor->processConfiguration($config, $content);
            }
            catch (\Exception $e) {
                $this->output->writeln("<error>{$this->docsFile} for {$repoName} has invalid configuration: " . $e->getMessage());
                continue;
            }

            $this->output->writeln("<comment>{$this->docsFile} valid");

            // TODO Process each option
        }
    }
}

