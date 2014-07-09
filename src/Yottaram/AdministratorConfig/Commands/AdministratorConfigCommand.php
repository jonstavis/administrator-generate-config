<?php namespace Yottaram\AdministratorConfig\Commands;

use Config;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Yottaram\AdministratorConfig\Models\ModelConfig;

class AdministratorConfigCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'administrator:config';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set up a new Laravel Administrator configuration.';

    private $adminConfigPath;
    private $modelConfigPath;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{

        $noPrompt = $this->option('no-prompt');
        $modelName = $this->argument('model-name');

        // look for administrator config
        $this->adminConfigPath = app_path() . "/config/packages/frozennode/administrator/administrator.php";
        if (!file_exists($this->adminConfigPath)) {
            $this->error(sprintf("Administrator config not found at %s.\nPerhaps try 'php artisan config:publish frozennode/administrator'?",$this->adminConfigPath));
            exit();
        }

        // confirm the model config output directory
        $this->modelConfigPath = Config::get('administrator::administrator.model_config_path');

        if (!$this->getYes(sprintf("Model config output directory located at %s.  Ok to proceed? (will not overwrite existing model configs) [Yes]/no:", $this->modelConfigPath))) {
            $this->error(sprintf("You may edit your model_config_path in the Administrator config file at %s", $this->adminConfigPath));
            exit();
        }

        // create the model config output directory if it doesn't exist
        if (!file_exists($this->modelConfigPath)) {
            mkdir($this->modelConfigPath);
            $this->info("Created model config output directory");
        } 

        $modelNames = [];

        // check if the model name was specified, has a corresponding file, and is a subclass of Eloquent Model
        if ($modelName && !$this->classExistsAndIsModelSubclass($modelName)) {
            $this->error(sprintf("Model '%s' not found", $modelName));
            exit();
        } elseif ($modelName) {
            array_push($modelNames, $modelName);
        }

        // if no model names were specified then scan the model dir
        if (sizeof($modelNames) == 0) {
            foreach (scandir(app_path() . '/models') as $fileName) {
                if (starts_with($fileName, '.')) {
                    continue;
                }
                $className = str_replace('.php','',$fileName);

                // ask if we should process this model
                if (!$noPrompt) {
                    $doModel = $this->getYes(sprintf("Generate configuration for model class %s? [Yes]/no:", $className));
                    if (!$doModel) {
                        continue;
                    }
                }

                if (!$this->classExistsAndIsModelSubclass($className)) {
                    $this->error(sprintf("Model of name %s not found for corresponding file %s", $className, $fileName));
                    exit();
                }

                array_push($modelNames, $className);
            }
        }

        foreach ($modelNames as $modelName) {
            $instance = new $modelName;
            $modelConfig = new ModelConfig($instance, $this);
            $php = $modelConfig->outputPHP();

            if (empty($php)) {
                $this->error(sprintf("No columns found for model %s.  Skipping config generation", $modelName));
                continue;
            }

            $modelConfigFile = $this->getFullPathToModelConfig($modelName);

            if (file_exists($modelConfigFile)) {
                $this->error(sprintf("Model config already exists at %s.  Skipping this particular configuration", $modelConfigFile));
                continue;
            }

            file_put_contents($modelConfigFile, $php);

            $this->info(sprintf("Created model config file %s", $modelConfigFile));
        }

        $this->info(sprintf("Model config generation complete.  You now need to add model names to the 'menu' property in the Administrator config file: %s", $this->adminConfigPath));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
            array('model-name', InputArgument::OPTIONAL, 'Specify one model name to generate a config file for', null),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
            array('no-prompt', null, InputOption::VALUE_NONE, 'Create config for each model without prompting (will not overwrite existing config)', null),
		);
	}

    private function classExistsAndIsModelSubclass($className)
    {
        return (class_exists($className) && is_subclass_of(new $className, 'Illuminate\Database\Eloquent\Model'));
    }

    // prompt the user with a message and return whether it was answered affirmatively with Yes as default
    private function getYes($message)
    {
        return (strtolower($this->ask($message) ?: 'Yes') == 'yes');

    }

    private function getFullPathToModelConfig($className)
    {
        return $this->modelConfigPath . '/' . str_plural(strtolower($className)) . '.php';
    }

}

