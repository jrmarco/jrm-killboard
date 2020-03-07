/** 
 * JRM Killboard - Admin JS script
 * Version: 1.3
 * Author: jrmarco
 * Author URI: https://bigm.it
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html#content
 */
jQuery(function() {});

// Show kill
function toggleKill(killId,status) {
    nonce = document.getElementById('wpopnn').value;

    data = {action: "jrm_killboard_hide_kill", id: killId, status:status, check: nonce };
    jQuery.post(ajaxurl, data, function(response) {
        json = JSON.parse(response);
        if(json.status) {
            location.reload();
        }
    });
}

// Hide kill
function removeKill(killId) {
    let message = document.getElementById('confirm-message').value;
    if (confirm(message.replace('%d',killId))) {
        nonce = document.getElementById('wpopnn').value;

        data = {action: "jrm_killboard_remove_kill", id: killId, check: nonce};
        jQuery.post(ajaxurl, data, function (response) {
            json = JSON.parse(response);
            if (json.status) {
                location.reload();
            }
        });
    }
}

// Do price sync
function syncPrice() {
    document.getElementById('jrm_alert').innerHTML  = document.getElementById('loading-message').value;
    document.getElementById("jrm_alert").classList.remove('alert-success');
    document.getElementById("jrm_alert").classList.add('alert-warning');
    nonce = document.getElementById('wpopnn').value;

    data = {action: "jrm_killboard_do_sync_price", check: nonce};
    jQuery.post(ajaxurl, data, function (response) {
        json = JSON.parse(response);
        if (json.status) {
            location.reload();
        } else {
            document.getElementById('jrm_alert').innerHTML  = json.error;
        }
    });
}

// Calculate kill worth price
function calculateWorthValue() {
    document.getElementById("jrm_alert").classList.remove('hidden');
    document.getElementById("jrm_alert").classList.add('alert-warning');
    document.getElementById('jrm_alert').innerHTML  = document.getElementById('loading-message').value;

    nonce = document.getElementById('wpopnn').value;

    data = {action: "jrm_killboard_do_sync_worth", check: nonce};
    jQuery.post(ajaxurl, data, function (response) {
        json = JSON.parse(response);
        if (json.status) {
            document.getElementById("jrm_alert").remove();
            location.reload();
        } else {
            document.getElementById('jrm_alert').innerHTML  = json.error;
        }
    });
}

// Save admin configuration
function saveSettings() {
    let nonce = document.getElementById('wpopnn').value;

    let data = {};
    let error = false;
    jQuery("form#setting_form :input").each(function(){
        let name = jQuery(this).attr("name");
        let value = jQuery(this).val();
        let ignored = ['page_title', 'esi_client_id', 'esi_client_secret', 'cron_endpoint', 'cron_secret', 'margin', 'padding'];
        let errorContainer = document.getElementById('message_container');
        if((name!=undefined && name!='undefined' && ignored.indexOf(name)==-1) && value=='') {
            document.getElementById("jrm_alert").classList.add('alert-danger');
            document.getElementById('jrm_alert').innerHTML  = errorContainer.getAttribute('data-error-'+name);
            error = true;
            return;
        }

        if(name == 'oauth' && document.getElementById("oauth_v1") != undefined) {
            value = (document.getElementById("oauth_v1").checked) ? 1 : 2;
        }


        data[name] = value;
    });

    if(error) { return; }

    postData = {action: "jrm_killboard_do_store_settings", 'check': nonce, 'settings': data};
    jQuery.post(ajaxurl, postData, function (response) {
        json = JSON.parse(response);
        if (json.status) {
            location.reload();
        } else {
            document.getElementById("jrm_alert").classList.add('alert-danger');
            document.getElementById('jrm_alert').innerHTML  = json.error;
        }
    });
}

