<?php
/**
 * index.php
 *
 * @author 	Yves Hoebeke - yhoebeke@benchmarkeducation.com
 *
 * @todo 	Continue adding comments to this
 *
 */

/**
 * Include my Rabbit class and instanciate it
 */
require_once 'assets/lib/RabbitMQ.class.php';
$myRabbit = RabbitMQ_Controller::getInstance();

/**
 * A couple of helper functions who facilitate navigation
 * to a certain spot after user has been given a return from
 * certain methods in the Rabbit class.
 */
function returnToMenu()
{
	?>
	<div>
	<form name="returntomenu" action="<?php $_SERVER['PHP_SELF']?>" method="post">
			<input type="submit" name="strMode" value="Return to Menu" />
		</form>
	</div>
	<?php
}

function returnToSelection($strMode)
{
	?>
	<form id="returntoselection" name="returntoselection" action="<?php $_SERVER['PHP_SELF']?> method="post">
		<input type="text" name="strMode" value="<?php echo $strMode;?>" />
	</form>
	<script type="text/javascript">document.getElementById('returntoselection').submit();</script>
	<?php
}
/**
 * Starting the HTML here
 */
?>

<!DOCTYPE html>
<html>

<header>
	<title>ReabbitMQ Center</title>
	<link rel="shortcut icon" href="assets/img/favicon.png">
	<link rel="stylesheet" type="text/css" href="assets/css/carrot.css">
	<script src="assets/js/carrot.js"></script>
</header>

<body onload="makeActive()">
<div>
	<img style="float:left;margin:4px;cursor:pointer" src="assets/img/rabbit-with-carrot.jpg" alt="carrot" width="67" height="70" onclick="window.location.href='carrot.php';" />
	<h3><span style="color:#E05510">CARROT</span> - RabbitMQ Center</h3>
	<p class="username"><?php echo "Logged in as: ".$myRabbit->username;?></p>
	<p class="datetime"><?php echo date('r');?></p>
</div>
<?php
/**
 * The mode variable indicates what we will be doing next.
 */
$strMode = (isset($_POST['strMode']) === true) ? $_POST['strMode'] : "";

