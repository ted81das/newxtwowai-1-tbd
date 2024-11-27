/* globals jQuery:true, ajaxurl:true, whizzie_params:true */
'use strict';

const Whizzie = (function($){
    
    let t;
    let current_step = '';
    let step_pointer = '';
    
    // callbacks from form button clicks.
    const callbacks = {
        do_next_step: function( btn ) {
            do_next_step( btn );
        },
        
        // ** Activate Firewall step
        activate_firewall: function(btn){
            const data = {
                'action': 'secnin_activate_firewall',
                '_ajax_nonce': whizzie_params.nonce
            };
            
            $.get(ajaxurl, data, function(response) {
                if (response.success) {
                    do_next_step();
                } else {
                    console.error('Error:', response.data);
                    // Display error to user
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                // Display error to user
            });
        },
        // ** Activate default fixes
        activate_default_fixes: function(btn){
            const data = {
                'action': 'secnin_activate_default_fixes',
                '_ajax_nonce': whizzie_params.nonce
            };
            
            $.get(ajaxurl, data, function(response) {
                if (response.success) {
                    do_next_step();
                } else {
                    console.error('Error:', response.data);
                    // Display error to user
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                // Display error to user
            });
        }
    };

    function setStepHeight() {
        let maxHeight = 0;
        
        $('.whizzie-menu li.step').each(function(index) {
            $(this).attr('data-height', $(this).innerHeight());
            if ($(this).innerHeight() > maxHeight) {
                maxHeight = $(this).innerHeight();
            }
        });
        
        $('.whizzie-menu li .detail').each(function(index) {
            $(this).attr('data-height', $(this).innerHeight());
            $(this).addClass('scale-down');
        });
        
        $('.whizzie-menu li.step').css('height', maxHeight);
    }

    function initializeSteps() {
        $('.whizzie-menu li.step:first-child').addClass('active-step');
        $('.whizzie-nav li:first-child').addClass('active-step');
        $('.whizzie-wrap').addClass('loaded');
    }

    function handleMoreInfoClick() {
        $('.whizzie-wrap').on('click', '.more-info', function(e) {
            e.preventDefault();
            const parent = $(this).parent().parent();
            parent.toggleClass('show-detail');
            const detail = parent.find('.detail');
            const maxHeight = parent.data('height');
            
            if (parent.hasClass('show-detail')) {
                parent.animate({
                    height: maxHeight + detail.data('height')
                },
                500,
                function(){
                    detail.toggleClass('scale-down');
                }).css('overflow', 'visible');
            } else {
                parent.animate({
                    height: maxHeight
                },
                500,
                function(){
                    detail.toggleClass('scale-down');
                }).css('overflow', 'visible');
            }
        });
    }

    function handleDoItClick() {
        $('.whizzie-wrap').on('click', '.do-it', function(e) {
            e.preventDefault();
            step_pointer = $(this).data('step');
            current_step = $(`.step-${step_pointer}`);
            $('.whizzie-wrap').addClass('spinning');
            
            if ($(this).data('callback') && typeof callbacks[$(this).data('callback')] !== 'undefined') {
                // we have to process a callback before continue with form submission
                callbacks[$(this).data('callback')](this);
                return false;
            } else {
                return true;
            }
        });
    }
    
    function secnin_window_loaded() {
        setStepHeight();
        initializeSteps();
        handleMoreInfoClick();
        handleDoItClick();
    }
    
    function do_next_step( btn ) {
        current_step.removeClass('active-step');
        $(`.nav-step-${step_pointer}`).removeClass('active-step');
        current_step.addClass('done-step');
        $(`.nav-step-${step_pointer}`).addClass('done-step');
        current_step.fadeOut(500, function() {
            current_step = current_step.next();
            step_pointer = current_step.data('step');
            current_step.fadeIn();
            current_step.addClass('active-step');
            $(`.nav-step-${step_pointer}`).addClass('active-step');
            $('.whizzie-wrap').removeClass('spinning');
        });
    }
    
    return {
        init: function(){
            t = this;
            $(secnin_window_loaded);
        },
        callback: function(func){
            // Removed console.log statements
        }
    };
    
})(jQuery);

jQuery(document).ready(function(){
    // Lets load when the page is ready
    Whizzie.init();
});