<?php namespace Yottaram\AdministratorConfig\Models;

use DB;
use Doctrine\DBAL\DBALException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Schema;

class ModelConfig {

    private $modelInstance;
    private $parentCommand;

    public function __construct(Model $modelInstance, Command $parentCommand) 
    {
        $this->modelInstance = $modelInstance;
        $this->parentCommand = $parentCommand;

    } 

    public function outputPHP()
    {
        $php = [];

        $className = get_class($this->modelInstance);
        $php['title'] = str_plural($className);
        $php['single'] = $className;
        $php['model'] = $className;
        $php['form_width'] = 700;

        $columns = [];
        $edit_fields = [];

        $tableName = $this->modelInstance->getTable();

        $columnNames = Schema::getColumnListing($tableName); 

        // TODO - would be nice to have column types here.  Doctrine can do this but is limited in that it doesn't support certain column types like enums.

        foreach ($columnNames as $columnName) {
            $this->putColumnField($columns, $columnName);
            $this->putEditField($edit_fields, $columnName);
        }

        if (empty($columns) || empty($edit_fields)) {
            return null;
        }

        $php['columns'] = $columns;
        $php['edit_fields'] = $edit_fields;


        return "<?php \n\n return " . var_export($php, true) . ";";
    }

    // TODO 
    private function getAdministratorTypeForDBType($dbType) 
    {
        switch ($dbType) {
            case 'string' : 
                return 'text';
            case 'text':
                return 'textarea';
            case 'datetime' :
                return 'datetime';
            default:
                return 'text'; 
        }
    }

    // ignore certain column names in edit fields
    private function ignoreColumnNameForEditField($columnName)
    {
        if (in_array($columnName, [ 'id', 'created_at', 'updated_at' ])) {
            return true;
        }
        return false;
    }

    private function putColumnField(array &$columns, $columnName)
    {
        $columns[$columnName] = [ 'title' => studly_case($columnName) ];
    }

    private function putEditField(array &$edit_fields, $columnName, $columnType = null)
    {
        if ($this->ignoreColumnNameForEditField($columnName)) {
            return;
        }
        $typeGuess = $this->getAdministratorTypeForDBType($columnType);

        $edit_fields[$columnName] = [ 'title' => studly_case($columnName), 'type' => $typeGuess ];
    }

}
