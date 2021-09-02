<?php

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

if (isset($_POST['config'])) {
    try {
        $oxidized_cfg = Yaml::parse($_POST['config']);
        $validate_cfg = validate_oxidized_cfg($oxidized_cfg);
        foreach ($validate_cfg as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
        if (empty($validate_cfg)) {
            echo '<div class="alert alert-success">Config has validated ok</div>';
        }
    } catch (ParseException $e) {
        echo "<div class='alert alert-danger'>{$e->getMessage()}</div>";
    }
}
?>

    <form method="post">
        <?php echo csrf_field() ?>
        <div class="form-group">
            <label for="exampleInputEmail1">Paste your Oxidized yaml config:</label>
            <textarea name="config" value="config" rows="20" class="form-control" placeholder="Paste your Oxidized yaml config"><?php echo $_POST['config']; ?></textarea>
        </div>
        <button type="submit" class="btn btn-default btn-primary">Validate YAML</button>
    </form>

<?php

function validate_oxidized_cfg($tree, $wanted_leaf = false)
{
    $valid_config = [
        'username' => 'string',
        'password' => 'string',
        'model' => 'string',
        'interval' => 'int',
        'use_syslog' => 'boolean',
        'log' => 'string',
        'debug' => 'boolean',
        'threads' => 'int',
        'timeout' => 'int',
        'retries' => 'int',
        'prompt' => 'string',
        'models' => 'array',
        'vars' => [
            'enable' => 'boolean',
            'ssh_no_exec' => 'boolean',
            'remove_secret' => 'boolean',
        ],
        'groups' => 'array',
        'rest' => 'string',
        'pid' => 'string',
        'input' => [
            'default' => 'string',
            'debug' => 'boolean',
            'ssh' => [
                'secure' => 'boolean',
            ],
        ],
        'output' => [
            'default' => 'string',
            'git' => [
                'user' => 'string',
                'email' => 'string',
                'repo' => 'string',
            ],
        ],
        'source' => [
            'default' => 'string',
            'csv' => [
                'file' => 'string',
                'delimiter' => 'string',
                'map' => [
                    'name' => 'string',
                    'model' => 'string',
                    'username' => 'string',
                    'password' => 'string',
                    'group' => 'group',
                ],
                'vars_map' => [
                    'enable' => 'string',
                ],
            ],
            'http' => [
                'url' => 'string',
                'scheme' => 'string',
                'secure' => 'boolean',
                'delimiter' => 'string',
                'user' => 'string',
                'pass' => 'string',
                'map' => [
                    'name' => 'string',
                    'model' => 'string',
                    'username' => 'string',
                    'password' => 'string',
                    'group' => 'group',
                ],
                'vars_map' => 'array',
                'headers' => [
                    'X-Auth-Token' => 'string',
                ],
            ],
            'mysql' => [
                'adapter' => 'string',
                'database' => 'string',
                'table' => 'string',
                'user' => 'string',
                'password' => 'password',
                'map' => [
                    'name' => 'string',
                    'model' => 'string',
                    'username' => 'string',
                    'password' => 'string',
                    'group' => 'group',
                ],
                'vars_map' => 'array',
            ],
        ],
        'model_map' => 'array',
        'next_adds_job' => 'boolean',
        'hooks' => 'array',
    ];

    if ($wanted_leaf !== false) {
        $valid_config_tmp = $wanted_leaf;
    } else {
        $valid_config_tmp = $valid_config;
    }

    $output = [];
    foreach ($tree as $leaf => $value) {
        if (is_array($tree[$leaf]) && ($valid_config_tmp !== 'array' && $valid_config_tmp[$leaf] !== 'array')) {
            $tmp_output = validate_oxidized_cfg($tree[$leaf], $valid_config_tmp[$leaf]);
            if (is_array($tmp_output)) {
                $output = array_merge($output, $tmp_output);
            }
        } else {
            if (! isset($valid_config_tmp[$leaf]) && ($valid_config_tmp !== 'array' && $valid_config_tmp[$leaf] !== 'array')) {
                $output[] = "$leaf - is not valid";
            }
        }
    }
    if (! empty($output)) {
        return $output;
    }
}
