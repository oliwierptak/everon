<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\DataMapper\Schema;

use Everon\DataMapper\Interfaces;
use Everon\Dependency;
use Everon\Interfaces\PdoAdapter;
use Everon\Helper;

abstract class Reader implements Interfaces\Schema\Reader
{
    use Dependency\PdoAdapter;
    use Dependency\Injection\Factory;
    use Helper\Arrays;
    
    
    protected $database = null;
    
    protected $column_list = null;
    
    protected $constraint_list = null;
    
    protected $foreign_key_list = null;
    
    protected $unique_key_list = null;
    
    protected $table_list = null;
    
    
    abstract protected function getTablesSql();
    abstract protected function getColumnsSql();
    abstract protected function getPrimaryKeysSql();
    abstract protected function getUniqueKeysSql();
    abstract protected function getForeignKeysSql();


    /**
     * @param PdoAdapter $PdoAdapter
     */
    public function __construct(PdoAdapter $PdoAdapter)
    {
        $this->PdoAdapter = $PdoAdapter;
    }

    /**
     * @inheritdoc
     */
    public function getDriver()
    {
        return $this->getPdoAdapter()->getConnectionConfig()->getDriver();
    }

    public function getDatabase()
    {
        return $this->getPdoAdapter()->getConnectionConfig()->getDatabase();
    }
    
    public function getAdapterName()
    {
        return $this->getPdoAdapter()->getConnectionConfig()->getAdapterName();
    }

    /**
     * @inheritdoc
     */
    public function getTableList()
    {
        if ($this->table_list === null) {
            $this->table_list = $this->arrayArrangeByKeySingle('__TABLE_NAME',
                $this->getPdoAdapter()->execute($this->getTablesSql(), ['schema'=>$this->getDatabase()], \PDO::FETCH_ASSOC)->fetchAll()
            );
        }
        return $this->table_list;
    }

    /**
     * @inheritdoc
     */
    public function getColumnList()
    {
        if ($this->column_list === null) {
            $this->column_list = $this->arrayArrangeByKey('__TABLE_NAME',
                $this->getPdoAdapter()->execute($this->getColumnsSql(), ['schema'=>$this->getDatabase()], \PDO::FETCH_ASSOC)->fetchAll()
            );
        }
        return $this->column_list;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeysList()
    {
        if ($this->constraint_list === null) {
            $this->constraint_list = $this->arrayArrangeByKey('__TABLE_NAME',
                $this->getPdoAdapter()->execute($this->getPrimaryKeysSql(), ['schema'=>$this->getDatabase()], \PDO::FETCH_ASSOC)->fetchAll()
            );
        }
        return $this->constraint_list;
    }

    /**
     * @inheritdoc
     */
    public function getForeignKeyList()
    {
        if ($this->foreign_key_list === null) {
            $this->foreign_key_list = $this->arrayArrangeByKey('__TABLE_NAME',
                $this->getPdoAdapter()->execute($this->getForeignKeysSql(), ['schema'=>$this->getDatabase()], \PDO::FETCH_ASSOC)->fetchAll()
            );
        }
        return $this->foreign_key_list;
    }
    
    /**
     * @inheritdoc
     */
    public function getUniqueKeysList()
    {
        if ($this->unique_key_list === null) {
            $this->unique_key_list = $this->arrayArrangeByKey('__TABLE_NAME',
                $this->getPdoAdapter()->execute($this->getUniqueKeysSql(), ['schema'=>$this->getDatabase()], \PDO::FETCH_ASSOC)->fetchAll()
            );
        }
        return $this->unique_key_list;
    }
    
    public function TMPdumpDataBaseSchema($dir, $prefix='mysql')
    {
        return;
        $tables = $this->getPdoAdapter()->execute($this->getTablesSql(), ['schema'=>$this->getDatabase()], \PDO::FETCH_ASSOC)->fetchAll();
        $columns = $this->getPdoAdapter()->execute($this->getColumnsSql(), ['schema'=>$this->getDatabase()], \PDO::FETCH_ASSOC)->fetchAll();
        $primary_keys = $this->getPdoAdapter()->execute($this->getPrimaryKeysSql(), ['schema'=>$this->getDatabase()], \PDO::FETCH_ASSOC)->fetchAll();
        $foreign_keys = $this->getPdoAdapter()->execute($this->getForeignKeysSql(), ['schema'=>$this->getDatabase()], \PDO::FETCH_ASSOC)->fetchAll();
        $unique_keys = $this->getPdoAdapter()->execute($this->getUniqueKeysSql(), ['schema'=>$this->getDatabase()], \PDO::FETCH_ASSOC)->fetchAll();

        $export = function($name, $data_to_export) use ($dir, $prefix) {
            $data = var_export($data_to_export, 1);
            $filename = $dir.$prefix.'_db_'.$name.'.php';
            @unlink($filename);
            $h = fopen($filename, 'w+');
            fwrite($h, "<?php return $data; ");
            fclose($h);
        };

        $export('tables', $tables);
        $export('columns', $columns);
        $export('primary_keys', $primary_keys);
        $export('foreign_keys', $foreign_keys);
        $export('unique_keys', $unique_keys);
    }
}