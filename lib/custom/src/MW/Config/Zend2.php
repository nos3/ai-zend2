<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2014
 * @package MW
 * @subpackage Config
 */


/**
 * Configuration setting class using ZF2 Config
 *
 * @package MW
 * @subpackage Config
 */
class MW_Config_Zend2
	extends MW_Config_Abstract
	implements MW_Config_Interface
{
	private $_config;
	private $_paths;


	/**
	 * Initialize config object with ZF2 Config instance
	 *
	 * @param Zend\Config\Config $config Configuration object
	 * @param array|string $path Filesystem path or list of paths to the configuration files
	 */
	public function __construct( Zend\Config\Config $config, $path = array() )
	{
		$this->_config = $config;
		$this->_paths = (array) $path;
	}


	/**
	 * Clones the objects inside.
	 */
	public function __clone()
	{
		$this->_config = clone $this->_config;
	}


	/**
	 * Returns the value of the requested config key.
	 *
	 * @param string $path Path to the requested value like tree/node/classname
	 * @param mixed $default Value returned if requested key isn't found
	 * @return mixed Value associated to the requested key or default value if no value in configuration was found
	 */
	public function get( $path, $default = null )
	{
		$parts = explode( '/', trim( $path, '/' ) );

		if( ( $value = $this->_get( $this->_config, $parts ) ) !== null ) {
			return $value;
		}

		foreach( $this->_paths as $fspath ) {
			$this->_load( $this->_config, $fspath, $parts );
		}

		if( ( $value = $this->_get( $this->_config, $parts ) ) !== null ) {
			return $value;
		}

		return $default;
	}


	/**
	 * Sets the value for the specified key.
	 *
	 * @param string $path Path to the requested value like tree/node/classname
	 * @param mixed $value Value that should be associated with the given path
	 */
	public function set( $path, $value )
	{
		$parts = explode( '/', trim( $path, '/' ) );

		$config = $this->_config;
		$max = count( $parts ) - 1;

		for( $i = 0; $i < $max; $i++ )
		{
			$val = $config->get( $parts[$i] );

			if( $val instanceof Zend\Config\Config ) {
				$config = $val;
			} else {
				$config = $config->{$parts[$i]} = new Zend\Config\Config( array(), true );
			}
		}

		$config->{$parts[$max]} = $value;
	}


	/**
	 * Descents into the configuration specified by the given path and returns the value if found.
	 *
	 * @param Zend\Config\Config $config Configuration object which should contain the loaded configuration
	 * @param array $parts List of config name parts to look for
	 * @return mixed Found value or null if no value is available
	 */
	protected function _get( Zend\Config\Config $config, array $parts )
	{
		if( ( $key = array_shift( $parts ) ) !== null && isset( $config->$key ) )
		{
			if( $config->$key instanceof Zend\Config\Config )
			{
				if( count( $parts  ) > 0 ) {
					return $this->_get( $config->$key, $parts );
				}

				return $config->$key->toArray();
			}

			return $config->$key;
		}

		return null;
	}


	/**
	 * Loads the configuration files when found.
	 *
	 * @param Zend\Config\Config $config Configuration object which should contain the loaded configuration
	 * @param string $path Path to the configuration directory
	 * @param array $parts List of config name parts to look for
	 */
	protected function _load( Zend\Config\Config $config, $path, array $parts )
	{
		if( ( $key = array_shift( $parts ) ) !== null )
		{
			$newPath = $path . DIRECTORY_SEPARATOR . $key;

			if( is_dir( $newPath ) )
			{
				if( !isset( $config->$key ) ) {
					$config->$key = new Zend\Config\Config( array(), true );
				}

				$this->_load( $config->$key, $newPath, $parts );
			}

			if( file_exists( $newPath . '.php' ) )
			{
				if( !isset( $config->$key ) ) {
					$config->$key = new Zend\Config\Config( array(), true );
				}

				$config->$key->merge( new Zend\Config\Config( $this->_include( $newPath . '.php' ), true ) );
			}
		}
	}

}