// Save graphics configuration
function saveGraphics() {
    let nonce = document.getElementById('wpopnn').value;

    let data = {};
    let error = false;
    jQuery("form#graphics_form :input").each(function(){
        let name = jQuery(this).attr("name");
        let value = jQuery(this).val();
        let ignored = ['page_title', 'margin', 'padding','btn_styles','image_styles'];
        let errorContainer = document.getElementById('message_container');
        if((name!=undefined && name!='undefined' && ignored.indexOf(name)==-1) && value=='') {
            document.getElementById("jrm_alert").classList.add('alert-danger');
            document.getElementById('jrm_alert').innerHTML  = errorContainer.getAttribute('data-error-'+name);
            error = true;
            return;
        }

        if(name == 'dev_sign') {
            value = (document.getElementById("dev_sign_yes").checked) ? 'show' : 'hide';
        }
        if(name == 'inspect_items') {
            value = (document.getElementById("inspect_items_yes").checked) ? 'show' : 'hide';
        }
        if(name == 'last_page') {
            value = (document.getElementById("last_page_yes").checked) ? 'show' : 'hide';
        }

        data[name] = value;
    });

    if(error) { return; }

    postData = {action: "jrm_killboard_do_store_graphics_settings", 'check': nonce, 'settings': data};
    jQuery.post(ajaxurl, postData, function (response) {
        json = JSON.parse(response);
        if (json.status) {
            location.reload();
        } else {
            document.getElementById("jrm_alert").classList.add('alert-danger');
            document.getElementById('jrm_alert').innerHTML  = json.error;
        }
    });
}

// Display sample of text code/name color
function colorPreview(ref) {
    let children = ref.children;
    children[2].style.backgroundColor = children[1].value;
}

// Fetch killmail
function fetchKillmail() {
    let nonce = document.getElementById('wpopnn').value;
    let url = document.getElementById('link_kill').value;

    document.getElementById("jrm_alert").classList.remove('hidden');
    document.getElementById("jrm_alert").classList.add('alert-warning');
    document.getElementById('jrm_alert').innerHTML  = document.getElementById('loading-long-message').value;

    postData = {action: "jrm_killboard_do_upload_killmail", 'check': nonce, 'link_kill': url};
    jQuery.post(ajaxurl, postData, function (response) {
        json = JSON.parse(response);
        if (json.status) {
            document.getElementById("jrm_alert").remove();
            location.reload();
        } else {
            document.getElementById("jrm_alert").classList.add('alert-danger');
            document.getElementById('jrm_alert').innerHTML  = json.error;
        }
    });
}

// Manually set a price of item
function setPriceValue(item = false, price = false) {
    document.getElementById('jrm_alert').innerHTML = document.getElementById('loading-message').value;
    document.getElementById("jrm_alert").classList.remove('alert-success');
    document.getElementById("jrm_alert").classList.add('alert-warning');

    nonce = document.getElementById('wpopnn').value;
    if (!item && !price) {
        item = document.getElementById('items').value;
        price = document.getElementById('price').value;
    }

    if(item!='' && price!='' && item.match(/[0-9]{1}/g) && price.match(/[0-9]{1}/g)) {
        data = {action: "jrm_killboard_do_set_item_price", id:item, price:price, check: nonce};
        jQuery.post(ajaxurl, data, function (response) {
            json = JSON.parse(response);
            if (json.status) {
                location.reload();
            } else {
                document.getElementById('jrm_alert').innerHTML  = json.error;
            }
        });
    }
}

// Process admin pagination
function pagination(page,lastpage = 0) {
    if(page == 'custom') {
        page = parseInt(document.getElementById('custom_page').value)-1;
    }

    if(/[^0-9]{1,}/.test(page) || page < 0 || page >= lastpage) {
        page = 0;
    }
    location.href += '&offset='+page;
}

