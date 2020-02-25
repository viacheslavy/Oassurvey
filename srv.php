<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <script src="js/jquery-1.12.4.min.js"></script>
    <style>
#testHold {
	position:relative;
	width:100%;
	overflow:hidden;
	border:1px solid #CCC;
}
#showMore{
	position:absolute;
	top:0px;
	right:0px;
}
.overfl {

}
.morecontent span {
    display: none;
}
.morelink {
	background:#FFF;
	position:absolute;
	top:0px;
	right:0px;
}
    </style>
</head>

<!-- The #page-top ID is part of the scrolling feature - the data-spy and data-target are part of the built-in Bootstrap scrollspy function -->

<body>
<html>
  <head>
    <title>jQuery Read More/Less Toggle Example</title>
  </head>
  <body>
  <div class="overfl" id="testHold"><span class="more" id="idmore">Morbi placerat imperdiet risus quis blandit. Ut lobortis elit luctus, feugiat era.Morbi placerat imperdiet risus quis blandit. Ut lobortis elit luctus, feugiat era.Morbi placerat imperdiet risus quis blandit. Ut lobortis elit luctus, feugiat era.Morbi placerat imperdiet risus quis blandit. Ut lobortis elit luctus, feugiat era.Morbi placerat imperdiet risus quis blandit. Ut lobortis elit luctus, feugiat era.Morbi placerat imperdiet risus quis blandit. Ut lobortis elit luctus, feugiat era.</span></div>
  </body>
</html>

<script>
$(document).ready(function() {
 //FUNCTIONS FOR COLLAPSING AND EXPANDING DESCRIPTION CONTAINERS
    //var showChar = 100;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "Show more >";
    var lesstext = "Show less";
    

    $('.more').each(function() {
        var content = $(this).html();
 
			var contentHeight = $('#idmore').height();
			var containerHeight = $('#testHold').height();
			if(contentHeight > containerHeight) {
 
            var c = content.substr(0, showChar);
            var h = content.substr(showChar, content.length - showChar);
 
            var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';
 			$(this).html(html);
        }
 
    });
    $(".morelink").click(function(){
        if($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });
	/*
var contentHeight = $('#idmore').height();
var containerHeight = $('#testHold').height();
if(contentHeight > containerHeight) {
    var showMore = $('<button id="showMore" value="Show More">Show More</button>');
    showMore.appendTo('#idmore');
}
	$("#showMore").click(function(){
		alert('hi');
		$("#idmore").removeClass("overfl");
	});
		*/
});
</script>
</body>

</html>