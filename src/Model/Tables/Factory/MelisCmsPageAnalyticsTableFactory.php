<?php
namespace MelisCmsPageAnalytics\Model\Tables\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ObjectProperty;
use MelisCmsPageAnalytics\Model\MelisCmsPageAnalytics;
use MelisCmsPageAnalytics\Model\Tables\MelisCmsPageAnalyticsTable;

class MelisCmsPageAnalyticsTableFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $hydratingResultSet = new HydratingResultSet(new ObjectProperty(), new MelisCmsPageAnalytics());
        $tableGateway       = new TableGateway('melis_cms_page_analytics', $sl->get('Zend\Db\Adapter\Adapter'), null, $hydratingResultSet);
        return new MelisCmsPageAnalyticsTable($tableGateway);
    }
}