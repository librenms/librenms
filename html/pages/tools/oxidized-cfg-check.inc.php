<?php

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

if (isset($_POST['config'])) {
    try {
        $oxidized_cfg = Yaml::parse($_POST['config']);
        print_r(validate_oxidized_cfg($oxidized_cfg));
    } catch (ParseException $e) {
        echo "<div class='alert alert-danger'>{$e->getMessage()}</div>";
    }
}
?>

    <form method="post">
        <div class="form-group">
            <label for="exampleInputEmail1">Paste your Oxidized yaml config:</label>
            <textarea name="config" value="config" rows="50" class="form-control" placeholder="Paste your Oxidized yaml config"><?php echo $_POST['config']; ?></textarea>
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
    </form>

<?php

function validate_oxidized_cfg($tree) {
    $valid_config = array(
        'username',
        'password',
        'model',
        'interval',
        'use_syslog',
        'log',
        'debug',
        'threads',
        'timeout',
        'retries',
        'prompt',
        'models',
        'vars' => array(
            'enable',
        ),
        'groups',
        'rest',
        'pid',
        'input' => array(
            'default',
            'debug',
            'ssh' => array(
                'secure',
            ),
        ),
        'output' => array(
            'default',
            'git' => array(
                'user',
                'email',
                'repo',
            ),
        ),
        'source' => array(
            'default',
            'csv' => array(
                'file',
                'delimiter',
                'map' => array(
                    'name',
                    'model',
                    'username',
                    'password',
                ),
                'vars_map' => array(
                    'enable',
                ),
            ),
        ),
        'model_map',
        'next_adds_job'
    );
    print_r(array_diff_assoc($tree, $valid_config));exit;
    foreach ($tree as $leaf => $value) {
        if (!in_array($leaf, $valid_config)) {
            $output[] = "$leaf - {$valid_config[$leaf]} is not valid";
        }
    }
    return $output;
}