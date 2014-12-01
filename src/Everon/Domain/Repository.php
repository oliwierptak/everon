<?php
/**
 * This file is part of the Everon framework.
 *
 * (c) Oliwier Ptak <oliwierptak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Everon\Domain;

use Everon\DataMapper;
use Everon\Dependency;
use Everon\Domain;
use Everon\Exception;
use Everon\Interfaces\Collection;
use Everon\Helper;

abstract class Repository implements Interfaces\Repository
{
    use Domain\Dependency\Injection\DomainManager;
    use Dependency\Injection\Factory;
    use Dependency\Injection\Logger;
    use Helper\Arrays;
    use Helper\Asserts\IsArrayKey;
    
    /**
     * @var DataMapper
     */
    protected $Mapper = null;
    
    protected $name = null;

    /**
     * @var Collection
     */
    protected $RelationCollection = null;


    /**
     * @param $name
     * @param \Everon\Interfaces\DataMapper $Mapper
     */
    public function __construct($name, \Everon\Interfaces\DataMapper $Mapper)
    {
        $this->name = $name;
        $this->Mapper = $Mapper;
    }

    /**
     * Makes sure data defined in the Entity is in proper format and all keys are set
     * 
     * @param array $data
     * @param $check_id
     * @return array
     * @throws \Everon\Exception\Domain
     */
    protected function prepareDataForEntity(array $data, $check_id)
    {
        /**
         * @var \Everon\DataMapper\Interfaces\Schema\Column $Column
         */
        foreach ($this->getMapper()->getTable()->getColumns() as $name => $Column) {
            if ($check_id && $Column->isPk()) {
                $default[$name] = null;
                continue;
            }

            if (array_key_exists($name, $data) === false) {
                if ($Column->isNullable() === false) {
                    throw new Exception\Domain('Missing Entity data: "%s" for "%s"', [$name, $this->getMapper()->getTable()->getName()]);
                }
                $default[$name] = null;
            }
            $data[$name] = $Column->getColumnDataForEntity($data[$name]);
        }

        return $data;
    }

    /**
     * @param array $data
     * @param DataMapper\Interfaces\Criteria\Builder $RelationCriteria
     * @throws Exception\Domain
     * @return Interfaces\Entity
     */
    protected function buildEntity(array $data, DataMapper\Interfaces\Criteria\Builder $RelationCriteria = null)
    {
        $data = $this->prepareDataForEntity($data, true);
        $Entity = $this->getFactory()->buildDomainEntity($this->getName(), $this->getMapper()->getTable()->getPk(), $data);
        $this->buildEntityRelations($Entity, $RelationCriteria);
        return $Entity;
    }

    /**
     * @param Interfaces\Entity $Entity
     * @param DataMapper\Interfaces\Criteria\Builder $RelationCriteria
     */
    protected function buildEntityRelations(Interfaces\Entity $Entity, DataMapper\Interfaces\Criteria\Builder $RelationCriteria = null)
    {
        $RelationCriteria = $RelationCriteria ?: $this->getFactory()->buildCriteriaBuilder();
        
        $RelationCollection = new Helper\Collection([]);
        //buildDomainRelationMapper
        foreach ($Entity->getRelationDefinition() as $relation_domain_name => $relation_data) {
            $relation_data = $this->arrayMergeDefault([
                'type' => null,
                'column' => null,
                'mapped_by' => null,
                'inversed_by' => null,
                'virtual' => false,
            ], $relation_data);
            
            $RelationMapper = $this->getFactory()->buildDomainRelationMapper(
                $relation_data['type'], $relation_domain_name, $relation_data['column'], $relation_data['mapped_by'], $relation_data['inversed_by'], $relation_data['virtual']
            );

            $Relation = $this->getFactory()->buildDomainRelation($relation_domain_name, $Entity, $RelationMapper);
            $Relation->setCriteriaBuilder(clone $RelationCriteria);
            $Relation->setEntityRelationCriteria(clone $RelationCriteria);
            $RelationCollection->set($relation_domain_name, $Relation);
        }

        $Entity->setRelationCollection($RelationCollection);
    }
    
    /**
     * @inheritdoc
     */
    public function getMapper()
    {
        return $this->Mapper;
    }

    /**
     * @inheritdoc
     */
    public function setMapper(\Everon\Interfaces\DataMapper $Mapper)
    {
        $this->Mapper = $Mapper;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function buildFromArray(array $data, DataMapper\Interfaces\Criteria\Builder $RelationCriteria=null)
    {
        return $this->buildEntity($data, $RelationCriteria);
    }

    /**
     * @inheritdoc
     */
    public function persistFromArray(array $data, $user_id=null)
    {
        $Entity = $this->buildFromArray($data);
        $this->persist($Entity, $user_id);
        return $Entity;
    }

    /**
     * @inheritdoc
     */
    public function validateEntity(Interfaces\Entity $Entity)
    {
        return $this->getMapper()->getTable()->prepareDataForSql($Entity->toArray(), $Entity->isNew() === false);
    }

    /**
     * @inheritdoc
     */
    public function getEntityById($id, DataMapper\Interfaces\Criteria\Builder $RelationCriteria=null)
    {
        $CriteriaBuilder = $this->getFactory()->buildCriteriaBuilder();
        $CriteriaBuilder->where($this->getMapper()->getTable()->getPk(), '=', $id);
        
        return $this->getOneByCriteria($CriteriaBuilder, $RelationCriteria);
    }

    /**
     * @inheritdoc
     */
    public function getEntityByPropertyValue(array $property_criteria, DataMapper\Interfaces\Criteria\Builder $RelationCriteria = null)
    {
        if (empty($property_criteria)) {
            return null;
        }
        
        $CriteriaBuilder = $this->getFactory()->buildCriteriaBuilder();
        foreach ($property_criteria as $property => $value) {
            $CriteriaBuilder->where($property, '=', $value);
        }
        
        return $this->getOneByCriteria($CriteriaBuilder, $RelationCriteria);
    }

    /**
     * @inheritdoc
     */
    public function getOneByCriteria(DataMapper\Interfaces\Criteria\Builder $CriteriaBuilder, DataMapper\Interfaces\Criteria\Builder $RelationCriteriaBuilder = null)
    {
        $CriteriaBuilder->setLimit(1);
        $CriteriaBuilder->setOffset(0);
        
        $data = $this->getMapper()->fetchOneByCriteria($CriteriaBuilder);
        if (empty($data)) {
            return null;
        }

        return $this->buildEntity($data, $RelationCriteriaBuilder);
    }

    /**
     * @inheritdoc
     */
    public function getByCriteria(DataMapper\Interfaces\Criteria\Builder $CriteriaBuilder, DataMapper\Interfaces\Criteria\Builder $RelationCriteriaBuilder=null)
    {
        $data = $this->getMapper()->fetchAll($CriteriaBuilder);
        if (empty($data)) {
            return null;
        }
        
        $result = [];
        foreach ($data as $item) {
            $result[] = $this->buildEntity($item, $RelationCriteriaBuilder);
        }
        
        return $result;
    }
    
    /**
     * @inheritdoc
     */
    public function count(DataMapper\Interfaces\Criteria\Builder $CriteriaBuilder = null)
    {
        return $this->getMapper()->count($CriteriaBuilder);
    }

    /**
     * @inheritdoc
     */
    public function persist(Interfaces\Entity $Entity, $user_id=null)
    {
        if ($Entity->isDeleted()) {
            throw new \Everon\Exception\Domain('Invalid state when attempting to persist entity: "%s@%s"', [$Entity->getDomainName(), $Entity->getId()]);
        }
        
        $data = $this->validateEntity($Entity);
        //$data = $Entity->toArray();
        
        if ($Entity->isNew()) {
            $data = $this->getMapper()->add($data, $user_id);
        }
        else {
            $this->getMapper()->save($data, $user_id);
        }

        $data = $this->prepareDataForEntity($data, false);
        
        $Entity->persist($data);
    }

    /**
     * @inheritdoc
     */
    public function remove(Interfaces\Entity $Entity, $user_id=null)
    {
        if ($Entity->isNew() || $Entity->isDeleted()) {
            throw new \Everon\Exception\Domain('Invalid state when attempting to delete entity: "%s@%s"', [$Entity->getDomainName(), $Entity->getId()]);
        }
        
        $this->getMapper()->delete($Entity->getId(), $user_id);
        $Entity->delete();
    }

    /**
     * @inheritdoc
     */
    public function removeByCriteria(DataMapper\Interfaces\Criteria\Builder $CriteriaBuilder)
    {
        $this->getMapper()->deleteByCriteria($CriteriaBuilder);
    }

    /**
     * @inheritdoc
     */
    public function removeByPropertyValue(array $property_criteria)
    {
        if (empty($property_criteria)) {
            return;
        }

        $CriteriaBuilder = $this->getFactory()->buildCriteriaBuilder();
        foreach ($property_criteria as $property => $value) {
            $CriteriaBuilder->where($property, '=', $value);
        }

        $this->removeByCriteria($CriteriaBuilder);
    }

    /**
     * @inheritdoc
     */
    public function beginTransaction($point=null)
    {
        $this->getMapper()->getSchema()->getPdoAdapterByName($this->getMapper()->getWriteConnectionName())->beginTransaction();
        $this->getMapper()->getSchema()->getPdoAdapterByName($this->getMapper()->getWriteConnectionName())->execute('BEGIN TRANSACTION;');
        if ($point !== null) {
            $this->getLogger()->transaction('begin: '.$point);
        }
        else {
            $this->getLogger()->transaction('begin');
        }
    }

    /**
     * @inheritdoc
     */
    public function commitTransaction($point=null)
    {
        $this->getMapper()->getSchema()->getPdoAdapterByName($this->getMapper()->getWriteConnectionName())->commitTransaction();
        $this->getMapper()->getSchema()->getPdoAdapterByName($this->getMapper()->getWriteConnectionName())->execute('COMMIT;');
        if ($point !== null) {
            $this->getLogger()->transaction('commit: '.$point);
        }
        else {
            $this->getLogger()->transaction('commit');
        }
    }

    /**
     * @inheritdoc
     */
    public function rollbackTransaction($point=null)
    {
        $this->getMapper()->getSchema()->getPdoAdapterByName($this->getMapper()->getWriteConnectionName())->rollbackTransaction();
        $this->getMapper()->getSchema()->getPdoAdapterByName($this->getMapper()->getWriteConnectionName())->execute('ROLLBACK;');
        if ($point !== null) {
            $this->getLogger()->transaction('rollback: '.$point);
        }
        else {
            $this->getLogger()->transaction('rollback');
        }
    }
}