jQuery(function( $ ) {
	'use strict';

	change_name();
	function change_name(){
		$("#wcrm_rows").children("tr").each(function(){
			let index = (new Date()).getTime();
			$(this).find("input, select").each(function(){
				let name = $(this).attr("name");
				$(this).attr("name", name.replace("[]", "["+index+"]"))
			});
		})
	}

	$("#add_address").on("click", function(e){
		e.preventDefault();

		let element = `<tr> <td> <input type="hidden" name="wcrm_fields[][id]" value=""> <select name="wcrm_fields[][type]" id="type_of_address"> <option value="billing">Billing</option> <option value="shipping">Shipping</option> </select> </td> <td> <input type="number" name="wcrm_fields[][address_id]" id="wcrm_address_id" /> </td> <td> <input type="text" name="wcrm_fields[][city]" id="wcrm_city" /> </td> <td> <input type="text" name="wcrm_fields[][country]" id="wcrm_country" /> </td> <td> <input type="text" name="wcrm_fields[][address_line]" id="address_line" /> </td> <td style="width: 20px; position: relative;"> <span class="removeAddr">+</span> </td></tr>`;

		$(".noaddr").remove();
		$("#wcrm_rows").append(element);
		change_name();
	});

	$(document).on("click", ".removeAddr", function(){
		$(this).parents("tr").remove();
	});

	$("#import_rabbit_users_btn").on("click", function () {
		if ($("#import_rabbit_users").val().toLowerCase().lastIndexOf(".csv") !== -1) {
			if (typeof (FileReader) != "undefined") {
				let reader = new FileReader();
				reader.onload = function (e) {
					let rows = e.target.result.split("\n");
					$.ajax({
						type: "post",
						url: wcrabbit_ajax.ajaxurl,
						data: {
							action: "import_customers",
							nonce: wcrabbit_ajax.nonce,
							rows: rows
						},
						beforeSend: function(){
							$("body").append(`<div class="rabbitLoader"> <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="50px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve"> <path opacity="0.2" fill="#000" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946 s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634 c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"></path> <path fill="#2271b1" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0 C22.32,8.481,24.301,9.057,26.013,10.047z"> <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.9s" repeatCount="indefinite"></animateTransform> </path> </svg> </div>`);
						},
						dataType: "json",
						success: function (response) {
							if(response.added || response.updated){
								$(".rabbitLoader").remove();
								$("#import_rabbit_users").val("");
								let updated = ` and ${response.updated} customers imported`;
								let alertMsg = `<div style="margin-left: 0" class="notice notice-success"><p>${response.added} customers added${updated}.</p></div>`;
								$("#importAlert").html(alertMsg);
							}

							if(response.error){
								$("#import_rabbit_users").val("");
								$(".rabbitLoader").remove();

								let alertMsg = `<div style="margin-left: 0" class="notice error"><p>There is a problem with the file.</p></div>`;
								$("#importAlert").html(alertMsg);
							}
						}
					});
					
				}
				reader.readAsText($("#import_rabbit_users")[0].files[0]);
			} else {
				alert("This browser does not support HTML5.");
			}
		} else {
			$(this).val("")
			alert("Please upload a valid CSV file.");
		}
	});


	$("#import_rabbit_addresses_btn").on("click", function () {
		if ($("#import_rabbit_addresses").val().toLowerCase().lastIndexOf(".csv") !== -1) {
			if (typeof (FileReader) != "undefined") {
				let reader = new FileReader();
				reader.onload = function (e) {
					let rows = e.target.result.split("\n");
					$.ajax({
						type: "post",
						url: wcrabbit_ajax.ajaxurl,
						data: {
							action: "import_customer_address",
							nonce: wcrabbit_ajax.nonce,
							rows: rows
						},
						beforeSend: function(){
							$("body").append(`<div class="rabbitLoader"> <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="50px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve"> <path opacity="0.2" fill="#000" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946 s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634 c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"></path> <path fill="#2271b1" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0 C22.32,8.481,24.301,9.057,26.013,10.047z"> <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.9s" repeatCount="indefinite"></animateTransform> </path> </svg> </div>`);
						},
						dataType: "json",
						success: function (response) {
							if(response.added || response.updated){
								$(".rabbitLoader").remove();
								$("#import_rabbit_addresses").val("");
								
								let updated = ` and ${response.updated} address imported`;
								let alertMsg = `<div style="margin-left: 0" class="notice notice-success"><p>${response.added} address added${updated}.</p></div>`;

								$("#importAlert").html(alertMsg);
							}

							if(response.error){
								$("#import_rabbit_addresses").val("");
								$(".rabbitLoader").remove();

								let alertMsg = `<div style="margin-left: 0" class="notice error"><p>There is a problem with the file.</p></div>`;
								$("#importAlert").html(alertMsg);
							}
						}
					});
					
				}
				reader.readAsText($("#import_rabbit_addresses")[0].files[0]);
			} else {
				alert("This browser does not support HTML5.");
			}
		} else {
			$(this).val("")
			alert("Please upload a valid CSV file.");
		}
	});
});
