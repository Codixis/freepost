(function() {

    var notification = {
        newReplies: null    // "You have $1 new replies" messages in user menu
    };
    
    var checkUnreadReplies = function() {
        var url = Routing.generate(
            "freepost_user_unread_replies",
            {},
            true
        );
        
        $.ajax({
            type:   "get",
            url:    url,
            data:   {},
            dataType:	"json"
        })
        .done(function(response) {
            if (response.hasOwnProperty("count") && response.count > 0)
                notification.newReplies
                    .text(function() {
                        return $(this).text().replace("$1", response.count);
                    })
                    .fadeIn();
        })
        .fail(function(response) {
        });
    };
    
    $(document).ready(function() {
        // This is defined in "views/Default/User/menu.html.twig"
        notification.newReplies = $(".userMenu .newReplies");
        
        checkUnreadReplies();
    });
    
})();










