<?php

namespace spec\ReadmeGen\Vcs\Type;

use PhpSpec\ObjectBehavior;
use ReadmeGen\Shell;
use ReadmeGen\Vcs\Type\Git;

class GitSpec extends ObjectBehavior
{
    
    function it_should_parse_a_git_log(Shell $shell)
    {
        $this->setArguments(array('from' => '1.0'));

        $log = sprintf("Foo bar.%s\nDummy message.%s\n\n", Git::MSG_SEPARATOR, Git::MSG_SEPARATOR);
        $shell->run($this->getCommand())->willReturn($log);
        
        $this->setShellRunner($shell);
        
        $this->parse()->shouldReturn(array(
            'Foo bar.',
            'Dummy message.',
        ));
    }
    
    function it_has_input_options_and_arguments()
    {
        $this->setOptions(array('a'));
        $this->setArguments(array('foo' => 'bar'));
        
        $this->hasOption('z')->shouldReturn(false);
        $this->hasOption('a')->shouldReturn(true);
        
        $this->hasArgument('wat')->shouldReturn(false);
        $this->hasArgument('foo')->shouldReturn(true);
        $this->getArgument('foo')->shouldReturn('bar');
    }
    
    function it_should_add_options_and_arguments_to_the_command(Shell $shell)
    {
        $log = sprintf("Foo bar.%s\nDummy message.%s\n\n", Git::MSG_SEPARATOR, Git::MSG_SEPARATOR);
        $shell->run(sprintf('git log --pretty=format:"%%s%s%%b"', Git::MSG_SEPARATOR))->willReturn($log);
        
        $this->setShellRunner($shell);
        
        $this->setOptions(array('x', 'y'));
        $this->setArguments(array('foo' => 'bar', 'baz' => 'wat', 'from' => '1.0'));
        
        $this->getCommand()->shouldReturn('git log --pretty=format:"%s'.Git::MSG_SEPARATOR.'%b" 1.0..HEAD --x --y');
    }

    function it_should_properly_include_the_from_and_to_arguments() {
        $this->setOptions(array('x', 'y'));

        $this->setArguments(array('from' => '3.4.5', 'foo' => 'bar'));
        $this->getCommand()->shouldReturn('git log --pretty=format:"%s'.Git::MSG_SEPARATOR.'%b" 3.4.5..HEAD --x --y');

        $this->setArguments(array('from' => '3.4.5', 'foo' => 'bar', 'to' => '4.0'));
        $this->getCommand()->shouldReturn('git log --pretty=format:"%s'.Git::MSG_SEPARATOR.'%b" 3.4.5..4.0 --x --y');
    }

    function it_returns_the_date_of_the_commit(Shell $shell) {
        $shell->run('git log -1 -s --format=%ci 3f04264')->willReturn('2014-11-28 01:01:58 +0100');

        $this->setShellRunner($shell);

        $this->setArguments(array('from' => '1.0', 'to' => '3f04264'));
        $this->getToDate()->shouldReturn('2014-11-28');
    }
    
}
