$(document).ready(function(){
    var ht=$(window).height();
    var fh = $('.footer').height();
    ht = ht-(fh+160);
    
    $(".gradient-bg").css("min-height", ht);
});