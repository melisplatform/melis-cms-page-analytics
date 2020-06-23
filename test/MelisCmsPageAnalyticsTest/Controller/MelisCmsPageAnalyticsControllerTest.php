<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCmsPageAnalyticsTest\Controller;

use MelisCore\ServiceManagerGrabber;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
class MelisCmsPageAnalyticsControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = false;
    protected $sm;
    protected $method = 'fetchAll';

    const SUCCESS = 1;
    public function setUp()
    {
        $this->sm  = new ServiceManagerGrabber();
    }

        /**
     * Get getAnalyticsTable table
     * @return mixed
     */
    private function getAnalyticsTable()
    {
        $conf = $this->sm->getPhpUnitTool()->getTable('MelisCmsPageAnalytics', __METHOD__);
        return $this->sm->getTableMock(new $conf['model'], $conf['model_table'], $conf['db_table_name'], $this->method);
    }


    public function getPayload($method)
    {
        return $this->sm->getPhpUnitTool()->getPayload('MelisCmsPageAnalytics', $method);
    }

    /**
     * START ADDING YOUR TESTS HERE
     */

    public function testAddDataOnPage()
    {
        $payloads = $this->getPayload(__METHOD__);
        $pageId   = (int) $payloads['page_id'];
        $data     = array(
            'ph_id'         => null,
            'ph_page_id'    => $pageId,
            'ph_session_id' => 'PHPUNIT-SESSID-'.substr(time(), 5),
            'ph_date_visit' => date('Y-m-d H:i:s'),
        );

        $result = (int) $this->getAnalyticsTable()->save($data);

        // convert returned ID into 1 to check if have successfully saved the data.
        if($result)
            $result = 1;

        $this->assertEquals($result, self::SUCCESS);
    }

}

