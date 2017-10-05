<div class="container nav-toggle">
	<div class="row">
		<div class="col-lg-offset-1 col-md-offset-1 col-lg-3 col-md-3 col-sm-3 col-xs-12 no-padding">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" onclick="toggleMerchantList()" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
			<div class="name-list">
				Merchants:
				<ul id="merchant-chat-list">
					<?php foreach($activeMerchants as $merchant){ ?>
					<li class="chat-merchant chat" merchant-id="<?= $merchant['merchant_id'] ?>" id="li<?= $merchant['merchant_id'] ?>">
						<span class="avatar">
							<span class="chat-person">T</span>
						</span>
						<span id="status<?= $merchant['merchant_id'] ?>" class="connected">connected</span>
						<span id="title<?= $merchant['merchant_id'] ?>" ><?= $merchant['merchant_id'] ?></span>
						<div style="display:none;" id="conversation<?= $merchant['merchant_id'] ?>">
							<ul id="conversation-list<?= $merchant['merchant_id'] ?>">
							</ul>
						</div>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<div class="col-lg-7 col-md-7 col-sm-9 col-xs-12 no-padding">
			<!-- <div class="header">
			<span class="dropdown">
			  <i class="fa fa-chevron-down" aria-hidden="true"></i>
			  <div id="chat-box-actions" >
			    <div class="left"><p><a id="load-previous">Load Previous Messages</a></p></div>
			    <div class="right"><p><a id="clear-all">Clear All</a></p></div>
			  </div>
			</span>
		</div> -->
		<div class="note-list chat">
			<ol  id="merchant-message" style="display:none;">
			</ol>
		</div>
		<div class="msg-box">
			<div class="wrap">
				<textarea type="text" placeholder="Type here!" class="textarea" id="message" ></textarea>
				<button id="send-btn" current-merchant="">Send</button>
			</div>
		</div>
	</div>
</div>
</div>
<script src="//code.jquery.com/jquery-3.2.1.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function(){

	if (location.protocol.indexOf("https") >= 0)
      var wsUri = "wss://192.168.0.58:9002";
    else
      var wsUri = "ws://192.168.0.58:9002";
	//create a new WebSocket object.
	//var wsUri = "ws://192.168.0.58:9002";
	websocket = new WebSocket(wsUri); 
	var vendorSid="vendorSid";
	var vendorRid="vendorRid";
	jQuery('#merchant-chat-list').on('click','.chat-merchant',function(){
		var id = jQuery(this).attr('merchant-id');
		$chatHtml = jQuery('#conversation-list'+id).html();
		jQuery('#merchant-message').html($chatHtml);
		jQuery('#merchant-message').show();
		//jQuery('#chat-box').show();
		jQuery('#send-btn').attr('current-merchant',id);
	});
	websocket.onopen = function(ev) { // connection is open 
		console.log("Connected!"); //notify user
		//prepare json data
		var msg = {
			hash: '<?= $hash ?>'
		};
		//convert and send data to server
		websocket.send(JSON.stringify(msg));
	}
	jQuery('#send-btn').click(function(){ //use clicks message send button	
		var mymessage = jQuery('#message').val(); //get message text
		var mapdata="teammember"+"-"+"vendor";
		if(mymessage == ""){ //emtpy message?
			alert("Enter Some message Please!");
			return;
		}
		var id = jQuery(this).attr('current-merchant');
		//prepare json data
		if(id==''){
			alert('Kindly Select Merchant');
			return;
		}
		var msg = {
			message: mymessage,
			id: id
		};
		//convert and send data to server
		websocket.send(JSON.stringify(msg));
	});
	//#### Message received from server?
	websocket.onmessage = function(ev) {
		var msg = JSON.parse(ev.data); //PHP sends Json data
		var id=msg.user_id;//get vendor id from server
		var umsg = msg.message; //message text
		var user_name = msg.sender_name;
		//list all client
		console.log(msg);
		if(msg.message_type=='status'){
			if(jQuery('#status'+id).length>0){
				jQuery('#status'+id).attr('class',msg.message);
				jQuery('#status'+id).html(msg.message);		
			}
			else
			{
				$html = '<li class="chat-merchant" merchant-id="'+id+'" id="li'+id+'">\
				<span id="status'+id+'" class="connected">connected</span>\
				<span id="title'+id+'" >'+id+'</span>\
				<div style="display:none;" id="conversation'+id+'">\
					<ul id="conversation-list'+id+'"></ul>\
				</div>\
			</li>';
			jQuery('#merchant-chat-list').append($html);
		}
	} else {			
			/*var date = msg.date;
			var dateId = date.replace(',', "");
			var dateId = dateId.replace(/ /g, "_");
			if(jQuery('#'+dateId).length == 0) {
				$formattedMsg += '<li class="chat-date" id="'+dateId+'">';
				$formattedMsg += '<span>'+date+'</span>';
				$formattedMsg += '</li>';
			}
			$formattedMsg += '<li ><span class="user-name">'+user_name+'</span> : <span>'+umsg+"</span></li>";*/
			if(id=='shopify_walmart_team') {
				//id = jQuery('#send-btn').attr('current-merchant');
				id = msg.receiver_ref;
			}
			var $formattedMsg = prepareMessageHtml(msg.user_id, user_name, umsg, msg, id);
			//console.log(jQuery('#conversation-list'+id).length);
			if(jQuery('#conversation-list'+id).length>0){
				jQuery('#conversation-list'+id).append($formattedMsg);
				if(parseInt(jQuery('#send-btn').attr('current-merchant'))==parseInt(id)){
					jQuery('#merchant-message').append($formattedMsg);
				}
			}
			else
			{
				$html = '<li class="chat-merchant" merchant-id="'+id+'" id="li'+id+'">\
				<span id="status'+id+'" class="connected">connected</span>\
				<span id="title'+id+'" >'+id+'</span>\
				<div id="conversation'+id+'">\
					<ul id="conversation-list'+id+'">\
						'+$formattedMsg+'\
					</ul>\
				</div>\
			</li>';
			jQuery('#merchant-chat-list').append($html);
		}
	}
		jQuery('#message').val(''); //reset text
	};
	websocket.onerror	= function(ev){
		var notified="Error Occurred-"+ev.data;
		alert(notified);
	}; 
	websocket.onclose 	= function(ev){
		alert("Connection Closed")
	}; 
});
	function prepareMessageHtml(senderId, senderName, msg, msgObj, activeMid)
	{
		var html = '';
		var date = msgObj.date;
		var dateId = date.replace(',', "");
		var dateId = dateId.replace(/ /g, "_") + '-' + activeMid;
		if(jQuery('#'+dateId).length == 0) {
			html += '<li class="chat-date" id="'+dateId+'">';
			html += '<span>'+date+'</span>';
			html += '</li>';
		}
		var time = msgObj.time;
		var splitTime = time.split(":");
		var time = splitTime[0]+':'+splitTime[1];
		if(senderId == 'shopify_walmart_team')
		{
			var firstLetter = senderName.substr(0, 1); 
			firstLetter = firstLetter.toUpperCase();
			html += '<li class="self">';
			html += '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 avatar"><span class="chat-person">'+firstLetter+'</span></div>';
			html += '<div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 msg">';
			html += '<p>'+msg+'</p>';
			html += '<time>'+time+'</time>';
			html += '</div>';
			html += '</li>';
		}
		else
		{
			var firstLetter = senderName.substr(0, 1);
			firstLetter = firstLetter.toUpperCase();
			html += '<li class="other">';
			html += '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 avatar"><span class="chat-person">'+firstLetter+'</span></div>';
			html += '<div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 msg">';
			html += '<p>'+msg+'</p>';
			html += '<time>'+time+'</time>';
			html += '</div>';
			html += '</li>';
		}
		return html;
	}

	function toggleMerchantList()
	{
		jQuery('.name-list').toggleClass('shownamelist');
	}
</script>
