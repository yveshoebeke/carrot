# Carrot

Carrot is a RabbitMQ API based AQMP panel.
![](https://github.com/yveshoebeke/carrot/blob/master/assets/img/rabbit-with-carrot.jpg)
Preamble:
---------
_Follow the next steps to create your own RabbitMQ AMQP virtual host:_

Note: The following are bash commands, and, you will need SU privileges for this.

a) Establish the vhost:

rabbitmqctl add_vhost {vhost name}


b) Create user:

rabbitmqctl add_user {user name} {user password}


c) Create user's credentials for your new vhost:

rabbitmqctl set_permissions -p {vhost name} {user name} .* .* .*

.* is the regex that will give the user full permission for, respectively:
		- configure
		- write
		- read

You can find the man page for rabbitmqctl here:

http://www.rabbitmq.com/man/rabbitmqctl.1.man.html

eebelow where to enter you {your username} {your password} and {your vhost name} in config.ini

1) Access:
----------
Access credentials are currently provided via the config.ini file that resides in the application root.

You will need to alter your vhost and authorization parameters to reflect yours:

;------------------------------
; RabbitMQ configuration
;

[RabbitMQ Credentials]
username = "{your username}"
password = "{your password}"

[RabbitMQ vhost]
vhost = "{your vhost name}"
node = "rabbit@RED"

[RabbitMQ host]
protocol = "http://"
hostname = "localhost"
port = "15672"
;------------------------------

2) Main Menu:
-------------
When activating the application you will be presented with the Main Menu, as follows:

a) Get User Info:
-----------------
Will list all users, associated vhost and their configuration, read and write permissions.

b) Get Vhost Info:
------------------
Will list all vhosts on the RabbitMQ server, and if the trace flag is set to true or false.
If the trace flag is set to true, one can easier inspect the ongoings inside the rabbit host. They are currently enabled, until development of this is terminated.
The logs are preserved here: /var/log/rabbitmq/rabbit@RED.log (basically our node name, rabbit@RED). Error logs are in this location as well.

c) List Channels:
-----------------
Will list all chennels with the following information, mostly to be used if message debugging is required:

ID: Channel name, constructed with port and host information.
peer port: Assigned port.
peer host: Assigned host.
idle Since: Date/time last activity.
transactional: true | false
confirm: true | false
consumer cnt: Number of consumers that are served by this channel.
unack msg: Number of unacknowledged messages.
unconf msg: NUmber of unconfirmed messages.
uncommit msg: Number of uncommitted messages.
uncommit acks: NUmber of uncommitted acknowledges.
state: Channel state (running or not).
node: In our case rabbit@RED, our node name.
number:
user: User associated to this channel.
vhost: Vhost associated to this channel.

Note: a more substantial explanation is forthcoming.

List Bindings:
--------------
Lists all the bindings between the queues and exchanges of your vhost:

Source: The exchange name.
vhost: Your vhost.
destination: The queue name.
destination type: The type of queue (mostly: transactional).
routing key: The routing key associated to this binding.

List (and Remove) Exchanges:
----------------------------
Lists all the exchanges on your vhost:

Name: The exchange name.
vhost: your vhost.
type: direct | headers | fanout | topic
durable: true | false. If set to true, it will survive a RabbitMQ restart and remain active. If false, it will no longer be present.
auto-delete: true | false. Id=f set to true, the exchange will be deleted when all of the queues it is bound to are deleted.
internal: true | false. If true, the exchange may not be used directly by publishers, but only when bound to other exchanges. Internal exchanges are used to construct wiring that is not visible to applications.
arguments: Any arguments associated to this exchange.

Note: The last column of this table can be selected to [D] Delete the exchange.

Create an Exchange:
-------------------
Allows you to create an exchange.

You will need to provide the name of your exchange. 

Note: If needed, future version of this may also allow you to set the durable, auto-delete and internal flages (see above).

List(and Purge/Remove) Queues:
------------------------------
Lists all the queues on your vhost.

Name: The name of the queue.
vhost: Your vhost associated with this queue.
durable: true | false. See explanation under 'exchanges', above.
auto_delete: true | false. See explanation underr 'exchanges', above.
state: running or not.
messages: Number of messages in this queue.
messages ready: Number of messages ready to be processed.
idle since: Date/time of last activity in this queue.
				
Note: The 2 last columns allow you to [P] Purge all message from the queue, or [D] Delete it all together.

Create a Queue:
---------------
In order to create a queue you will need to provide:
The name of your queue.
Select the exchange you want to bind the queue to (from drop down).
The routing key associated to this binding.

Note: The application will automatically bind this new queue to the specified exchange and associate the routing key to it.


 
Send a Message:
-------------------	
To send a message you will need to provide the following:

The exchange you want to send the message to (from drop down).
The routing key (see 'Create a Queue' and 'Bindings', above)
The payload, or the message itself.

Note: Future version will allow you to set the encoding, currently UTF-8, but here is also an option for Base64 encoding.

