<?php

/**
 * AppserverIo\Routlt\PhtmlServletTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2015 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/routlt
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Routlt;

use AppserverIo\Http\HttpProtocol;
/**
 * This is test implementation for the PHTML servlet implementation.
 *
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2015 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/routlt
 * @link      http://www.appserver.io
 */
class PhtmlServletTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Tests the servlets init() method.
     *
     * @return void
     */
    public function testInit()
    {

        // create a servlet config mock instance
        $mockServletConfig = $this->getMockBuilder($servletConfigInterface = 'AppserverIo\Psr\Servlet\ServletConfigInterface')
            ->setMethods(get_class_methods($servletConfigInterface))
            ->getMock();

        // mock the necessary method
        $mockServletConfig->expects($this->once())
            ->method('getWebappPath')
            ->will($this->returnValue($webappPath = '/opt/appserver/webapps/test'));

        // create and initialize a servlet instance
        $servlet = new PhtmlServlet();
        $servlet->init($mockServletConfig);

        // check that the servlet has been initilized successfully
        $this->assertSame($webappPath, $servlet->getWebappPath());
        $this->assertSame(get_class($servlet), $servlet->getPoweredBy());
    }

    /**
     * This tests the service() method with a request, prepared with a servlet path.
     *
     * @return void
     */
    public function testServiceWithServletPath()
    {

        // initialize the controller with mocked methods
        $mockServlet = $this->getMockBuilder('AppserverIo\Routlt\PhtmlServlet')
            ->setMethods(array('getWebappPath', 'getPoweredBy'))
            ->getMock();

        // mock the necessary methods
        $mockServlet->expects($this->once())
            ->method('getWebappPath')
            ->will($this->returnValue(__DIR__));
        $mockServlet->expects($this->once())
            ->method('getPoweredBy')
            ->will($this->returnValue(get_class($mockServlet)));

        // create a mock servlet request instance
        $mockServletRequest = $this->getMockBuilder('AppserverIo\Appserver\ServletEngine\Http\Request')
            ->setMethods(array('getServletPath', 'hasHeader', 'getHeader'))
            ->getMock();

        // mock the necessary methods
        $mockServletRequest->expects($this->once())
            ->method('getServletPath')
            ->will($this->returnValue('/_files/my_template.phtml'));
        $mockServletRequest->expects($this->once())
            ->method('hasHeader')
            ->with(HttpProtocol::HEADER_X_POWERED_BY)
            ->will($this->returnValue(true));
        $mockServletRequest->expects($this->once())
            ->method('getHeader')
            ->with(HttpProtocol::HEADER_X_POWERED_BY)
            ->will($this->returnValue($poweredBy = 'AppserverIo\Routlt\ControllerServlet'));

        // create a mock servlet response instance
        $mockServletResponse = $this->getMockBuilder('AppserverIo\Appserver\ServletEngine\Http\Response')
            ->setMethods(array('addHeader', 'appendBodyStream'))
            ->getMock();

        // mock the necessary methods
        $mockServletResponse->expects($this->once())
            ->method('addHeader')
            ->with(HttpProtocol::HEADER_X_POWERED_BY, get_class($mockServlet) . ', ' . $poweredBy);
        $mockServletResponse->expects($this->once())
            ->method('appendBodyStream')
            ->with('Hello World!');

        // invoke the method we want to test
        $mockServlet->service($mockServletRequest, $mockServletResponse);
    }

    /**
     * This tests the service() method with a request, prepared with a missing PHTML file.
     *
     * @return void
     *
     * @expectedException AppserverIo\Psr\Servlet\ServletException
     * @expectedExceptionMessage Requested template '/_files/not_existing_template.phtml' is not available
     */
    public function testServiceWithMissingPhtmlFile()
    {

        // initialize the controller with mocked methods
        $mockServlet = $this->getMockBuilder('AppserverIo\Routlt\PhtmlServlet')
            ->setMethods(array('getWebappPath', 'getPoweredBy'))
            ->getMock();

        // mock the necessary methods
        $mockServlet->expects($this->once())
            ->method('getWebappPath')
            ->will($this->returnValue(__DIR__));
        $mockServlet->expects($this->once())
            ->method('getPoweredBy')
            ->will($this->returnValue(get_class($mockServlet)));

        // create a mock servlet request instance
        $mockServletRequest = $this->getMockBuilder('AppserverIo\Appserver\ServletEngine\Http\Request')
            ->setMethods(array('getServletPath', 'hasHeader'))
            ->getMock();

        // mock the necessary methods
        $mockServletRequest->expects($this->once())
            ->method('getServletPath')
            ->will($this->returnValue('/_files/not_existing_template.phtml'));
        $mockServletRequest->expects($this->once())
            ->method('hasHeader')
            ->with(HttpProtocol::HEADER_X_POWERED_BY)
            ->will($this->returnValue(false));

        // create a mock servlet response instance
        $mockServletResponse = $this->getMockBuilder('AppserverIo\Appserver\ServletEngine\Http\Response')
            ->setMethods(array('addHeader', 'appendBodyStream'))
            ->getMock();

        // mock the necessary methods
        $mockServletResponse->expects($this->once())
            ->method('addHeader')
            ->with(HttpProtocol::HEADER_X_POWERED_BY, get_class($mockServlet));

        // invoke the method we want to test
        $mockServlet->service($mockServletRequest, $mockServletResponse);
    }
}
