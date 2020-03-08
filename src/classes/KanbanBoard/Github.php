<?php

/**
 * GithubClient
 */
class GithubClient
{
    private $client;
    private $milestone_api;
    private $account;
    
    /**
     * __construct
     *
     * @param  mixed $token
     * @param  mixed $account
     * @return void
     */
    public function __construct(string $token,string $account)
    {
        require '../../vendor/autoload.php';
        $this->account = $account;
        $this->client = new \Github\Client(new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache')));
        $this->client->authenticate($token, \Github\Client::AUTH_HTTP_TOKEN);

        $this->milestone_api = $this->client->api('issues')->milestones();
    }
    
    /**
     * milestones
     *
     * @param  mixed $repository
     * @return array
     */
    public function milestones(string $repository) : array
    {
        return $this->milestone_api->all($this->account, $repository);
    }
    
    /**
     * issues
     *
     * @param  mixed $repository
     * @param  mixed $milestone_id
     * @return array
     */
    public function issues(string $repository, int $milestone_id) : array
    {
        $issue_parameters = array('milestone' => $milestone_id, 'state' => 'all');
        return $this->client->api('issue')->all($this->account, $repository, $issue_parameters);
    }
}
