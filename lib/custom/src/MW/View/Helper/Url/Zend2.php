<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2014
 * @package MW
 * @subpackage View
 */


/**
 * View helper class for building URLs using Zend2 Router.
 *
 * @package MW
 * @subpackage View
 */
class MW_View_Helper_Url_Zend2
	extends MW_View_Helper_Abstract
	implements MW_View_Helper_Interface
{
	private $_router;
	private $_serverUrl;


	/**
	 * Initializes the URL view helper.
	 *
	 * @param MW_View_Interface $view View instance with registered view helpers
	 * @param Zend\Mvc\Router\RouteInterface $router Zend Router implementation
	 * @param string $serverUrl Url of the server including scheme, host and port
	 */
	public function __construct( $view, \Zend\Mvc\Router\RouteInterface $router, $serverUrl )
	{
		parent::__construct( $view );

		$this->_router = $router;
		$this->_serverUrl = $serverUrl;
	}


	/**
	 * Returns the URL assembled from the given arguments.
	 *
	 * @param string|null $target Route or page which should be the target of the link (if any)
	 * @param string|null $controller Name of the controller which should be part of the link (if any)
	 * @param string|null $action Name of the action which should be part of the link (if any)
	 * @param array $params Associative list of parameters that should be part of the URL
	 * @param array $trailing Trailing URL parts that are not relevant to identify the resource (for pretty URLs)
	 * @param array $config Additional configuration parameter per URL
	 * @return string Complete URL that can be used in the template
	 */
	public function transform( $target = null, $controller = null, $action = null, array $params = array(), array $trailing = array(), array $config = array() )
	{
		$paramList = array( 'module' => $target, 'controller' => $controller, 'action' => $action );


		foreach( $params as $key => $value )
		{
			// Slashes in URL parameters confuses the router
			$paramList[$key] = str_replace( '/', '', $value );

			// Arrays are not supported
			if( is_array( $value ) ) {
				$paramList[$key] = implode( ' ', $value );
			}
		}

		if( !empty( $trailing ) ) {
			$paramList['trailing'] = str_replace( '/', '-', join( '-', $trailing ) );
		}

		$url = $this->_router->assemble( $paramList, array() );

		if( isset( $config['absoluteUri'] ) ) {
			$url = $this->_serverUrl . $url;
		}

		return $url;
	}
}