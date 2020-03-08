<?php

namespace KanbanBoard;

use Github\Client;
use GithubClient;
use vierbergenlars\SemVer\version;

use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\SemVerException;
use \Michelf\Markdown;
use phpDocumentor\Reflection\Types\Array_;

/**
 * Application
 */
class Application
{
	private $github;
	private $repositories;
	private $paused_labels;
	
	/**
	 * __construct
	 *
	 * @param  mixed $github
	 * @param  mixed $repositories
	 * @param  mixed $paused_labels
	 * @return void
	 */
	public function __construct(GithubClient $github, Array $repositories, Array $paused_labels = array())
	{
		$this->github = $github;
		$this->repositories = $repositories;
		$this->paused_labels = $paused_labels;
	}
	
	/**
	 * board
	 *
	 * @return Array
	 */
	public function board() : Array
	{
		$ms = array();
		foreach ($this->repositories as $repository) {
			foreach ($this->github->milestones($repository) as $data) {
				$ms[$data['title']] = $data;
				$ms[$data['title']]['repository'] = $repository;
			}
		}
		ksort($ms);
		foreach ($ms as $name => $data) {
			$issues = $this->issues($data['repository'], $data['number']);
			$percent = self::_percent($data['closed_issues'], $data['open_issues']);
			if ($percent) {
				$milestones[] = array(
					'milestone' => $name,
					'url' => $data['html_url'],
					'progress' => $percent,
					'queued' => $issues['queued'],
					'active' => $issues['active'],
					'completed' => $issues['completed']
				);
			}
		}
		return $milestones;
	}
	
	/**
	 * issues
	 *
	 * @param  mixed $repository
	 * @param  mixed $milestone_id
	 * @return Array
	 */
	private function issues(string $repository,string $milestone_id) : Array
	{
		$issues = array(
			'active' => array(),
			'queued' => array(),
			'completed' => array()
		);


		foreach ($this->github->issues($repository, $milestone_id) as $issue) {
			
				array_push($issues[$this->_state($issue)], array(
					'id' => $issue['id'], 'number' => $issue['number'],
					'title' => $issue['title'],
					'body' => Markdown::defaultTransform($issue['body']),
					'url' => $issue['html_url'],
					'assignee' => (is_array($issue) && array_key_exists('assignee', $issue) && !empty($issue['assignee'])) ? $issue['assignee']['avatar_url'] . '?s=16' : NULL,
					'paused' => self::labels_match($issue, $this->paused_labels),
					'progress' => self::_percent(
						substr_count(strtolower($issue['body']), '[x]'),
						substr_count(strtolower($issue['body']), '[ ]')
					),
					'closed' => $issue['closed_at']
				));
		}

		if (is_array($issues['active']))
			usort($issues['active'], function ($a, $b) {
				return count($a['paused']) - count($b['paused']) === 0 ? strcmp($a['title'], $b['title']) : count($a['paused']) - count($b['paused']);
			});

		return $issues;
	}
	
	/**
	 * _state
	 *
	 * @param  mixed $issue
	 * @return String
	 */
	private static function _state(array $issue) : String
	{
		if ($issue['state'] === 'closed')
			return 'completed';
		else if (Utilities::hasValue($issue, 'assignee') && count($issue['assignee']) > 0)
			return 'active';
		else
			return 'queued';
	}
	
	/**
	 * labels_match
	 *
	 * @param  mixed $issue
	 * @param  mixed $needles
	 * @return Array
	 */
	private static function labels_match(array $issue, array $needles) : Array
	{
		if (Utilities::hasValue($issue, 'labels')) {
			foreach ($issue['labels'] as $label) {
				if (in_array($label['name'], $needles)) {
					return array($label['name']);
				}
			}
		}
		return array();
	}
	
	/**
	 * _percent
	 *
	 * @param  mixed $complete
	 * @param  mixed $remaining
	 * @return Array
	 */
	private static function _percent(int $complete, int $remaining) : Array
	{
		$total = $complete + $remaining;
		if ($total > 0) {
			$percent = ($complete or $remaining) ? round($complete / $total * 100) : 0;
			return array(
				'total' => $total,
				'complete' => $complete,
				'remaining' => $remaining,
				'percent' => $percent
			);
		}
		return array();
	}
}
