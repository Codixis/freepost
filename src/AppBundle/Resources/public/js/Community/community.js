(function() {

    var image;
    var followButton;
    var isFollowing = function(callback) {
        var url = Routing.generate(
            "freepost_user_follows_community",
            {
                communityName: followButton.data("community-name")
            },
            true
        );
        
        $.ajax({
            type:   "post",
            url:    url,
            data:   {},
            dataType:	"json"
        })
        .done(function(response) {
            callback(response && response.follows);
        })
        .fail(function(response) {
            callback(false);
        });
    };
    
    $(document).ready(function() {
        
        image        = $(".communityMenu > .picture > .image");
        followButton = $("#follow");
        
        // If user is signed in and we have a "Follow" button
        if (followButton.length)
        {
            followButton.click(function() {
                
                followButton.hide();
                
                // URL used to send the request
                var url = Routing.generate(
                    "freepost_user_follow",               // route
                    {                                  // route params
                        communityName: followButton.data("community-name")
                    },
                    true                               // absolute URL
                );
                
                $.ajax({
                    type:   "post",
                    url:    url,
                    data:   {},
                    dataType:	"json"
                })
                .done(function(response) {
                })
                .fail(function(response) {
                })
                .always(function(response) {
                });
            });
            
            // Show "Follow" button
            isFollowing(function(follows) {
                !follows && followButton.css("visibility", "visible");
            });
        }
    });
    
})();