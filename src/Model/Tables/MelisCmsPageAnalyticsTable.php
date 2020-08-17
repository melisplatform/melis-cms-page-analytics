<?php

namespace MelisCmsPageAnalytics\Model\Tables;

use MelisEngine\Model\Tables\MelisGenericTable;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Where;

class MelisCmsPageAnalyticsTable extends MelisGenericTable
{
    /**
     * Model table
     */
    const TABLE = 'melis_cms_page_analytics';

    /**
     * Table primary key
     */
    const PRIMARY_KEY = 'ph_id';

    public function __construct()
    {
        $this->idField = self::PRIMARY_KEY;
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
    public function getData($search = '', $searchableColumns = [], $orderBy = '', $orderDirection = 'DESC', $start = 0, $limit = null, $siteId = null)
    {
        $select = $this->getTableGateway()->getSql()->select();
        $select->columns(array('*', 'count' => new Expression('Count(ph_page_id)'), 'last_date_visited' => new Expression('max(ph_date_visit)')));
        $select->join('melis_cms_page_published', 'melis_cms_page_analytics.ph_page_id = melis_cms_page_published.page_id',
            array('page_name'), $select::JOIN_LEFT);
        $select->group('ph_page_id');

        if (!empty($searchableColumns) && !empty($search)) {
            foreach ($searchableColumns as $column) {
                $select->where->or->like($column, '%' . $search . '%');
            }
        }

        if (!empty($siteId)) {
            $select->where->equalTo('ph_site_id', $siteId);
        }

        $getCount = $this->getTableGateway()->selectWith($select);
        // set current data count for pagination
        $this->setCurrentDataCount((int)$getCount->count());

        if (!is_null($limit) && ((int)$limit >= 1)) {
            $select->limit($limit);
        }

        if (!empty($start)) {
            $select->offset($start);
        }

        if (!empty($orderBy)) {
            $select->order($orderBy . ' ' . $orderDirection);
        }

        $resultSet = $this->getTableGateway()->selectWith($select);

        return $resultSet;
    }

    public function getDataByPageId($pageId, $search = '', $searchableColumns = [], $orderBy = 'ph_id', $orderDirection = 'DESC', $start = 0, $limit = null)
    {
        $select = $this->getTableGateway()->getSql()->select();
        $select->columns(array('*'));

        $where = new Where();
        $where->equalTo('ph_page_id', $pageId);

        if (!empty($searchableColumns) && !empty($search)) {
            $nest = $where->and->nest();
            foreach ($searchableColumns as $column) {
                // nest function creates a nested query like so:
                // ...AND (`ph_id` LIKE '%$search%' OR `ph_date_visit` LIKE '%$search%')
                $nest->like($column, '%' . $search . '%')->or;
            }
        }
        $select->where($where);

        $getCount = $this->getTableGateway()->selectWith($select);
        // set current data count for pagination
        $this->setCurrentDataCount((int)$getCount->count());

        if (!is_null($limit) && ((int)$limit >= 1)) {
            $select->limit($limit);
        }

        if (!empty($start)) {
            $select->offset($start);
        }

        if (!empty($orderBy)) {
            $select->order($orderBy . ' ' . $orderDirection);
        }

        $resultSet = $this->getTableGateway()->selectWith($select);
        return $resultSet;

    }

    public function getDataBySessionAndPageId($pageId, $sessionCookie)
    {
        $select = $this->getTableGateway()->getSql()->select();

        $select->where->equalTo('ph_page_id', $pageId)->and->equalTo('ph_session_id', $sessionCookie);

        $resultSet = $this->getTableGateway()->selectWith($select);

        return $resultSet;
    }

}
