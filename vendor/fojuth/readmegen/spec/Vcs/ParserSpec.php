<?php

namespace spec\ReadmeGen\Vcs;

use PhpSpec\ObjectBehavior;
use ReadmeGen\Vcs\Type\TypeInterface;

class ParserSpec extends ObjectBehavior
{
    function let(TypeInterface $vcs)
    {
        $this->beConstructedWith($vcs);
    }
    
    function it_should_parse_the_vcs_log_into_an_array(TypeInterface $vcs)
    {
        $returnData = array(
            'foo bar',
            'baz',
        );
        
        $vcs->parse()->willReturn($returnData);
        
        $this->parse()->shouldBe($returnData);
    }
}
