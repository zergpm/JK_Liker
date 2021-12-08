jQuery(document).ready(function() {
    jQuery(document).on('click','.jk-like-post-btn', function() {

        var post_id = jQuery(this).data('post-id');
        var user_id = jQuery(this).data('user-id');
        var this_post = jQuery("[data-post-id='" + post_id + "']");

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
			data: {
                action: 'like_action',
                post_id: post_id,
                user_id: user_id
            },
			success: function(data) {
                if(data == false) {
                    alert('you are not authorized to perform this action'); 
                    return false;
                }
                var likeData = JSON.parse(data);
                this_post.find('.jk-like-post-text').text(likeData.btn_text);
                this_post.find('.jk-like-post-count').text(likeData.count);
            }
        });
    });
});
