<?php

namespace spec\ReadmeGen\Output\Format;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MdSpec extends ObjectBehavior
{
    protected $issueTrackerUrl = 'http://some.issue.tracker.com/show/';
    protected $issueTrackerPattern = 'http://some.issue.tracker.com/show/\1';
    protected $log = array(
        'Features' => array(
            'bar #123 baz',
            'dummy feature',
        ),
        'Bugfixes' => array(
            'some bugfix (#890)',
        ),
    );

    function let() {
        $this->setLog($this->log);
    }

    function it_should_add_links_to_the_issue_tracker()
    {
        $result = array(
            'Features' => array(
                "bar [#123]({$this->issueTrackerUrl}123) baz",
                'dummy feature',
            ),
            'Bugfixes' => array(
                "some bugfix ([#890]({$this->issueTrackerUrl}890))",
            ),
        );

        $this->setIssueTrackerUrlPattern($this->issueTrackerPattern);
        $this->decorate()->shouldReturn($result);
    }

    function it_should_generate_a_write_ready_output() {
        $this->setRelease('4.5.6')
            ->setDate(new \DateTime('2014-12-21'));

        $result = array(
            "## 4.5.6",
            "*(2014-12-21)*",
            "\n#### Features",
            '* bar #123 baz',
            '* dummy feature',
            "\n#### Bugfixes",
            '* some bugfix (#890)',
            "\n---\n",
        );

        $this->generate()->shouldReturn($result);
    }
}
