<?php

namespace App\Commands\Generators;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

class CRUDGenerator extends BaseCommand
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
    protected $name = 'make:crud';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'make:crud <name> [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'name' => 'The model class name'
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--only'            => 'Not create to create a model, an entity, or a migration',
        '-o'                => 'Same as --only',
        '--no-model'        => 'Not create a model',
        '-nmo'              => 'Same as --no-model',
        '--no-entity'       => 'Not create an entity (If --no-model or -nmo is declared, it is declared automatically)',
        '-nen'              => 'Same as --no-entity',
        '--no-migration'    => 'Not create a migration',
        '-nmi'              => 'Same as --no-migration',
        '--force'           => 'Force overwrite existing files.'
    ];

    protected function createDefaultParams(array $params) {
        $newParams = [];

        $newParams[0] = $params[0] ?? CLI::getSegment(2);
        if (array_key_exists('force', $params) || CLI::getOption('force')) {
            $newParams['force'] = null;
        }
        $newParams['namespace'] = APP_NAMESPACE;

        return $newParams;
    }

    /**
     * Actually execute a command.
     */
    public function run(array $params)
    {
        $isRequiredAll = (is_null($this->getOption('o')) && is_null($this->getOption('only')));
        $needModel = $isRequiredAll ? (is_null($this->getOption('nmo')) && is_null($this->getOption('no-model'))) : false;
        $needEntity = $needModel ? (is_null($this->getOption('ne')) && is_null($this->getOption('no-entity'))) : false;
        $needMigration = $isRequiredAll ? (is_null($this->getOption('nmi')) && is_null($this->getOption('no-migration'))) : false;

        if($needModel) {
            $newParams = $this->createDefaultParams($params);
            $newParams['return'] = $needEntity ? 'entity' : 'object';

            $this->call('crud:model', $newParams);
        }

        if($needMigration) {
            $newParams = $this->createDefaultParams($params);
            $newParams[0] = "create_{$newParams[0]}";
            $this->call('make:migration', $newParams);
        }

        $this->component = 'Controller';
        $this->directory = 'Controllers\Api';
        $this->template  = 'crud.template.php';

        $this->classNameLang = 'CLI.generator.className.controller';
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

        $useStatement = 'App\Controllers\BaseController';
        $varName = strtolower($baseClass);
        $return  = is_string($return) ? $return : 'object';

        if (! in_array($return, ['object', 'entity'], true)) {
            // @codeCoverageIgnoreStart
            $return = CLI::prompt(lang('CLI.generator.returnType'), ['object', 'entity'], 'required');
            CLI::newLine();
            // @codeCoverageIgnoreEnd
        }

        return $this->parseTemplate(
            $class,
            ['{useStatement}', '{varName}'],
            [$useStatement, $varName],
            ['modelType' => $return]
        );
    }

    /**
     * Gets the generator view as defined in the `Config\Generators::$views`,
     * with fallback to `$template` when the defined view does not exist.
     */
    protected function renderTemplate(array $data = []): string
    {
        return view(APP_NAMESPACE . "\\Commands\\Generators\\Views\\{$this->template}", $data, ['debug' => false]);
    }
}
