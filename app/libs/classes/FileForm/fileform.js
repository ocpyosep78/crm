jQuery(function($){
  $('body').one('submit', 'form:has(input[type="file"][ffcb])', function(){
	$('<iframe />', {id:'fffr', name:'fffr'}).hide().appendTo($('body'));
	var cb = $(this).find('input[type="file"][ffcb]').attr('ffcb');
	$(this).append($('<input type="hidden" name="ffcb" value="'+cb+'" />'))
		.attr({target:'fffr', method:'post', enctype:'multipart/form-data'});
  });
});