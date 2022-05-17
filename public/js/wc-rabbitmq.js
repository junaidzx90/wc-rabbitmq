jQuery(document).ready(function ($) {
    $(".wr-unset_default-billing").on("click", function(){
        let btn = $(this);
        $.ajax({
            type: "post",
            url: wcrabbit_ajax.ajaxurl,
            data: {
                action: "unset_selected_address",
                type: "billing",
                nonce: wcrabbit_ajax.nonce,
            },
            beforeSend: function(){
                btn.prop("disabled", true);
                btn.text("....")
            },
            dataType: "json",
            success: function (response) {
                location.reload();
            }
        });
    });

    $(".wr-unset_default-shipping").on("click", function(){
        let id = $(this).data("id");
        let btn = $(this);
        $.ajax({
            type: "post",
            url: wcrabbit_ajax.ajaxurl,
            data: {
                action: "unset_selected_address",
                type: "shipping",
                nonce: wcrabbit_ajax.nonce,
            },
            beforeSend: function(){
                btn.prop("disabled", true);
                btn.text("....")
            },
            dataType: "json",
            success: function (response) {
                location.reload();
            }
        });
    });


    $(".wr-set_default-billing").on("click", function(){
        let id = $(this).data("id");
        let btn = $(this);
        $.ajax({
            type: "post",
            url: wcrabbit_ajax.ajaxurl,
            data: {
                action: "set_selected_address",
                type: "billing",
                id: id,
                nonce: wcrabbit_ajax.nonce,
            },
            beforeSend: function(){
                btn.prop("disabled", true);
                btn.text("....")
            },
            dataType: "json",
            success: function (response) {
                location.reload();
            }
        });
    });

    $(".wr-set_default-shipping").on("click", function(){
        let id = $(this).data("id");
        let btn = $(this);
        $.ajax({
            type: "post",
            url: wcrabbit_ajax.ajaxurl,
            data: {
                action: "set_selected_address",
                type: "shipping",
                id: id,
                nonce: wcrabbit_ajax.nonce,
            },
            beforeSend: function(){
                btn.prop("disabled", true);
                btn.text("....")
            },
            dataType: "json",
            success: function (response) {
                location.reload();
            }
        });
    });

    $(".wr-delete-billing").on("click", function(){
        let id = $(this).data("id");
        let btn = $(this);
        $.ajax({
            type: "post",
            url: wcrabbit_ajax.ajaxurl,
            data: {
                action: "delete_additional_address",
                type: "billing",
                id: id,
                nonce: wcrabbit_ajax.nonce,
            },
            beforeSend: function(){
                btn.prop("disabled", true);
                btn.text("....")
            },
            dataType: "json",
            success: function (response) {
                btn.parents(".wr-address").remove();
            }
        });
    });

    $(".wr-delete-shipping").on("click", function(){
        let id = $(this).data("id");
        let btn = $(this);
        $.ajax({
            type: "post",
            url: wcrabbit_ajax.ajaxurl,
            data: {
                action: "delete_additional_address",
                type: "shipping",
                id: id,
                nonce: wcrabbit_ajax.nonce,
            },
            beforeSend: function(){
                btn.prop("disabled", true);
                btn.text("....")
            },
            dataType: "json",
            success: function (response) {
                btn.parents(".wr-address").remove();
            }
        });
    });
});