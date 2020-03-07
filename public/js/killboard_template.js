/** 
 * JRM Killboard - Front end JS script
 * Version: 1.3
 * Author: jrmarco
 * Author URI: https://bigm.it
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html#content
 */
jQuery(function() {});
loadItems();

jQuery('.jrm_killboard_pager').on('click',function(event) {
    document.getElementById('item-modal').style.display = 'none';
	let nonce = document.getElementById('fenon').value;
    let current = document.getElementById('tabledata').getAttribute('data-page');
    let offset = event.target.getAttribute('data-mode')

    postData = {action: "jrm_killboard_get_table_data", 'check': nonce, 'offset': offset, 'current': current};
    jQuery.post(axobject.ajaxurl, postData, function (response) {
        let data = JSON.parse(response);
        if(data.count>0) {
            document.getElementById('tabledata').setAttribute('data-page',data.landing_page);
            if (document.getElementById('pageIndex_top') != undefined) {
                document.getElementById('pageIndex_top').value = data.index;
            }
            document.getElementById('pageIndex_bottom').value = data.index;
            document.getElementById('tabledata').innerHTML = data.html;
            loadItems();
        }
    });
});

// Fetch killmail items and populate modal with data
function loadItems() {
    let nonce = document.getElementById('fenon').value;
    let elements = document.getElementsByClassName('load_items');
    for (i=0;i<elements.length;i++) {
        let element = elements[i];
        element.addEventListener('click',function() {
            postData = {action: "jrm_killboard_load_items", 'check': nonce, 'id': element.getAttribute('data-id')};
            jQuery.post(axobject.ajaxurl, postData, function (response) {
                let data = JSON.parse(response);
                let cardBody = document.getElementById('item-card-body');
                if(data.items && data.items != 'missing') {
                    document.getElementById('item-ship').innerHTML = element.getAttribute('data-ship');
                    document.getElementById('item-victim').innerHTML = element.getAttribute('data-victim');
                    html = '<table style="margin-top:5px;"><tbody style="font-size: '+cardBody.style.fontSize+';">';
                    for (i=0;i<data.items.length;i++) {
                        url = cardBody.getAttribute('data-url').replace('ID',data.items[i].id);
                        size = cardBody.getAttribute('data-size');
                        html += '<tr><td width="40px"><img src="'+url+'" /></td><td style="margin-left:4px;">'+data.items[i].name;
                        html += data.items[i].quantity != null ? '<br>[ '+cardBody.getAttribute('data-dropped')+' '+data.items[i].quantity+' ]' : '';
                        html += '</td></tr>';
                    }
                    html += '</tbody></table>';
                    cardBody.innerHTML = html;
                    document.getElementById('item-modal').style.display = '';
                } else {
                    document.getElementById('item-victim').innerHTML = 'Error';
                    document.getElementById('item-ship').innerHTML = cardBody.getAttribute('data-missing');
                    cardBody.innerHTML = '';
                    document.getElementById('item-modal').style.display = '';
                }
            });
        });
    }

    let top = (document.documentElement.clientHeight-550)/2;
    let left = (document.documentElement.clientWidth-jQuery('#item-modal').width())/2;
    document.getElementById('item-modal').style.top = top+'px';
    document.getElementById('item-modal').style.left = left+'px';
}

function closeItemsModal() {
    document.getElementById('item-modal').style.display = 'none';
}
