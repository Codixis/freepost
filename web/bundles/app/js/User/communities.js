(function() {

    var communities;        // List of communities
    var communitiesSubscribed; // List of subscribed communities
    var communitiesItems;   // <div> within communities
    var list;               // Load posts in here
    var search = {          // Search input box
        filter: null,
        results: null,
        
        request: null       /* Keep a reference to the last ajax search request.
                             * This is useful when a user types a word in the search
                             * input box, so that only the last one returns the desired
                             * html code.
                             */
    };
    
    var loadCommunityPosts = function(communityHashId) {
        var url = Routing.generate(
            "freepost_user_read_community_posts",
            {
                communityHashId: communityHashId
            },
            true
        );
        
        $.ajax({
            type:   "post",
            url:    url,
            data:   {},
            dataType:	"html"
        })
        .done(function(response) {
            list.html(response);
        })
        .fail(function(response) {
        });
    };
    
    var setCommunitiesItemsClickHandler = function() {
        // Click event for each community
        communities.find(".community").each(function(index, aCommunity) {
            aCommunity = $(aCommunity);
            
            aCommunity.click(function() {
                loadCommunityPosts(aCommunity.data("hashid"));
                
                communitiesItems.removeClass("selected");
                aCommunity.addClass("selected");
            });
        });
    };
    
    $(document).ready(function() {
        
        communities = $("#communities");
        communitiesSubscribed = $("#communities > .subscribed");
        communitiesItems = communities.find(".community");
        list = $("#list");
        search.filter   = $("#communities > .search > input[name=filter]");
        search.results  = $("#communities > .search > .results");
        
        setCommunitiesItemsClickHandler();
        
        // Select the first community
        communitiesItems.first().click();
        
        // Start searching when user types something
        search.filter.keyup(function() {
            // Kill the last search request
            search.request && search.request.abort();
            
            var name = search.filter.val();
            
            if (name.length < 1)
            {
                search.results.html("").hide();
                communitiesSubscribed.show();
                return;
            }
            
            var url             = Routing.generate(
                "freepost_community_search",
                { communityName: name },
                true
            );
            
            search.request = $.ajax({
                type:   "get",
                url:    url,
                data:   {},
                dataType:	"json"
            })
            .done(function(response) {
                if (response.hasOwnProperty("html"))
                {
                    search.results.html(response.html).show();
                    communitiesSubscribed.hide();
                } else
                {
                    search.results.html("").hide();
                    communitiesSubscribed.show();
                }
                
                setCommunitiesItemsClickHandler();
            })
            .fail(function(response) {
            })
            .always(function(response) {
            });
        });
        
    });
    
})();










