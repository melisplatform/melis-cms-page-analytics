<?php

namespace MelisCmsPageAnalytics\Model\Tables;

use MelisEngine\Model\Tables\MelisGenericTable;
use Laminas\Db\Sql\Expression;

class MelisCmsPageAnalyticsDataTable extends MelisGenericTable
{
    /**
     * Model table
     */
    const TABLE = 'melis_cms_page_analytics_data';

    /**
     * Table primary key
     */
    const PRIMARY_KEY = 'pad_id';

    public function __construct()
    {
        $this->idField = self::PRIMARY_KEY;
    }


    public function getAnalytics($siteId, $analyticsKey = null)
    {
        $select = $this->getTableGateway()->getSql()->select();

        $select->join('melis_cms_page_analytics_data_settings', 'melis_cms_page_analytics_data_settings.pads_site_id = melis_cms_page_analytics_data.pad_site_id',
            array('*'), $select::JOIN_LEFT);


        $select->where->equalTo('pad_site_id', (int) $siteId);

        if(!is_null($analyticsKey) && !empty($analyticsKey)) {
            $select->where->and->equalTo('pads_analytics_key', $analyticsKey);
        }

        $resultSet = $this->getTableGateway()->selectWith($select);

        return $resultSet;
    }


}