<?php
/**
 *	RabbitMQ controller class.
 *
 *	This class has most of the practical methods to CRUD essential parts
 *	of AMQP related elements.
 *
 *	It will allow you to maintain exchanges, queues and their bindings for
 *	the particular virtual host that is indicated in your config.ini file.
 *
 * 	The class follows a singleton pattern, since it is intended to be leveraged
 * 	mostly by web applications that will be out of its control.
 * 	See: http://www.ibm.com/developerworks/library/co-single/ 
 * 	and look for: "When it really is a singleton"
 *
 *	@author 	yhoebeke@benchmarkeducation.com
 *	@todo		complete documenting.
 */

interface RabbitMQ_template
{
	public function createExchange($strExchangeName);
	public function removeExchange($strExchangeName);
	public function createQueue($strQueueName, $strExchangeToBindTo, $strRoutingKey);
	public function removeQueue($strQueueName);
	public function purgeQueue($strQueueName);
	public function sendMessage($arrMessage);
}

class RabbitMQ_Controller implements RabbitMQ_template
{
	const CONFIG_FILE = 'config.ini';

	private static $instance;

	public $arrResult = array();
	public $username = '';

	private $password = '';
	private $hostname = '';
	private $port = 0;
	private $vhost = '';
	private $protocol = 'http://';
	private $requestURL = '';
	private $node = '';
	private $defaultMethod = 'GET';
	private $allowedMethods = array('GET','PUT','POST','DELETE');

	public static function getInstance()
	{
		if ( is_null( self::$instance ) )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *
	 * @internal 	Since this is a singleton pattern we only allow access 
	 *				to the constructor from the inside ( see: the method getInstance() ).
	 *
	 */
	private function __construct()
	{
		$arrConfigParams = parse_ini_file(self::CONFIG_FILE, false, INI_SCANNER_NORMAL);

		$this->username = $arrConfigParams['username'];
		$this->password = $arrConfigParams['password'];
		$this->vhost = ($arrConfigParams['vhost'] === '/') ? '' : $arrConfigParams['vhost'] . '/';
		$this->hostname = $arrConfigParams['hostname'];
		$this->port = $arrConfigParams['port'];
		$this->protocol = $arrConfigParams['protocol'];
		$this->requestURL = $this->protocol . $this->hostname . ':' . $this->port . '/api/';
		$this->node = $arrConfigParams['node'];
	}

	/**
	 *
	 * @internal 	Since this is a singleton pattern we do not allow 
	 *				cloning or unserialization.
	 */
	private function __clone(){}
	private function __wakeup(){}

	/**
	 *
	 * @internal 	Main routine to put all the pieces together and curl
	 *				the request to the server. 
	 *
	 * @todo 		Solidify return codes / error handling-notifications.
	 *
	 */
	private function pushToAPI($arrInterface)
	{
		$url = $arrInterface['url'];	
		$method = (isset($arrInterface['method']) === true) ? $arrInterface['method'] : $this->defaultMethod;
		
		echo 'Sending request to ' . $url . '<br />';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

		switch($method)
		{
			default:
			case 'GET':
				curl_setopt($ch, CURLOPT_HTTPGET, true);
				break;
			case 'PUT':
				curl_setopt($ch, CURLOPT_PUT, true);
				break;
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				break;
			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
		}

		if(isset($arrInterface['header']) === true)
		{
			if(count($arrInterface['header']) > 0)
			{
				curl_setopt($ch, CURLOPT_HTTPHEADER, $arrInterface['header']);
			}
		}

		if(isset($arrInterface['postfields']))
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $arrInterface['postfields']);
		}

		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$errs = curl_error($ch);
		/*
		echo '<hr />';
		var_dump($info);
		var_dump($errs);
		echo '<hr />';
		*/
		curl_close($ch);
		$this->arrResult = json_decode($output);

