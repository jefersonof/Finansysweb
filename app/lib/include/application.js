loading = true;

Application = {};
Application.translation = {
    'en' : {
        'loading' : 'Loading'
    },
    'pt' : {
        'loading' : 'Carregando'
    },
    'es' : {
        'loading' : 'Cargando'
    }
};

Adianti.onClearDOM = function(){
	/* $(".select2-hidden-accessible").remove(); */
	$(".colorpicker-hidden").remove();
	$(".select2-display-none").remove();
	$(".tooltip.fade").remove();
	$(".select2-drop-mask").remove();
	/* $(".autocomplete-suggestions").remove(); */
	$(".datetimepicker").remove();
	$(".note-popover").remove();
	$(".dtp").remove();
	$("#window-resizer-tooltip").remove();
};


function showLoading() 
{ 
    if(loading)
    {
        __adianti_block_ui(Application.translation[Adianti.language]['loading']);
    }
}

Adianti.onBeforeLoad = function(url) 
{ 
    loading = true; 
    setTimeout(function(){showLoading()}, 400);
    if (url.indexOf('&static=1') == -1) {
        $("html, body").animate({ scrollTop: 0 }, "fast");
    }
};

Adianti.onAfterLoad = function(url, data)
{ 
    loading = false; 
    __adianti_unblock_ui( true );
};

// set select2 language
$.fn.select2.defaults.set('language', $.fn.select2.amd.require("select2/i18n/pt"));


//Zindex

function tjquerydialog_start( id, modal, draggable, resizable, width, height, top, left, zIndex, actions, close_action, close_escape, dialog_class) {
	$( id ).dialog({
		modal: modal,
		stack: false,
		zIndex: 2000,
        draggable: draggable,
        resizable: resizable,
        closeOnEscape: close_escape,
		height: height,
		width: width,
		dialogClass: dialog_class,
		beforeClose: function() {
		    if (typeof(close_action) == "function") {
		        close_action();
		        return false;
		    }
		    return true;
		},
		close: function(ev, ui) {
            $(this).remove();
            $(".tooltip.fade").remove();
		},
		buttons: actions
	});
	
	$('.ui-dialog').last().focus();
	
	$( id ).closest('.ui-dialog').css({ zIndex: zIndex });
	$(".ui-widget-overlay").css({ zIndex: 1050 });
	
	if (top > 0) {
	    $( id ).closest('.ui-dialog').css({ top: top+'px' });
	}
	
	if (left > 0) {
	    $( id ).closest('.ui-dialog').css({ left: left+'px' });
	}
}

function tjquerydialog_block_ui()
{
    $( document ).ready(function() {
        $('.ui-dialog').css('pointer-events', 'none');
        $('.ui-dialog-content').css('opacity', '0.5');
    });
}

function tjquerydialog_unblock_ui()
{
    $('.ui-dialog').css('pointer-events', 'all');
    $('.ui-dialog-content').css('opacity', '');
}