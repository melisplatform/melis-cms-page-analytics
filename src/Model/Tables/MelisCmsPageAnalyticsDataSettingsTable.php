<?php

namespace MelisCmsPageAnalytics\Model\Tables;

use MelisEngine\Model\Tables\MelisGenericTable;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Expression;

class MelisCmsPageAnalyticsDataSettingsTable extends MelisGenericTable
{
    /**
     * Model table
     */
    const TABLE = 'melis_cms_page_analytics_data_settings';

    /**
     * Table primary key
     */
    const PRIMARY_KEY = 'pads_id';

    public function __construct()
    {
        $this->idField = self::PRIMARY_KEY;
    }
}