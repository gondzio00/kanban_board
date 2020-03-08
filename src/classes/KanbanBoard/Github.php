<?php

class GithubClient
{
    private $client;
    private $milestone_api;
    private $account;

    public function __construct($token, $account)
    {
        require '../../vendor/autoload.php';
        $this->account = $account;
        $this->client= new \Github\Client(new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache')));
        $this->client->authenticate($token, \Github\Client::AUTH_HTTP_TOKEN);
        $this->milestone_api = $this->client->api('issues')->milestones();
    }

    public function milestones($repository)
    {
        if(Utilities::hasValue($this->milestone_api, $repository))
            return $this->milestone_api->all($this->account, $repository);
        else
            throw new Exception("Repository : ".$repository." doesn't exist");
    }

    public function issues($repository, $milestone_id)
    {
        $issue_parameters = array('milestone' => $milestone_id, 'state' => 'all');
        return $this->client->api('issue')->all($this->account, $repository, $issue_parameters);
    }
}