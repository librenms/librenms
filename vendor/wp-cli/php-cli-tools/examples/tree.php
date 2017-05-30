<?php

require_once 'common.php';

$data = array(
    'Test' => array(
        'Something Cool' => array(
            'This is a 3rd layer',
        ),
        'This is a 2nd layer',
    ),
    'Other test' => array(
        'This is awesome' => array(
            'This is also cool',
            'This is even cooler',
            'Wow like what is this' => array(
                'Awesome eh?',
                'Totally' => array(
                    'Yep!'
                ),
            ),
        ),
    ),
);

printf("ASCII:\n");

/**
 * ASCII should look something like this:
 *
 * -Test
 * |\-Something Cool
 * ||\-This is a 3rd layer
 * |\-This is a 2nd layer
 * \-Other test
 *  \-This is awesome
 *   \-This is also cool
 *   \-This is even cooler
 *   \-Wow like what is this
 *    \-Awesome eh?
 *    \-Totally
 *     \-Yep!
 */

$tree = new \cli\Tree;
$tree->setData($data);
$tree->setRenderer(new \cli\tree\Ascii);
$tree->display();

printf("\nMarkdown:\n");

/**
 * Markdown looks like this:
 *
 * - Test
 *     - Something Cool
 *         - This is a 3rd layer
 *     - This is a 2nd layer
 * - Other test
 *     - This is awesome
 *         - This is also cool
 *         - This is even cooler
 *         - Wow like what is this
 *             - Awesome eh?
 *             - Totally
 *                 - Yep!
 */

$tree = new \cli\Tree;
$tree->setData($data);
$tree->setRenderer(new \cli\tree\Markdown(4));
$tree->display();
