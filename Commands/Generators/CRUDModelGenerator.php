<?php

namespace App\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

class CRUDModelGenerator extends BaseCommand
{
    use GeneratorTrait;

    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Generators';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'crud:model';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generates a new model file.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'crud:model <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The entity class name.',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--return'    => 'Return type, Options: [object, entity]. Default: "object".',
        '--force'     => 'Force overwrite existing file.',
    ];

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $this->component = 'Model';
        $this->directory = 'Models';
        $this->template  = 'model.tpl.php';

        $this->classNameLang = 'CLI.generator.className.model';
        $this->execute($params);
    }

    /**
     * Prepare options and do the necessary replacements.
     */
    protected function prepare(string $class): string
    {
        $return  = $this->getOption('return');

        $baseClass = class_basename($class);

        if (preg_match('/^(\S+)Model$/i', $baseClass, $match) === 1) {
            $baseClass = $match[1];
        }

        $table   = strtolower($baseClass);
        $return  = is_string($return) ? $return : 'object';

        if (! in_array($return, ['object', 'entity'], true)) {
            // @codeCoverageIgnoreStart
            $return = CLI::prompt(lang('CLI.generator.returnType'), ['object', 'entity'], 'required');
            CLI::newLine();
            // @codeCoverageIgnoreEnd
        }

        if ($return === 'entity') {
            $return = str_replace('Models', 'Entities', $class);

            if (preg_match('/^(\S+)Model$/i', $return, $match) === 1) {
                $return = $match[1];
            }

            $return = '\\' . trim($return, '\\') . '::class';
            $this->call('make:entity', array_merge([$baseClass], $this->params));
        } else {
            $return = "'{$return}'";
        }

        return $this->parseTemplate($class, ['{table}', '{dbGroup}', '{return}'], [$table, 'default', $return]);
    }
}
