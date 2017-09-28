<?php
namespace MelisCmsPageAnalytics\Model\Tables\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ObjectProperty;
use MelisCmsPageAnalytics\Model\MelisCmsPageAnalyticsSettings;
use MelisCmsPageAnalytics\Model\Tables\MelisCmsPageAnalyticsSettingsTable;

class MelisCmsPageAnalyticsSettingsTableFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $hydratingResultSet = new HydratingResultSet(new ObjectProperty(), new MelisCmsPageAnalyticsSettings());
        $tableGateway       = new TableGateway('melis_cms_page_analytics_settings', $sl->get('Zend\Db\Adapter\Adapter'), null, $hydratingResultSet);
        return new MelisCmsPageAnalyticsSettingsTable($tableGateway);
    }
}