// Init ESI SSO Authorization button
function initAuthorization(btn) {
    document.getElementById("esiconfig").classList.remove('alert-danger');

    if(document.getElementById('corporation_id')==-1) {
        document.getElementById("jrm_alert").innerHTML = document.getElementById("message_container").getAttribute('data-error-cid');
        document.getElementById("jrm_alert").classList.add('alert-warning');
        document.getElementById("esiconfig").classList.add('alert-danger');
        return true;
    }
    let authUrl = btn.getAttribute('data-auth-link');
    let clientId = btn.getAttribute('data-esi-id');
    let secret = document.getElementById('esi_client_secret');
    let scope = btn.getAttribute('data-esi-scope');
    let state = btn.getAttribute('data-esi-state');
    let redirect = btn.getAttribute('data-redirect');
    if(authUrl!='' && clientId!='' && secret!='' && scope!='' && state!='' && redirect!='') {
      var d = new Date();
      d.setTime(d.getTime() + (5*60*1000));
      var expires = "expires="+ d.toUTCString();
      document.cookie = "esi-init-call=" + state + ";" + expires + ";path=/";
      window.open(authUrl+encodeURI('?response_type=code&redirect_uri='+redirect+'&client_id='+clientId+'&scope='+scope+'&state='+state));
    } else {
        document.getElementById("esiconfig").classList.add('alert-danger');
    }
}

// Remove ESI SSO Auth tokens
function removeAuthorization() {
    nonce = document.getElementById('wpopnn').value;
    data = {action: "jrm_killboard_do_remove_sso_auth", check: nonce};
    jQuery.post(ajaxurl, data, function () {
        location.href = '/wp-admin/admin.php?page=jrmevekillboard_settings';
    });
}

// Generate random password token for cronjob endpoints
function generateToken() {
    partOne = Math.random().toString(36).slice(2);
    partTwo = Math.random().toString(36).slice(2);
    document.getElementById('cron_secret').value = partOne+partTwo;
}

// Get log
function getLog() {
    nonce = document.getElementById('wpopnn').value;

    data = {action: "jrm_killboard_do_get_log", check: nonce};
    jQuery.post(ajaxurl, data, function (response) {
        json = JSON.parse(response);
        document.getElementById('modal_content').innerHTML = json.html;
    });
    jQuery('#modal_log').show();
}

// Hide log modal
function hideLogs() {
    jQuery('#modal_log').hide();   
}

// Clean the processing log
function clearLog() {
    nonce = document.getElementById('wpopnn').value;

    data = {action: "jrm_killboard_do_clear_log", check: nonce};
    jQuery.post(ajaxurl, data, function (response) {
        location.reload();
    });
}

// Toggle tab elements
function changeTab() {
    let structures = document.getElementsByClassName('tabstructure');
    let colors = document.getElementsByClassName('tabcolors');
    event.target.parentElement.parentElement.querySelector('.active').classList.remove('active');
    event.target.classList.add('active');
    if (event.target.getAttribute('data-tab') == 'structure') {
        structures[0].classList.remove('d-none');
        structures[1].classList.remove('d-none');
        colors[0].classList.add('d-none');
        colors[1].classList.add('d-none');
    } else {
        structures[0].classList.add('d-none');
        structures[1].classList.add('d-none');
        colors[0].classList.remove('d-none');
        colors[1].classList.remove('d-none');
    }


}

// Multi select kills
function selectKills(mode) {
    let checkboxes = document.getElementsByClassName('multiselect_kill');
    for (i=0;i<checkboxes.length;i++) {
        checkboxes[i].checked = mode;
    }
}

// Process bulk elements
function groupProcessing() {
    let groupAction = document.getElementById('group_action');
    let checkboxes = document.getElementsByClassName('multiselect_kill');
    let ids = [];
    for (i=0;i<checkboxes.length;i++) {
        if (checkboxes[i].checked) {
            ids.push(checkboxes[i].getAttribute('data-value'));
        }
    }
    if (groupAction.value == 'delete') {
        deleteBulk(ids);
    } else {
        toggleBulk(ids,groupAction.value);
    }
}

