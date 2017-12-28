<?php

namespace MelisCmsPageAnalytics\Model\Tables;

use MelisEngine\Model\Tables\MelisGenericTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;

class MelisCmsPageAnalyticsTable extends MelisGenericTable
{
    protected $tableGateway;
    protected $idField;

    public function __construct(TableGateway $tableGateway)
    {
        parent::__construct($tableGateway);
        $this->idField = 'ph_id';
    }   
    /**
     * 
     * @param string $search
     * @param array $searchableColumns
     * @param string $orderBy
     * @param string $orderDirection
     * @param int $start
     * @param null $limit
     * @return mixed
     */
    public function getData($search = '', $searchableColumns = [], $orderBy ='', $orderDirection = 'DESC', $start = 0, $limit = null)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('*','count' => new Expression('Count(ph_page_id)'),'last_date_visited' => new Expression('max(ph_date_visit)')) );
        $select->join('melis_cms_page_published', 'melis_cms_page_analytics.ph_page_id = melis_cms_page_published.page_id',
             array('page_name'), $select::JOIN_LEFT);
        $select->group('ph_page_id');

        if(!empty($searchableColumns) && !empty($search)) {
            foreach($searchableColumns as $column) {
                $select->where->or->like($column, '%'.$search.'%');
            }
        }  
        

        $getCount = $this->tableGateway->selectWith($select);
        // set current data count for pagination
        $this->setCurrentDataCount((int) $getCount->count());

        if(!is_null($limit) && ((int) $limit >= 1)) {
            $select->limit($limit);
        }

        if(!empty($start)) {
            $select->offset($start);
        }
        

        if(!empty($orderBy)) {
            $select->order($orderBy . ' ' . $orderDirection);
        }


        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }
     public function getDataByPageId($pageId, $search = '', $searchableColumns = [], $orderBy = 'ph_id', $orderDirection = 'DESC', $start = 0, $limit = null)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('*'));
        $select->where->equalTo('ph_page_id',$pageId);


        if(!empty($searchableColumns) && !empty($search)) {
            foreach($searchableColumns as $column) {
                $select->where->or->like($column, '%'.$search.'%');
            }
        }  

        $getCount = $this->tableGateway->selectWith($select);
        // set current data count for pagination
        $this->setCurrentDataCount((int) $getCount->count());

        if(!is_null($limit) && ((int) $limit >= 1)) {
            $select->limit($limit);
        }

        if(!empty($start)) {
            $select->offset($start);
        }
       
        if(!empty($orderBy)) {
            $select->order($orderBy . ' ' . $orderDirection);
        }

        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;

    }

    public function getDataBySessionAndPageId($pageId, $sessionCookie)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->where->equalTo('ph_page_id',$pageId)->and->equalTo('ph_session_id', $sessionCookie);

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

}