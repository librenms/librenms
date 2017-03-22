<?php

namespace spec\ReadmeGen\Log;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ReadmeGen\Output\Format\FormatInterface;

class DecoratorSpec extends ObjectBehavior
{

    function let(FormatInterface $formatter)
    {
        $this->beConstructedWith($formatter);
    }

    function it_should_set_the_correct_output_class(FormatInterface $formatter)
    {
        $formatter->setLog(array())->shouldBeCalled();
        $formatter->setIssueTrackerUrlPattern('')->shouldBeCalled();
        $formatter->decorate()->willReturn('foo');

        $this->setLog(array());
        $this->setIssueTrackerUrlPattern('');
        $this->decorate()->shouldReturn('foo');
    }

}
