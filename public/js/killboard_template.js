/** 
 * JRM Killboard - Front end JS script
 * Version: 1.0
 * Author: jrmarco
 * Author URI: https://bigm.it
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html#content
 */
(function($) {
    $('.jrm_killboard_pager').on('click',function(event) {
    	let nonce = document.getElementById('fenon').value;
        let current = document.getElementById('tabledata').getAttribute('data-page');
        let offset = event.target.getAttribute('data-mode')

        postData = {action: "jrm_killboard_get_table_data", 'check': nonce, 'offset': offset, 'current': current};
        $.post(axobject.ajaxurl, postData, function (response) {
            let data = JSON.parse(response);
            console.log(data);
            if(data.count>0) {
                document.getElementById('tabledata').setAttribute('data-page',data.landing_page);
                document.getElementById('pageIndex').value = data.index;
                document.getElementById('tabledata').innerHTML = data.html;
            }
        });
    });
})(jQuery);