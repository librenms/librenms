<?php
/**
 * Created by PhpStorm.
 * User: neillathwood
 * Date: 02/06/2018
 * Time: 21:38
 */

namespace LibreNMS\Alert\Template;

class Blade extends Template
{
    /**
     *
     * Get the parsed body
     *
     * @param $data
     * @return string
     */
    public function getBody($data)
    {
        try {
            return view(['template' => $this->getTemplate()->template], $data)->__toString();
        } catch (\Exception $e) {
            return view(['template' => $this->getDefaultTemplate()], $data)->__toString();
        }
    }

    /**
     *
     * Get the parsed title
     *
     * @param $data
     * @return string
     */
    public function getTitle($data)
    {
        try {
            return view(['template' => $this->getTemplate()->title], $data)->__toString();
        } catch (\Exception $e) {
            return view(['template' => "Template " . $this->getTemplate()->name], $data)->__toString();
        }
    }

    /**
     *
     * Get the default template for this parsing engine
     *
     * @return string
     */
    public function getDefaultTemplate()
    {
        return '{{ $title }}' . PHP_EOL .
            'Severity: {{ $severity }}' . PHP_EOL .
            '@if ($state == 0)Time elapsed: {{ $elapsed }} @endif ' . PHP_EOL .
            'Timestamp: {{ $timestamp }}' . PHP_EOL .
            'Unique-ID: {{ $uid }}' . PHP_EOL .
            'Rule: @if ($name) {{ $name }} @else {{ $rule }} @endif ' . PHP_EOL .
            '@if ($faults)Faults:' . PHP_EOL .
            '@foreach ($faults as $key => $value)' . PHP_EOL .
            '  #{{ $key }}: {{ $value[\'string\'] }} @endforeach' . PHP_EOL .
            '@endif' . PHP_EOL .
            'Alert sent to: @foreach ($contacts as $key => $value) {{ $value }} <{{ $key }}> @endforeach';
    }
}
