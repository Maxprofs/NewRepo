<div class="container">
  <div class="row">
    <div class="col-lg-offset-2 col-md-offset-2 col-sm-offset-1 col-lg-8 col-md-8 col-sm-10 col-xs-12">
      <div class="header">
        <span class="dropdown">
          <i class="fa fa-chevron-down" aria-hidden="true"></i>
          <div id="chat-box-actions" >
            <div class="left"><p><a id="load-previous">Load Previous Messages</a></p></div>
            <div class="right"><p><a id="clear-all">Clear All</a></p></div>
          </div>
        </span>
      </div>
      <ol class="note-list chat">
      </ol>
      <!-- <input type="text" id="message"> -->
      <div class="msg-box">
        <div class="wrap">
          <textarea type="text" placeholder="Type here!" class="textarea" id="message" ></textarea>
          <button id="send-btn">Send</button>
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
    websocket.onopen = function(ev) { // connection is open 
        console.log("Connected!"); //notify user
        //prepare json data
        var msg = {
          hash: '<?= $hash ?>'
        };
        //convert and send data to server
        websocket.send(JSON.stringify(msg));
      }
      jQuery('#load-previous').click(function(){
        jQuery.post('<?= \yii\helpers\Url::toRoute(['chat/load','id'=>10]) ?>',function(data){
          if(data==''){
            jQuery('.note-list').prepend('<li>No Previous Chat Available</li>');
          }
          else
          {
            jQuery('.note-list').html(data);
          }
          jQuery('#load-previous').remove();
        });
      });
      jQuery('#clear-all').click(function(){
        jQuery.post('<?= \yii\helpers\Url::toRoute(['chat/clear','id'=>10]) ?>',function(data){
          jQuery('#clear-all').remove();
          jQuery('.note-list').html('');
          jQuery('.note-list').prepend('<li>Chat Cleared Successfully..</li>');
        });
      });
    jQuery('#send-btn').click(function(){ //use clicks message send button  
        var mymessage = jQuery('#message').val(); //get message text
        var mapdata="teammember"+"-"+"vendor";
        if(mymessage == ""){ //emtpy message?
          alert("Enter Some message Please!");
          return;
        }
        //prepare json data
        var msg = {
          message: mymessage
        };
        //convert and send data to server
        websocket.send(JSON.stringify(msg));
      });
    //#### Message received from server?
    websocket.onmessage = function(ev) {
        var msg = JSON.parse(ev.data); //PHP sends Json data
        var id=msg.user_id;//get vendor id from server
        var umsg = msg.message; //message text
        var user_name=msg.sender_name;
        console.log(msg);
        var html = prepareMessageHtml(id,user_name, umsg, msg);
        jQuery('.note-list').append(html);
        //jQuery('.note-list').append('<li ><span class="user-name">'+user_name+'</span> : <span>'+umsg+"</span></li>");
        jQuery('#message').val(''); //reset text
      };
      websocket.onerror   = function(ev){
       var notified="Error Occurred-"+ev.data;
       alert(notified);
     }; 
     websocket.onclose    = function(ev){
      alert("Connection Closed")
    }; 
  });
  function prepareMessageHtml(senderId, senderName, msg, msgObj)
  {
      var html = '';

      var date = msgObj.date;
      var dateId = date.replace(',', "");
      var dateId = dateId.replace(/ /g, "_");
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

        html += '<li class="other">';
        //html += '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 avatar">'+firstLetter+'<img src="http://i.imgur.com/DY6gND0.png" draggable="false"/></div>';
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

          html += '<li class="self">';
          //html += '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 avatar">'+firstLetter+'<img src="http://i.imgur.com/HYcn9xO.png" draggable="false"/></div>';
          html += '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 avatar"><span class="chat-person">'+firstLetter+'</span></div>';
          html += '<div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 msg">';
          html += '<p>'+msg+'</p>';
          html += '<time>'+time+'</time>';
          html += '</div>';
          html += '</li>';
      }
      return html;
}
</script>
<script type="text/javascript">
 jQuery(document).ready(function(){
  var height = $(window).height();
  var footerheight = $(".msg-box").height();
  var finalheight = (height - (footerheight));
  $(".note-list").css("height" , finalheight)
}); 
</script>