		return $this->arrResult;
	}

	/**
	 *
	 * @internal 	General request entry point.
	 *
	 */
	private function getRequest($arrRequest)
	{
		$arrReturnResult = array();
		$strRequest = $arrRequest['request'];

		if( isset( $arrRequest['vhost'] ) )
		{
			$strVhost = $arrRequest['vhost'];

			switch( $strRequest )
			{
				case 'users':
					$url = $this->requestURL . $strRequest;
					break;
				default:
					$url = $this->requestURL . $strRequest . '/' . $strVhost;
			}
		}
		else
		{
			$url = $this->requestURL . $strRequest;
		}
		
		$arrReturnResult['url'] = $url;
	
		$strMethod = $this->defaultMethod;

		if(isset($arrRequest['method']) === true)
		{
			$strMethod = $arrRequest['method'];

			if(in_array($strMethod, $this->allowedMethods) === true)
			{
				$arrReturnResult['method'] = $strMethod;
			}
		}

		return $arrReturnResult;
	}

	/**
	 *
	 * @internal 	Intermediat general request acceptor, will
	 *				transfer request to the main method. 
	 *
	 */
	public function processRequest($arrRequest)
	{
		$arrAPIRequest = $this->getRequest( $arrRequest );
		$this->pushToAPI( $arrAPIRequest );
	}

	/**
	 *
	 * @internal 	Creates an exchange.
	 *
	 * @todo 		Need to dynamically handle all possible parameters.
	 *
	 */
	public function createExchange($strExchangeName)
	{
		$arrExchangeProperties = array('type'=>'direct','auto_delete'=>false,'durable'=>true,'internal'=>false,arguments=>array());
		$jsonExchangeProperties = json_encode($arrExchangeProperties);

		$arrAPIRequest['method'] = 'PUT';
		$arrAPIRequest['header'][] = "content-type:application/json";
		$arrAPIRequest['postfields'] = $jsonExchangeProperties;

		$arrAPIRequest['url'] = $this->requestURL . 'exchanges/' . $this->vhost . $strExchangeName;

		$this->pushToAPI( $arrAPIRequest );
	}

	/**
	 *
	 * @internal 	Creates a queue and binds it to an exchange.
	 *
	 * @todo 		Need to dynamically handle all possible parameters.
	 *
	 */
	public function createQueue($strQueueName, $strExchangeToBindTo, $strRoutingKey)
	{
		$arrQueueProperties = array('auto_delete'=>false,'durable'=>true,'arguments'=>array(),'node'=>$this->node);
		$jsonQueueProperties = json_encode($arrQueueProperties);

		$arrAPIRequest['method'] = 'PUT';
		$arrAPIRequest['header'][] = "content-type:application/json";
		$arrAPIRequest['postfields'] = $jsonQueueProperties;

		$arrAPIRequest['url'] = $this->requestURL . 'queues/' . $this->vhost . $strQueueName;

		$this->pushToAPI( $arrAPIRequest );

		var_dump($this->arrResult);

		$this->createBinding($strQueueName, $strExchangeToBindTo, $strRoutingKey);
	}

	/**
	 *
	 * @internal 	Creates relationship between a queue and an exchange with a routing key.
	 *
	 */
	private function createBinding($strQueueName, $strExchange, $strRoutingKey)
	{
		$arrBindingProperties = array('routing_key'=>$strRoutingKey,'arguments'=>array());
		$jsonBindingProperties = json_encode($arrBindingProperties);

		$arrAPIRequest['method'] = 'POST';
		$arrAPIRequest['header'][] = "content-type:application/json";
		$arrAPIRequest['postfields'] = $jsonBindingProperties;

		$arrAPIRequest['url'] = $this->requestURL . 'bindings/' . $this->vhost . 'e/' . $strExchange . '/q/' . $strQueueName;

		$this->pushToAPI( $arrAPIRequest );
	}

	/**
	 *
	 * @internal 	Show existing bindings between queues and exchanges.
	 *
	 */
	public function showBindings($strExchange)
	{
		$arrAPIRequest['url'] = $this->requestURL . 'exchanges/' . $this->vhost . $strExchange . '/bindings/source';
		$this->pushToAPI( $arrAPIRequest);
	}

	/**
	 *
	 * @internal 	Sends a payload to designated queue.
	 *
	 */
	public function sendMessage($arrMessage)
	{
		$strToExchange = $arrMessage['exchange'];
		$strRoutingKey = $arrMessage['routing_key'];
		$strContent = $arrMessage['content'];


		$arrProperties = array();
		$objProperties = (object)$arrProperties;
		$arrMessageProperties = array('properties'=>$objProperties,'routing_key'=>$strRoutingKey,'payload'=>$strContent,'payload_encoding'=>'string');
		$jsonMessageProperties = json_encode($arrMessageProperties);

//		$jsonMessageProperties = '{"properties":{},"routing_key":"' . $strRoutingKey . '","payload":"' .  $strContent . '","payload_encoding":"string"}';

		$arrAPIRequest['method'] = 'POST';
		$arrAPIRequest['header'][] = "content-type:application/json";
		$arrAPIRequest['postfields'] = $jsonMessageProperties;

		$arrAPIRequest['url'] = $this->requestURL . 'exchanges/' . $this->vhost . $strToExchange . '/publish';

		var_dump($arrAPIRequest);
		echo '<hr />';
		$this->pushToAPI( $arrAPIRequest );
	}

	/**
	 *
	 * @internal 	Removes exchanges.
	 *
	 */
	public function removeExchange($strExchangeName)
	{
		$arrAPIRequest['method'] = 'DELETE';
		$arrAPIRequest['url'] = $this->requestURL . 'exchanges/' . $this->vhost . $strExchangeName;

		$this->pushToAPI( $arrAPIRequest );
	}

	/**
	 *
	 * @internal 	Removes queues.
	 *
	 */
	public function removeQueue($strQueueName)
	{
		$arrAPIRequest['method'] = 'DELETE';
		$arrAPIRequest['url'] = $this->requestURL . 'queues/' . $this->vhost . $strQueueName;

		$this->pushToAPI( $arrAPIRequest );
	}

	/**
	 *
	 * @internal 	Deletes messages from queues.
	 *
	 */
	public function purgeQueue($strQueueName)
	{
		// /api/queues/vhost/name/contents
		$arrAPIRequest['method'] = 'DELETE';
		$arrAPIRequest['url'] = $this->requestURL . 'queues/' . $this->vhost . $strQueueName . '/contents';

		$this->pushToAPI($arrAPIRequest);
	}

	/**
	 *
	 * @internal 	Creates virtual hosts.
	 *
	 * @todo 		Need to rethink this one.
	 */
	public function createVHost()
	{
	}
}
/* End of RabbitMQ.class.php */
