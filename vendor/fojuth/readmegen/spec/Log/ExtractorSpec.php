<?php

namespace spec\ReadmeGen\Log;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExtractorSpec extends ObjectBehavior
{

    function it_extracts_messages_from_log()
    {
        $log = array(
            'foo',
            'feature: bar baz',
            'nope',
            'feature: dummy feature',
            'feat: lol',
            'also nope',
            'fix: some bugfix',
        );

        $messageGroups = array(
            'Features' => array('feature', 'feat'),
            'Bugfixes' => array('bugfix', 'fix'),
            'Docs' => array('docs'),
        );

        $result = array(
            'Features' => array(
                'bar baz',
                'dummy feature',
                'lol',
            ),
            'Bugfixes' => array(
                'some bugfix',
            ),
        );

        $this->setLog($log);
        $this->setMessageGroups($messageGroups);

        $this->extract()->shouldReturn($result);
    }

}
