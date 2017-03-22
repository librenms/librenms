<?php

namespace spec\ReadmeGen\Input;

use PhpSpec\ObjectBehavior;

class ParserSpec extends ObjectBehavior
{
    function it_should_fetch_options()
    {
        $this->setInput('someDummyContent --f=foo --to=bar --release=4.5.6');

        $result = $this->parse();

        $result['from']->shouldReturn('foo');
        $result['f']->shouldReturn('foo');
        $result['to']->shouldReturn('bar');
        $result['t']->shouldReturn('bar');
        $result['release']->shouldReturn('4.5.6');
        $result['r']->shouldReturn('4.5.6');
    }

    function it_should_check_for_required_arguments()
    {
        $this->setInput('someDummyContent --from=1.2');
        $this->shouldThrow('\BadMethodCallException')->during('parse');

        $this->setInput('someDummyContent --release=1.2');
        $this->shouldThrow('\BadMethodCallException')->during('parse');

        $this->setInput('someDummyContent --release=1.2 --from=2.3');
        $result = $this->parse();

        $result['release']->shouldBe('1.2');
        $result['from']->shouldBe('2.3');
    }
}
