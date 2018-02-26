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


    public function getAnalytics($siteId, $analyticsKey = null)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->join('melis_cms_page_analytics_data_settings', 'melis_cms_page_analytics_data_settings.pads_site_id = melis_cms_page_analytics_data.pad_site_id',
            array('*'), $select::JOIN_LEFT);


        $select->where->equalTo('pad_site_id', (int) $siteId);

        if(!is_null($analyticsKey) && !empty($analyticsKey)) {
            $select->where->and->equalTo('pads_analytics_key', $analyticsKey);
        }

        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }


}