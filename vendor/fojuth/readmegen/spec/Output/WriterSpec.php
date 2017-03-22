<?php namespace spec\ReadmeGen\Output;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ReadmeGen\Output\Format\FormatInterface;

class WriterSpec extends ObjectBehavior
{
    protected $fileName = 'foobar.md';
    protected $fileNameWithBreakpoint = 'foobar_break.md';
    protected $break = '- Changelog';

    function let(FormatInterface $formatter)
    {
        $this->beConstructedWith($formatter);
        file_put_contents($this->fileNameWithBreakpoint, "line one\n{$this->break}\nline two\n##{$this->break}\nline three");
    }

    function letgo()
    {
        @ unlink($this->fileName);
        @ unlink($this->fileNameWithBreakpoint);
    }

    function it_should_write_log_output_to_a_file(FormatInterface $formatter)
    {
        $logContent = array(
            'Features:',
            '- foo',
            '- bar',
        );

        $formatter->generate()->willReturn($logContent);
        $formatter->getFileName()->willReturn($this->fileName);

        $this->write();

        if (false === file_exists($this->fileName)) {
            throw new \Exception(sprintf('File %s has not been created.', $this->fileName));
        }

        $content = file_get_contents($this->fileName);

        if (true === empty($content)) {
            throw new \Exception(sprintf('File %s is empty.', $this->fileName));
        }

        if (trim($content) !== join("\n", $logContent)) {
            throw new \Exception('File content differs from expectations.');
        }
    }

    function it_should_add_content_after_breakpoint(FormatInterface $formatter)
    {
        $logContent = array(
            'Features:',
            '- foo',
            '- bar',
        );

        $resultContent = array(
            'line one',
            $this->break,
            'Features:',
            '- foo',
            '- bar',
            '',
            'line two',
            '##'.$this->break,
            'line three',
        );

        $formatter->generate()->willReturn($logContent);
        $formatter->getFileName()->willReturn($this->fileNameWithBreakpoint);

        $this->setBreak($this->break);
        $this->write();

        $content = file_get_contents($this->fileNameWithBreakpoint);

        if (trim($content) !== join("\n", $resultContent)) {
            throw new \Exception('File content differs from expectations.');
        }
    }

}