switch($strMode)
{
	default:
		?>
		<h4>Menu</h4>
		<form id="menu_form" action="<?php $_SERVER['PHP_SELF']?>" method="post">
			<ul class="main_menu">
				<li class="menu_item" id="user_info">Get User Info</li>
				<li class="menu_item" id="vhost_info">Get vhost Info</li>
				<li class="menu_separator">----------------------------------------</li>
				<li class="menu_item" id="channel_info">List Channels</li>
				<li class="menu_item" id="binding_info">List Bindings</li>
				<!-- <li class="menu_item" id="connections_info">List Connections</li> -->
				<li class="menu_separator">----------------------------------------</li>
				<li class="menu_item" id="exchange_info">List (and Remove) Exchanges</li>
				<li class="menu_item" id="setup_exchange">Create an Exchange</li>
				<li class="menu_separator">----------------------------------------</li>
				<li class="menu_item" id="queue_info">List (and Purge/Remove) Queues</li>
				<li class="menu_item" id="setup_queue">Create a Queue</li>
				<li class="menu_separator">----------------------------------------</li>
				<li class="menu_item" id="send_message">Send a Message</li>
				<li class="menu_separator">----------------------------------------</li>
				<li class="menu_item" id="readme">Read me</li>
			</ul>
			<input id="mode" type="hidden" name="strMode" value="" />
		</form>
		<?php
		break;

	case 'user_info':
		$myRabbit->processRequest(array('request'=>'permissions'));
		?>
		<h4>User Info</h4>
		<table border="1">
			<tr><th colspan="2">Users</th><th colspan="3" style="text-align:center">Permissions</th></tr>
			<tr><th>Name</th><th>vhost</th><th>configure</th><th>read</th><th>write</th></tr>
				<?php
				foreach($myRabbit->arrResult as $user)
				{
					echo '<tr><td>' . $user->user . '</td><td>' . $user->vhost . '</td><td>' . $user->configure . '</td><td>' . $user->read . '</td><td>' . $user->write . '</td></tr>';
				}
				?>
			<tr><th colspan="5" style="text-align:center"><?php returnToMenu()?></th></tr>
		</table>
		<?php
		break;
		
	case 'vhost_info':
		$myRabbit->processRequest(array('request'=>'vhosts'));
		?>
		<h4>User Info</h4>
		<table border="1">
			<tr><th>Vhost</th><th>tracing</th></tr>
				<?php
				foreach($myRabbit->arrResult as $intIndex => $vhost)
				{
					if($intIndex === 0)	continue;
					$tracing = ($vhost->tracing === false) ? 'false' : 'true';
					echo '<tr><td>' . $vhost->name . '</td><td>' . $tracing . '</td></tr>';
				}
				?>
			<tr><th colspan="2" style="text-align:center"><?php returnToMenu()?></th></tr>
		</table>
		<?php
		break;
		
	case 'channel_info':
		$myRabbit->processRequest(array('request'=>'channels'));
		?>
		<h4>Channel List</h4>
		<table border="1">
			<tr>
				<th>ID</th>
				<th>peer port</th>
				<th>peer host</th>
				<th>idle Since</th>
				<th>transactional</th>
				<th>confirm</th>
				<th>consumer cnt</th>
				<th>unack msg</th>
				<th>unconf msg</th>
				<th>uncommit msg</th>
				<th>uncommit acks</th>
				<th>state</th>
				<th>node</th>
				<th>number</th>
				<th>user</th>
				<th>vhost</th>
			</tr>
				<?php
				foreach($myRabbit->arrResult as $intIndex => $channel)
				{
					 $transactional = ($channel->transactional === false) ? 'false' : 'true';
					 $confirm = ($channel->confirm === false) ? 'false' : 'true';

					 echo '<tr>';
						 echo '<td>' . $channel->connection_details->name . '</td>';
						 echo '<td>' . $channel->connection_details->peer_port . '</td>';
						 echo '<td>' . $channel->connection_details->peer_host . '</td>';
						 echo '<td>' . $channel->idle_since . '</td>';
						 echo '<td>' . $transactional . '</td>';
						 echo '<td>' . $confirm . '</td>';
						 echo '<td>' . $channel->consumer_count . '</td>';
						 echo '<td>' . $channel->messages_unacknowledged . '</td>';
						 echo '<td>' . $channel->messages_unconfirmed . '</td>';
						 echo '<td>' . $channel->messages_uncommitted . '</td>';
						 echo '<td>' . $channel->acks_uncommitted . '</td>';
						 echo '<td>' . $channel->state . '</td>';
						 echo '<td>' . $channel->node . '</td>';
						 echo '<td>' . $channel->number . '</td>';
						 echo '<td>' . $channel->user . '</td>';
						 echo '<td>' . $channel->vhost . '</td>';
					 echo '</tr>';
				}
				?>
			<tr><th colspan="16" style="text-align:center"><?php returnToMenu()?></th></tr>
		</table>
		<?php
		break;

  	case 'exchange_info':
		$myRabbit->processRequest(array('request'=>'exchanges'));
		?>
		<h4>Exchange List</h4>
		<table border="1">
			<tr>
				<th>Name</th>
				<th>vhost</th>
				<th>type</th>
				<th>durable</th>
				<th>auto_delete</th>
				<th>internal</th>
				<th>arguments</th>
				<th style="color:red">X</th>
			</tr>
				<?php
				foreach($myRabbit->arrResult as $intIndex => $exchange)
				{
					 $durable = ($exchange->durable === false) ? 'false' : 'true';
					 $auto_delete = ($exchange->auto_delete  === false) ? 'false' : 'true';
					 $internal = ($exchange->internal  === false) ? 'false' : 'true';

					 echo '<tr>';
						 echo '<td>' . $exchange->name . '</td>';
						 echo '<td>' . $exchange->vhost . '</td>';
						 echo '<td>' . $exchange->type . '</td>';
						 echo '<td>' . $durable . '</td>';
						 echo '<td>' . $auto_delete . '</td>';
						 echo '<td>' . $internal . '</td>';
						 echo '<td>' . $channel->arguments . '</td>';
						 echo '<td id="' . $exchange->name . '" class="delete_exchange"></td>';
					 echo '</tr>';
				}
				?>
			<tr><th colspan="8" style="text-align:center"><?php returnToMenu()?></th></tr>
		</table>
		<form id="delete_exchange_form" name="delete_exchange_form" action="<?php $_SERVER['PHP_SELF']?>" method="post">
			<input type="hidden" id="delete_exchange_id" name="delete_exchange" value="" />
			<input type="hidden" name="strMode" value="delete_exchange" />
		</form>
		<?php
		break;

	case 'delete_exchange':
		$myRabbit->removeExchange($_POST['delete_exchange']);
		returnToSelection('exchange_info');
		break;

  	case 'queue_info':
		$myRabbit->processRequest(array('request'=>'queues/vhost_yves'));
		?>
		<h4>Queue List</h4>
		<table border="1">
			<tr>
				<th>Name</th>
				<th>vhost</th>
				<th>durable</th>
				<th>auto_delete</th>
				<th>state</th>
				<th>messages</th>
				<th>messages ready</th>
				<th>idle since</th>
				<th style="color:red">X</th>
				<th style="color:red">P</th>
			</tr>
				<?php

				foreach($myRabbit->arrResult as $intIndex => $queue)
				{
					 $durable = ($queue->durable === false) ? 'false' : 'true';
					 $auto_delete = ($queue->auto_delete  === false) ? 'false' : 'true';

					 echo '<tr>';
						 echo '<td>' . $queue->name . '</td>';
						 echo '<td>' . $queue->vhost . '</td>';
						 echo '<td>' . $durable . '</td>';
						 echo '<td>' . $auto_delete . '</td>';
						 echo '<td>' . $queue->state . '</td>';
						 echo '<td>' . $queue->messages . '</td>';
						 echo '<td>' . $queue->messages_ready . '</td>';
						 echo '<td>' . $queue->idle_since . '</td>';
						 echo '<td id="' . $queue->name . '" class="delete_queue"></td>';
						 echo '<td id="' . $queue->name . '" class="purge_queue"></td>';
					 echo '</tr>';
				}
				?>
			<tr><th colspan="10" style="text-align:center"><?php returnToMenu()?></th></tr>
		</table>
		<form id="delete_queue_form" name="delete_queue_form" action="<?php $_SERVER['PHP_SELF']?>" method="post">
			<input type="hidden" id="delete_queue_id" name="delete_queue" value="" />
			<input type="hidden" name="strMode" value="delete_queue" />
		</form>
		<form id="purge_queue_form" name="purge_queue_form" action="<?php $_SERVER['PHP_SELF']?>" method="post">
			<input type="hidden" id="purge_queue_id" name="purge_queue" value="" />
			<input type="hidden" name="strMode" value="purge_queue" />
		</form>
		<?php
		break;

	case 'delete_queue':
		$myRabbit->removeQueue($_POST['delete_queue']);
		returnToSelection('queue_info');
		break;

	case 'purge_queue':
		$myRabbit->purgeQueue($_POST['purge_queue']);
		returnToSelection('queue_info');
		break;

	case 'binding_info':
		$myRabbit->processRequest(array('request'=>'exchanges'));
		?>
		<h4>Binding Info</h4>
		<form id="select_exchange_binding" action="<?php $_SERVER['PHP_SELF']?>" method="post">
		<table border="1">
			<tr>
				<th style-"text-align:right">Select an Exchange:</th>
				<td>
					<select name="selected_exchange">
						<?php
						foreach($myRabbit->arrResult as $intIndex => $exchange)
						{
							if(strlen(trim($exchange->name)) > 0 )
							{
								echo '<option value="' . $exchange->name . '">' . $exchange->name . '</option>';
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th colspan="2" style="text-align:center"><input type="submit" name="strMode" value="Show Bindings" /></th>
			</tr>
		</table>
		</form>
		<?php
		break;

	case 'Show Bindings':
		$myRabbit->showBindings($_POST['selected_exchange']);
		?>
		<h4>Binding Info</h4>
		<table border="1">
			<tr>
				<th>Source</th>
				<th>vhost</th>
				<th>destination</th>
				<th>destination type</th>
				<th>routing key</th>
			</tr>
				<?php

				foreach($myRabbit->arrResult as $binding)
				{
					 echo '<tr>';
						 echo '<td>' . $binding->source . '</td>';
						 echo '<td>' . $binding->vhost . '</td>';
						 echo '<td>' . $binding->destination . '</td>';
						 echo '<td>' . $binding->destination_type . '</td>';
						 echo '<td>' . $binding->routing_key . '</td>';
					 echo '</tr>';
				}
				?>
			<tr><th colspan="5" style="text-align:center"><?php returnToMenu()?></th></tr>
		</table>
		<?php
		break;

	case 'connections_info':
		$myRabbit->processRequest(array('request'=>'connections'));
		var_dump($myRabbit->arrResult);
		exit();
		break;

	case 'setup_queue':
		$myRabbit->processRequest(array('request'=>'exchanges'));
		?>
		<h4>Create Queue</h4>
		<form id="create_queue" action="<?php $_SERVER['PHP_SELF']?>" method="post">
		<table border="1">
			<tr>
				<th style-"text-align:right">Queue name:</th>
				<td><input type="text" name="new_queue" value ="" /></td>
			</tr>
			<tr>
				<th style-"text-align:right">Bind to Exchange:</th>
				<td>
					<select name="bindToExchange">
						<?php
						foreach($myRabbit->arrResult as $intIndex => $exchange)
						{
							if(strlen(trim($exchange->name)) > 0 )
							{
								echo '<option value="' . $exchange->name . '">' . $exchange->name . '</option>';
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th style-"text-align:right">Routing Key:</th>
				<td><input type="text" name="routing_key" value ="" /></td>
			</tr>
			<tr>
				<th colspan="2" style="text-align:center"><input type="submit" name="strMode" value="Create Queue" /></th>
			</tr>
			<tr>
				<th colspan="2" style="text-align:center"><?php returnToMenu();?></th>
			</tr>
		</table>
		</form>
		<?php
		break;
		
	case 'Create Queue':
		$myRabbit->createQueue($_POST['new_queue'], $_POST['bindToExchange'], $_POST['routing_key']);
		returnToMenu();
		break;

	case 'setup_exchange':
		?>
		<h4>Create Queue</h4>
		<form id="create_exchange" action="<?php $_SERVER['PHP_SELF']?>" method="post">
		<table border="1">
			<tr>
				<th>Exchange name:</th>
				<td><input type="text" name="new_exchange" value ="" /></td>
			</tr>
			<tr>
				<th colspan="2" style="text-align:center"><input type="submit" name="strMode" value="Create Exchange" /></th>
			</tr>
			<tr>
				<th colspan="2" style="text-align:center"><?php returnToMenu();?></th>
			</tr>
		</table>
		</form>
		<?php
		break;
		
	case 'Create Exchange':
		$myRabbit->createExchange($_POST['new_exchange']);
		returnToMenu();
		break;

	case 'send_message':
		$myRabbit->processRequest(array('request'=>'exchanges'));
		?>
		<h4>Send Message</h4>
		<form id="send_message" name="send_message" action="<?php $_SERVER['PHP_SELF']?>" method="post">
		<table border="1">
			<tr>
				<th style="text-align:right">To Exchange:</th>
				<td>
					<select name="to_exchange">
						<?php
						foreach($myRabbit->arrResult as $intIndex => $exchange)
						{
							if(strlen(trim($exchange->name)) > 0 )
							{
								echo '<option value="' . $exchange->name . '">' . $exchange->name . '</option>';
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr><th style="text-align:right">Routing Key:</th><td><input type="text" name="routing_key" value="" /></td></tr>
			<tr><th colspan="2" style="text-align:left">Payload:</th></tr>
			<tr><td colspan="2"><textarea id="carrot-message" name="message_content" cols="100" rows="10"></textarea></td></tr>
			<tr><th colspan="2" style="text-align:center"><input type="submit" name="strMode" value="Send" /></th></tr>
			<tr><th colspan="2" style="text-align:center"><?php returnToMenu();?></th></tr>
			<tr>
				<td colspan="2" style="text-align:center;background-color:#F5F2A2;color:maroon">
					<p>Please note that the intent of this is to test and experiment with messages.</p>
					<p>This is by no means intended to deal with quantities and volumes encountered in a production environment.</p>
				</td>
			</tr>
		</table>
		</form>			
		<?php
		break;
		
	case 'Send':
		$arrMessageAttributes = array('exchange'=>$_POST['to_exchange'], 'content'=>$_POST['message_content'], 'routing_key'=>$_POST['routing_key']);
		$myRabbit->sendMessage($arrMessageAttributes);

		returnToSelection('send_message');
		break;

	case 'readme':
		$strReadme = file_get_contents('assets/doc/README');
		returnToMenu();
		?>
		<div><pre><?php print $strReadme?></pre></div>
		<?php
		returnToMenu();
		break;
}
?>

</body>
</html>
