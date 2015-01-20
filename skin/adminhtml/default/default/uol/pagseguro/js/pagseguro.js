/**
************************************************************************
Copyright [2015] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************  
*/

var checkboxes = function () {

	var j = 0;
	var ckbTrue = 0;

	jQuery('input[name="send_emails[]"]').each(function() {
		if (jQuery(this).is(':checked') == true) {
			ckbTrue++;
		}
		j++;
	});	
	
	jQuery('input[name="conciliation_orders[]"]').each(function() {
		if (jQuery(this).is(':checked') == true) {
			ckbTrue++;
		}
		j++;
	});	
	
	if (j == ckbTrue) {
		jQuery(':checkbox').prop('checked','');
	} else {
		jQuery(':checkbox').prop('checked','checked');
	}
} 

function setMsgError(msg) {
	var structure = '<ul class="messages">' +
						'<li class="error-msg">' +
							'<ul>' +
								'<li>' +
									'<span>' + msg + '</span>' +
								'</li>' +
							'</ul>' +
						'</li>' +
					'</ul>';
	jQuery('#messages').append(structure);
	jQuery('body,html').animate({scrollTop:0},600);
}

function setMsgSuccess(msg) {
	var structure = '<ul class="messages">' +
						'<li class="success-msg">' +
							'<ul>' +
								'<li>' +
									'<span>' + msg + '</span>' +
								'</li>' +
							'</ul>' +
						'</li>' +
					'</ul>';
	jQuery('#messages').append(structure);
	jQuery('body,html').animate({scrollTop:0},600);
}  
