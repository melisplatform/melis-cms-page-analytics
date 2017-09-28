<?php

namespace MelisCmsPageAnalytics\Model\Tables;

use MelisEngine\Model\Tables\MelisGenericTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;

class MelisCmsPageAnalyticsDataTable extends MelisGenericTable
{
    protected $tableGateway;
    protected $idField;

    public function __construct(TableGateway $tableGateway)
    {
        parent::__construct($tableGateway);
        $this->idField = 'pad_id';
    }

    public function getCurrentAnalyticsData()
    {
        $select = $this->tableGateway->getSql()->select();

        $select->join('melis_cms_page_analytics_settings', 'melis_cms_page_analytics_settings.pas_analytics = melis_cms_page_analytics_data.pad_current_analytics', array('*'), $select::JOIN_LEFT);


        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;

    }
}