<?php

/**
 * GitHub.php
 *
 * An interface to GitHubs api
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace LibreNMS\Util;

use Exception;
use Requests;
use Requests_Response;

class GitHub
{
    protected $tag;
    protected $from;
    protected $token;
    protected $file;
    protected $pr;
    protected $stop = false;
    protected $pull_requests = [];
    protected $changelog = [
        'feature' => [],
        'enhancement' => [],
        'breaking change' => [],
        'security' => [],
        'device' => [],
        'webui' => [],
        'authentication' => [],
        'graphs' => [],
        'snmp traps' => [],
        'applications' => [],
        'api' => [],
        'alerting' => [],
        'billing' => [],
        'discovery' => [],
        'polling' => [],
        'rancid' => [],
        'oxidized' => [],
        'bug' => [],
        'refactor' => [],
        'cleanup' => [],
        'documentation' => [],
        'translation' => [],
        'tests' => [],
        'misc' => [],
        'dependencies' => [],
    ];
    protected $changelog_users = [];
    protected $changelog_mergers = [];
    protected $profile_links = [];

    protected $markdown;
    protected $github = 'https://api.github.com/repos/librenms/librenms';
    protected $graphql = 'https://api.github.com/graphql';

    public function __construct($tag, $from, $file, $token = null, $pr = null)
    {
        $this->tag = $tag;
        $this->from = $from;
        $this->file = $file;
        $this->pr = $pr;
        if (! is_null($token) || getenv('GH_TOKEN')) {
            $this->token = $token ?: getenv('GH_TOKEN');
        }
    }

    /**
     * Return the GitHub Authorization header for the API call
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (! is_null($this->token)) {
            $headers['Authorization'] = "token {$this->token}";
        }

        return $headers;
    }

    /**
     * Get the release information for a specific tag
     *
     * @param string $tag
     * @return mixed
     */
    public function getRelease($tag)
    {
        $release = Requests::get($this->github . "/releases/tags/$tag", $this->getHeaders());

        return json_decode($release->body, true);
    }

    /**
     * Get a single pull request information
     */
    public function getPullRequest()
    {
        $pull_request = Requests::get($this->github . "/pulls/{$this->pr}", $this->getHeaders());
        $this->pr = json_decode($pull_request->body, true);
    }

    /**
     * Get all closed pull requests up to a certain date
     *
     * @param string $date
     * @param string $after
     */
    public function getPullRequests($date, $after = null)
    {
        if ($after) {
            $after = ", after: \"$after\"";
        }

        $query = <<<GRAPHQL
{
  search(query: "repo:librenms/librenms is:pr is:merged merged:>=$date", type: ISSUE, first: 100$after) {
    edges {
      node {
        ... on PullRequest {
          number
          title
          url
          mergedAt
          author {
            login
            url
          }
          mergedBy {
            login
            url
          }
          labels(first: 20) {
            nodes {
              name
            }
          }
          reviews(first: 100) {
            nodes {
              author {
                login
                url
              }
            }
          }
        }
      }
    }
    pageInfo {
      endCursor
      hasNextPage
    }
  }
}
GRAPHQL;

        $data = json_encode(['query' => $query]);
        $prs = Requests::post($this->graphql, $this->getHeaders(), $data);
        $prs = json_decode($prs->body, true);
        if (! isset($prs['data'])) {
            var_dump($prs);
        }

        foreach ($prs['data']['search']['edges'] as $edge) {
            $pr = $edge['node'];
            $pr['labels'] = $this->parseLabels($pr['labels']['nodes']);
            $this->pull_requests[] = $pr;
        }

        // recurse through the pages
        if ($prs['data']['search']['pageInfo']['hasNextPage']) {
            $this->getPullRequests($date, $prs['data']['search']['pageInfo']['endCursor']);
        }
    }

    /**
     * Parse labels response into standardized names and remove emoji
     *
     * @param array $labels
     * @return array
     */
    private function parseLabels($labels)
    {
        return array_map(function ($label) {
            $name = preg_replace('/ :[\S]+:/', '', strtolower($label['name']));

            return str_replace('-', ' ', $name);
        }, $labels);
    }

    /**
     * Build the data for the change log.
     */
    public function buildChangeLog()
    {
        $valid_labels = array_keys($this->changelog);

        foreach ($this->pull_requests as $k => $pr) {
            // check valid labels in order
            $category = 'misc';
            foreach ($valid_labels as $valid_label) {
                if (in_array($valid_label, $pr['labels'])) {
                    $category = $valid_label;
                    break; // only put in the first found label
                }
            }

            // If the Gihub profile doesnt exist anymore, the author is null
            if (empty($pr['author'])) {
                $pr['author'] = ['login' => 'ghost', 'url' => 'https://github.com/ghost'];
            }

            // only add the changelog if it isn't set to ignore
            if (! in_array('ignore changelog', $pr['labels'])) {
                $title = addcslashes(ucfirst(trim(preg_replace('/^[\S]+: /', '', $pr['title']))), '<>');
                $this->changelog[$category][] = "$title ([#{$pr['number']}]({$pr['url']})) - [{$pr['author']['login']}]({$pr['author']['url']})" . PHP_EOL;
            }

            $this->recordUserInfo($pr['author']);
            // Let's not count self-merges
            if ($pr['author']['login'] != $pr['mergedBy']['login']) {
                $this->recordUserInfo($pr['mergedBy'], 'changelog_mergers');
            }

            $ignore = [$pr['author']['login'], $pr['mergedBy']['login']];
            foreach (array_unique($pr['reviews']['nodes'], SORT_REGULAR) as $reviewer) {
                if (! in_array($reviewer['author']['login'], $ignore)) {
                    $this->recordUserInfo($reviewer['author'], 'changelog_mergers');
                }
            }
        }
    }

    /**
     * Record user info and count into the specified array (default changelog_users)
     * Record profile links too.
     *
     * @param array $user
     * @param string $type
     */
    private function recordUserInfo($user, $type = 'changelog_users')
    {
        $user_count = &$this->$type;

        $user_count[$user['login']] = isset($user_count[$user['login']])
            ? $user_count[$user['login']] + 1
            : 1;

        if (! isset($this->profile_links[$user['login']])) {
            $this->profile_links[$user['login']] = $user['url'];
        }
    }

    /**
     * Format the change log into Markdown.
     */
    public function formatChangeLog()
    {
        $tmp_markdown = "## $this->tag" . PHP_EOL;
        $tmp_markdown .= '*(' . date('Y-m-d') . ')*' . PHP_EOL . PHP_EOL;

        if (! empty($this->changelog_users)) {
            $tmp_markdown .= 'A big thank you to the following ' . count($this->changelog_users) . ' contributors this last month:' . PHP_EOL . PHP_EOL;
            $tmp_markdown .= $this->formatUserList($this->changelog_users);
        }

        $tmp_markdown .= PHP_EOL;

        if (! empty($this->changelog_mergers)) {
            $tmp_markdown .= 'Thanks to maintainers and others that helped with pull requests this month:' . PHP_EOL . PHP_EOL;
            $tmp_markdown .= $this->formatUserList($this->changelog_mergers) . PHP_EOL;
        }

        foreach ($this->changelog as $section => $items) {
            if (! empty($items)) {
                $tmp_markdown .= '#### ' . ucwords($section) . PHP_EOL;
                $tmp_markdown .= '* ' . implode('* ', $items) . PHP_EOL;
            }
        }

        $this->markdown = $tmp_markdown;
    }

    /**
     * Create a markdown list of users and link their github profile
     * @param array $users
     * @return string
     */
    private function formatUserList($users)
    {
        $output = '';
        arsort($users);
        foreach ($users as $user => $count) {
            $output .= "  - [$user]({$this->profile_links[$user]}) ($count)" . PHP_EOL;
        }

        return $output;
    }

    /**
     * Update the specified file with the new Change log info.
     */
    public function writeChangeLog()
    {
        if (file_exists($this->file)) {
            $existing = file_get_contents($this->file);
            $content = $this->getMarkdown() . PHP_EOL . $existing;
            if (is_writable($this->file)) {
                file_put_contents($this->file, $content);
            }
        } else {
            echo "Couldn't write to file {$this->file}" . PHP_EOL;
            exit;
        }
    }

    /**
     * Return the generated markdown.
     *
     * @return mixed
     */
    public function getMarkdown()
    {
        return $this->markdown;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function createRelease()
    {
        // push the changelog and version bump
        $this->pushFileContents($this->file, file_get_contents($this->file), "Changelog for $this->tag");
        $updated_sha = $this->pushVersionBump();

        // make sure the markdown is built
        if (empty($this->markdown)) {
            $this->createChangelog(false);
        }

        $release = Requests::post($this->github . '/releases', $this->getHeaders(), json_encode([
            'tag_name' => $this->tag,
            'target_commitish' => $updated_sha,
            'body' => $this->markdown,
            'draft' => false,
        ]));

        return $release->status_code == 201;
    }

    /**
     * Function to control the creation of creating a change log.
     * @param bool $write
     * @throws Exception
     */
    public function createChangelog($write = true)
    {
        $previous_release = $this->getRelease($this->from);
        if (! is_null($this->pr)) {
            $this->getPullRequest();
        }

        if (! isset($previous_release['published_at'])) {
            throw new Exception(
                $previous_release['message'] ??
                "Could not find previous release tag. ($this->from)"
            );
        }

        $this->getPullRequests($previous_release['published_at']);
        $this->buildChangeLog();
        $this->formatChangeLog();

        if ($write) {
            $this->writeChangeLog();
        }
    }

    private function pushVersionBump()
    {
        $version_file = 'LibreNMS/Util/Version.php';
        $contents = file_get_contents(base_path($version_file));
        $updated_contents = preg_replace("/const VERSION = '[^']+';/", "const VERSION = '$this->tag';", $contents);

        return $this->pushFileContents($version_file, $updated_contents, "Bump version to $this->tag");
    }

    /**
     * @param string $file Path in git repo
     * @param string $contents new file contents
     * @param string $message The commit message
     * @return Requests_Response
     */
    private function pushFileContents($file, $contents, $message)
    {
        $existing = Requests::get($this->github . '/contents/' . $file, $this->getHeaders());
        $existing_sha = json_decode($existing->body)->sha;

        $updated = Requests::put($this->github . '/contents/' . $file, $this->getHeaders(), json_encode([
            'message' => $message,
            'content' => base64_encode($contents),
            'sha' => $existing_sha,
        ]));

        return json_decode($updated->body)->commit->sha;
    }
}
