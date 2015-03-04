function makeActive(){
	makeMenuActive();
	makeDeleteActive();
}

function makeMenuActive() {
	var menuItems = document.getElementsByClassName('menu_item');
    for(var i=0;i<menuItems.length;i++){
        menuItems[i].addEventListener('click', function(){
    	document.getElementById('mode').value = this.id;
    	document.getElementById('menu_form').submit();
		}, false);
	}
}

function makeDeleteActive() {
	var exchangeItems = document.getElementsByClassName('delete_exchange');
    for(var i=0;i<exchangeItems.length;i++){
    exchangeItems[i].addEventListener('click', function(){
    	if(confirm('Are you sure you want to remove exchange: '+this.id+' ?') == true){
        	document.getElementById('delete_exchange_id').value = this.id;
        	document.getElementById('delete_exchange_form').submit();
		}}, false);
	}

	var queueItems = document.getElementsByClassName('delete_queue');
    for(var i=0;i<queueItems.length;i++){
    queueItems[i].addEventListener('click', function(){
    	if(confirm('Are you sure you want to remove queue: '+this.id+' ?') == true){
        	document.getElementById('delete_queue_id').value = this.id;
	       	document.getElementById('delete_queue_form').submit();
	    }}, false);
	}

	var queueItems = document.getElementsByClassName('purge_queue');
    for(var i=0;i<queueItems.length;i++){
    queueItems[i].addEventListener('click', function(){
    	if(confirm('Are you sure you want to purge queue: '+this.id+' ?') == true){
        	document.getElementById('purge_queue_id').value = this.id;
	       	document.getElementById('purge_queue_form').submit();
	    }}, false);
	}
}