// Toggle multiple kills
function toggleBulk(ids,mode) {
    nonce = document.getElementById('wpopnn').value;
    let stringIds = ids.join(',');
    let offtime = 1000*(ids.length/5);
    data = {action: "jrm_killboard_toggle_kills", check: nonce, ids: stringIds, toggle:mode };
    jQuery.post(ajaxurl, data, function(response) {
        json = JSON.parse(response);
        if(json.status) {
            location.reload();
        }
    });
}

// Delete multiple kills
function deleteBulk(ids) {
    if (confirm(document.getElementById('group_action').getAttribute('data-delete-all'))) {
        nonce = document.getElementById('wpopnn').value;
        let stringIds = ids.join(',');
        data = {action: "jrm_killboard_delete_bulk", check: nonce, ids: stringIds};
        jQuery.post(ajaxurl, data, function (response) {
            location.reload();
        });
    }
}

// Update items price
function updateItemsPrice(needCallback = false) {
    let div = '';
    if (needCallback) {
        div = '<tr><td colspan="5" align="center" class="bg-warning text-dark">'+document.getElementById('loading-message').value+'</td></tr>';
        document.getElementById('items').querySelector('tbody').innerHTML = div;
    }
    nonce = document.getElementById('wpopnn').value;

    data = {action: "jrm_killboard_do_sync_price", check: nonce, callback: needCallback};
    jQuery.post(ajaxurl, data, function (response) {
        json = JSON.parse(response);
        if (json.status) {
            if (needCallback) {
                data = {action: "jrm_killboard_do_update_items_price", check: json.callback};
                jQuery.post(ajaxurl, data, function (response2) {
                    json2 = JSON.parse(response2);
                    if (json2.status) {
                        location.reload();
                    } else {
                        document.getElementById('jrm_alert').innerHTML  = json.error;            
                    }
                });
            } else {
                location.reload();
            }
        } else {
            document.getElementById('jrm_alert').innerHTML  = json.error;
        }
    });
}

// Enable inline edit price element
function enableEdit() {
    let element = event.target;
    if (element.getAttribute('data-editing') == 'false') {
        element.innerHTML = '';
        let input = document.createElement('input');
        input.type = 'text';
        input.value = element.getAttribute('data-flatprice');
        input.setAttribute('data-value',input.value);
        input.addEventListener('blur',function() {
            this.setAttribute('data-value',this.value);
        });
        element.append(input);
        let save = document.createElement('button');
        save.classList = 'btn btn-sm btn-primary ml-1 mr-1 itemSnippet';
        save.setAttribute('data-type','save');
        save.innerHTML = 'Save';
        save.addEventListener('click',function() {
            processSnippet(event.target)
        });
        element.append(save);
        let abort = document.createElement('button');
        abort.classList = 'btn btn-sm btn-danger itemSnippet';
        abort.setAttribute('data-type','abort');
        abort.innerHTML = 'Close';
        abort.addEventListener('click',function() {
            processSnippet(event.target)
        });
        element.append(abort);
        element.setAttribute('data-editing','true');
    }
}

// Trigger inline action
function processSnippet(element) {
    let div = '<tr><td colspan="5" align="center" class="bg-warning text-dark">'+document.getElementById('loading-message').value+'</td></tr>';
    
    let loader = document.getElementById("jrm_alert");
    let parent = element.parentElement;
    let type = element.getAttribute('data-type');
    if (type == 'abort') {
        parent.innerHTML = parent.getAttribute('data-dressed');
        parent.setAttribute('data-editing','false');
    } else {
        parent.parentElement.outerHTML = div;
        let input = parent.querySelector('input');
        let itemId = parent.getAttribute('data-id');
        let price = input.getAttribute('data-value');
        parent.setAttribute('data-editing','false');
        setPriceValue(itemId,price);
    }
}