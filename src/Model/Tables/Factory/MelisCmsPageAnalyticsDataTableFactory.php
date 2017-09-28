<?php
namespace MelisCmsPageAnalytics\Model\Tables\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ObjectProperty;
use MelisCmsPageAnalytics\Model\MelisCmsPageAnalyticsData;
use MelisCmsPageAnalytics\Model\Tables\MelisCmsPageAnalyticsDataTable;

class MelisCmsPageAnalyticsDataTableFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $hydratingResultSet = new HydratingResultSet(new ObjectProperty(), new MelisCmsPageAnalyticsData());
        $tableGateway       = new TableGateway('melis_cms_page_analytics_data', $sl->get('Zend\Db\Adapter\Adapter'), null, $hydratingResultSet);
        return new MelisCmsPageAnalyticsDataTable($tableGateway);
    }
}