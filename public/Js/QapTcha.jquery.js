/************************************************************************
*************************************************************************
@Name :       	QapTcha - jQuery Plugin
@Revison :    	4.2
@Date : 		06/09/2012  - dd/mm/YYYY
@Author:     	 ALPIXEL Agency - (www.myjqueryplugins.com - www.alpixel.fr)
@License :		 Open Source - MIT License : http://www.opensource.org/licenses/mit-license.php

**************************************************************************
*************************************************************************/
jQuery.QapTcha = {
	build : function(options)
	{
        var defaults = {
			txtLock : '将箭头移至最右端解锁',
			txtUnlock : '已解锁,可以登陆了.',
			disabledSubmit : true,
			autoRevert : true,
			PHPfile : '/public/huadongyz.html',
			autoSubmit : false
        };

		if(this.length>0)
		return jQuery(this).each(function(i) {
			/** Vars **/
			var
				opts = $.extend(defaults, options),
				$this = $(this),
				form = $('form').has($this),
				Clr = jQuery('<div>',{'class':'clr'}),
				bgSlider = jQuery('<div>',{'class':'bgSlider'}),
				Slider = jQuery('<div>',{'class':'Slider'}),
				TxtStatus = jQuery('<div>',{'class':' TxtStatus dropError',text:opts.txtLock}),
				inputQapTcha = jQuery('<input>',{name:generatePass(32),value:generatePass(7),type:'hidden'});

			/** Disabled submit button **/
			if(opts.disabledSubmit) form.find('input[class=\'submit\']').attr('disabled','disabled');

			/** Construct DOM **/
			bgSlider.appendTo($this);
			Clr.insertAfter(bgSlider);
			TxtStatus.insertAfter(Clr);
			inputQapTcha.appendTo($this);
			Slider.appendTo(bgSlider);
			$this.show();

			Slider.draggable({
				revert: function(){
					if(opts.autoRevert)
					{
						if(parseInt(Slider.css("left")) > (bgSlider.width()-Slider.width()-10)) return false;
						else return true;
					}
				},
				containment: bgSlider,
				axis:'x',
				stop: function(event,ui){
					if(ui.position.left > (bgSlider.width()-Slider.width()-10))
					{
						// set the SESSION iQaptcha in PHP file
						$.post(opts.PHPfile,{
							action : 'qaptcha',
							qaptcha_key : inputQapTcha.attr('name')
						},
						function(data) {
							if(!data.error)
							{
								Slider.draggable('disable').css('cursor','default');
								inputQapTcha.val('');
								TxtStatus.text(opts.txtUnlock).addClass('dropSuccess').removeClass('dropError');
								$('.QapTcha').hide();
								$('.submit').show();
								form.find('input[class=\'submit\']').removeAttr('disabled');
								form.find('input[class=\'submit\']').css('background-color', '#529ECC');
								form.find('input[class=\'submit\']').val('登入');
								if(opts.autoSubmit) form.find('input[class=\'submit\']').trigger('click');
							}
						},'json');
					}
				}
			});

			function generatePass(nb) {
		        var chars = 'azertyupqsdfghjkmwxcvbn23456789AZERTYUPQSDFGHJKMWXCVBN_-#@';
		        var pass = '';
		        for(i=0;i<nb;i++){
		            var wpos = Math.round(Math.random()*chars.length);
		            pass += chars.substring(wpos,wpos+1);
		        }
		        return pass;
		    }

		});
	}
}; jQuery.fn.QapTcha = jQuery.QapTcha.build;