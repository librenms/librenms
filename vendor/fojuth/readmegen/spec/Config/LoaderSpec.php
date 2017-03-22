<?php

namespace spec\ReadmeGen\Config;

use PhpSpec\ObjectBehavior;

class LoaderSpec extends ObjectBehavior
{

    protected $dummyConfigFile = 'dummy_config.yaml';
    protected $badConfigFile = 'dummy_config_bad.yaml';

    function let()
    {
        file_put_contents($this->dummyConfigFile, "vcs: git\nfoo: bar");
        file_put_contents($this->badConfigFile, "badly:\tformed\n\tfile");
    }

    function letgo()
    {
        unlink($this->dummyConfigFile);
        unlink($this->badConfigFile);
    }

    function it_should_throw_exception_when_default_config_doesnt_exist()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('get', array('foobar.yml'));
    }

    function it_should_throw_exception_when_the_config_file_is_malformed()
    {
        $this->shouldThrow('\Symfony\Component\Yaml\Exception\ParseException')->during('get', array($this->badConfigFile));
    }

    function it_loads_the_default_config()
    {
        $this->get($this->dummyConfigFile)->shouldBeArray();
    }

    function it_should_have_specific_values_loaded()
    {
        $this->get($this->dummyConfigFile)->shouldHaveKey('vcs');
    }

    function getMatchers()
    {
        return array(
            'haveKey' => function($subject, $key) {
                return array_key_exists($key, $subject);
            },
        );
    }

}
