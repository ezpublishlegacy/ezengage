var eZEngageSettings = {
    engageClickLimit: 10,
    engageBufferLimit: 150,
    engageVisitedLimit: 20,
    authorScoreWeight: 2,
    idScoreWeight: 10,
    tagsScoreWeight: 1
};

var EngageStorage = {
    getEngageVariable: function( $name, $session )
    {
        $session = (typeof $session !== 'undefined') ?  $session : false;
        if(!$session)
        {
            return JSON.parse( localStorage.getItem( $name ) );
        }
        else
        {
            return JSON.parse( sessionStorage.getItem($name) );
        }
    },
    storeEngageVariable: function( $name, $value, $session )
    {
        $session = (typeof $session !== 'undefined') ?  $session : false;
        if(!$session)
        {
            localStorage.setItem( $name, JSON.stringify( $value ) );
        }
        else
        {
            sessionStorage[$name] = JSON.stringify( $value );
        }
    }
}

var EngageUtils = {
    removeItemFromObjectArray: function($value, $array, $attribute)
    {
        $result =  [];
        $comparisonValue = typeof $value == "object" ? $value[$attribute] : $value;
        for( var x in $array )
        {
            if( $comparisonValue != $array[x][$attribute] )
            {
                $result.push($array[x]);
            }
        }
        return $result;
    },
    removeItemsByIdFromRecommendedArray: function( contentIDs, recommendedItems )
    {
        var result = [];
        for( var x in recommendedItems )
        {
            for( var y in contentIDs )
            {
                recommendedItems[x].values = EngageUtils.removeItemFromObjectArray(contentIDs[y], recommendedItems[x].values, 'id');
            }
            if( recommendedItems[x].values.length > 0 )
            {
                result.push(recommendedItems[x]);
            }
        }
        return result;
    },
    addEvent: function(obj, evt, fn)
    {
        if (obj.addEventListener) {
            obj.addEventListener(evt, fn, false);
        }
        else if (obj.attachEvent) {
            obj.attachEvent("on" + evt, fn);
        }
    }
}

function EngageUser()
{
    // assigns a value for the user id from the local storage
    this.uid = EngageStorage.getEngageVariable("uid", true);
    // if it is null create a new uid and save it in the local storage
    if( !this.uid )
    {
        this.uid = Math.random().toString(36).substr(2, 10) + '_' + Date.now();
        EngageStorage.storeEngageVariable( "uid", this.uid, true );
    }
    
    this.visitedContent = EngageStorage.getEngageVariable("visitedContent");
    if( !this.visitedContent )
    {
        this.visitedContent = [];
    }
    this.recommendedItems = [];
    this.updateVisitedContent = function( data )
    {
        var oldItem = this.visitedContent.filter(function(e) { return e.id == data.id; }).shift();
        if( typeof oldItem != "undefined" )
        {
            data.hits += oldItem.hits;
        }
        this.visitedContent = EngageUtils.removeItemFromObjectArray(data, this.visitedContent, 'id');

        this.visitedContent.unshift(data);
        if( this.visitedContent.length > eZEngageSettings.engageVisitedLimit )
        {
            this.visitedContent = this.visitedContent.slice( 0, eZEngageSettings.engageVisitedLimit );
        }
        EngageStorage.storeEngageVariable( "visitedContent", this.visitedContent );
    };
    this.sendMessage = function( callBack )
    {
        if( typeof(Storage) == 'undefined' || typeof(JSON) == 'undefined' )
        {
            return;
        }
        var currentUser = this;
        $.ajax({
            type: "GET",
            url: '/ezengage/message',
            data: { 'uid': currentUser.uid, 'message': JSON.stringify( currentUser.visitedContent ) },
            success: function(response){
                var responseItems = [];
                // loop through all the response items
                // assigns a score for each of them
                for(var y in response)
                {
                    if(y != currentUser.uid )
                    {
                        var dataScore = { 
                            score: 0
                            , values: []
                            , authorScore: 0
                            , tagsScore: 0
                            , idScore: 0
                        };
                        responseArray = JSON.parse( response[y] );
                        for(var x in currentUser.visitedContent)
                        {
                            //var baseItem = responseArray.filter(function(e) { return e.id == currentUser.visitedContent[x].id; }).shift();
                            for( var z in  responseArray)
                            {
                                if( responseArray[z].id == currentUser.visitedContent[x].id )
                                {
                                    dataScore.idScore++;
                                }
                                if( responseArray[z].owner = currentUser.visitedContent[x].owner )
                                {
                                    dataScore.authorScore++;
                                }
                                if(typeof currentUser.visitedContent[x].tags != "undefined" && typeof responseArray[z].tags != "undefined")
                                {
                                    var comparedTags1 = currentUser.visitedContent[x].tags.split(',');
                                    var comparedTags2 = responseArray[z].tags.split(',');
                                    $.map(comparedTags1, $.trim);
                                    $.map(comparedTags2, $.trim);
                                    dataScore.tagsScore = comparedTags1.filter(function(n) { return comparedTags2.indexOf(n) !== -1; }).length;
                                }
                                dataScore.score = eZEngageSettings.idScoreWeight*dataScore.idScore + eZEngageSettings.authorScoreWeight*dataScore.authorScore + eZEngageSettings.tagsScoreWeight*dataScore.tagsScore;
                            }
                        }
                        dataScore.values = responseArray;
                        responseItems.push(dataScore);
                    }
                }
                // sorts all data score items
                responseItems.sort(function(a, b) {
                    return b.score - a.score;
                });
                if( responseItems.length )
                {
                    // remove all repeated items from the response items
                    var recommendedItems = [];
                    for( var x in responseItems )
                    {
                        // make a copy of the attribute instead of assigning by reference
                        var newValues =  JSON.parse(JSON.stringify(responseItems[x]));
                        for( var y in responseItems[x].values )
                        {
                            // check if the recommendedItems array contains the current item value
                            for( var z in recommendedItems )
                            {
                                if ( recommendedItems[z].values.filter(function(e) {
                                    return e.id == responseItems[x].values[y].id;
                                }).length > 0 )
                                {
                                    var extraData = { hits: responseItems[x].values[y].hits, id: responseItems[x].values[y].id };
                                    newValues.values = EngageUtils.removeItemFromObjectArray(responseItems[x].values[y], newValues.values, 'id');
                                    for( var a in recommendedItems[z].values )
                                    {
                                        if( recommendedItems[z].values[a].id == extraData.id )
                                        {
                                            recommendedItems[z].values[a].hits += extraData.hits;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        if( newValues.values.length > 0 )
                        {
                            recommendedItems.push( JSON.parse(JSON.stringify(newValues)) );
                        }
                    }
                    // remove current viewed item, could be all content ids on screen
                    var contentIDs = [];
                    // get current content id
                    contentIDs.push( $('#engage-page').attr( 'data-contentId' ) );
                    currentUser.recommendedItems = EngageUtils.removeItemsByIdFromRecommendedArray( contentIDs, recommendedItems );
                    if( typeof callBack ==  "function" )
                    {
                        callBack();
                    }
                }
            },
            dataType: 'json'
        });
    };
}

var eZEngage = {
    user: new EngageUser(),
    bufferedItems: EngageStorage.getEngageVariable("bufferedItems"),
    dataEngageClickEventCallBack: function( $clickedLink )
    {
        if( $clickedLink.attr('target') )
        {
            window.open($clickedLink.attr('href'), $clickedLink.attr('target') );
        }
        else
        {
            window.location.href = $clickedLink.attr('href');
        }
    },
    createEngageLinksWithinContent: function( $container, $engageType, $params )
    {
        $params = (typeof $params !== 'undefined') ?  $params : {};
        $container.find('a').each(function() {
            jQuery(this).on( 'click', function(event){
                event.preventDefault();
                var engageClicks = EngageStorage.getEngageVariable("engageClicks");
                if( !engageClicks )
                {
                    engageClicks = [];
                }
                engageClicks.unshift({
                            type: $engageType
                            , text: jQuery.trim( $(this).text() )
                            , params: $params
                        });
                if( engageClicks.length > eZEngageSettings.engageClickLimit )
                {
                    engageClicks = engageClicks.slice( 0, eZEngageSettings.engageClickLimit );
                }
                EngageStorage.storeEngageVariable( "engageClicks", engageClicks );
                eZEngage.dataEngageClickEventCallBack( $(this) );
            });
        });
    },
    createEngageLinksEvents: function()
    {
        if( EngageStorage.getEngageVariable("hits", true) )
        {
            EngageStorage.storeEngageVariable( "hits", Number( EngageStorage.getEngageVariable("hits", true) ) + 1, true );
        }
        else
        {
            EngageStorage.storeEngageVariable( "hits", 1, true );
        }
    },
    extractEngageListData: function()
    {
        var $items = jQuery('.engage-list-item');
        var $results = [];
        $items.each(function() {
            var dataObject = {};
            $(this).find('[data-engage-key]').each(function() {
                var type = $(this).attr('data-engage-type');
                switch(type) {
                    case 'value':
                        dataObject[$(this).attr('data-engage-key')] = $(this).attr('data-engage-value');
                        break;
                    case 'text':
                        dataObject[$(this).attr('data-engage-key')] = jQuery.trim( $(this).text() );
                        break;
                    case 'image':
                        if( typeof $(this).attr('src') !== "undefined" )
                        {
                            dataObject[$(this).attr('data-engage-key')] = $(this).attr('src');
                        }
                        else
                        {
                            dataObject[$(this).attr('data-engage-key')] = $(this).find('img').attr('src');
                        }
                        break;
                    case 'link':
                        if( typeof $(this).attr('href') !== "undefined" )
                        {
                            dataObject[$(this).attr('data-engage-key')] = $(this).attr('href');
                        }
                        else
                        {
                            dataObject[$(this).attr('data-engage-key')] = $(this).find('a').attr('href');
                        }
                        break;
                }
            });
            $results.push(dataObject);
        });
        return $results;
    },
    addItemsToBuffer: function( $items )
    {
        for( var x in $items )
        {
            var found = false;
            for( var y in this.bufferedItems )
            {
                if( this.bufferedItems[y].id == $items[x].id )
                {
                    found = true;
                    break;
                }
            }
            if( !found )
            {
                this.bufferedItems.unshift($items[x]);
            }
        }
        if( this.bufferedItems.length > eZEngageSettings.engageBufferLimit )
        {
            this.bufferedItems = this.bufferedItems.slice( 0, eZEngageSettings.engageBufferLimit );
        }
        EngageStorage.storeEngageVariable( "bufferedItems", this.bufferedItems );
    },
    run: function()
    {
        if( typeof(Storage) != 'undefined' && typeof(JSON) != 'undefined' )
        {
            if( !this.bufferedItems )
            {
                this.bufferedItems = [];
            }
            if( $('#engage-page').attr( 'data-classIdentifier' ) == 'article' )
            {
                var contentID = $('#engage-page').attr( 'data-contentId' );
                var ownerID = $('#engage-page').attr( 'data-ownerId' );
                var tags = $('#engage-page').attr( 'data-tags' );
                var data = {id: contentID, owner: ownerID, hits: 1, tags: tags };
                this.user.updateVisitedContent(data);
            }
            this.createEngageLinksEvents();
            this.bufferableItemsOnPage = this.extractEngageListData();
            this.addItemsToBuffer(this.bufferableItemsOnPage);
        }
    }
};

function injectItemsRightMenu()
{
    var $excludeItems = eZEngage.bufferableItemsOnPage;
    var bufferedItems = eZEngage.bufferedItems;
    if( !$excludeItems )
    {
        $excludeItems = [];
    }
    if( !bufferedItems )
    {
        bufferedItems = [];
    }
    var data = [];
    for( var x in bufferedItems )
    {
        var found = false;
        for( var y in $excludeItems )
        {
            if( bufferedItems[x].id == $excludeItems[y].id || bufferedItems[x].id == $('#engage-page').attr( 'data-contentId' ) )
            {
                found = true;
                break;
            }
        }
        if( !found && bufferedItems[x].link != window.location.pathname )
        {
            data.push(bufferedItems[x]);
        }
    }
    var source   = $("#ezengage_test_html").html();
    var template = Handlebars.compile( source );
    var html    = template( {items: data} );
    $( '.content-view-aside' ).append(html);
}

function onEngageMessageSent()
{
    console.log(eZEngage.user.recommendedItems);
    if( eZEngage.user.recommendedItems.length > 0 && eZEngage.user.recommendedItems[0].values.length > 0 )
    {
        // will recommend the article with highest number of hits
        eZEngage.user.recommendedItems[0].values.sort(function(a, b) {
            return b.hits - a.hits;
        });
        $.ajax({
            type: "GET",
            url: '/ezengage/get_objects',
            data: { 'ids': eZEngage.user.recommendedItems[0].values[0].id },
            success: function(response){
                if( Array.isArray(response) && response.length > 0 )
                {
                    var source   = $("#ezengage_notification_html").html();
                    var template = Handlebars.compile( source );
                    var html    = template( {items: response} );
                    $(window).scroll(function() {
                        var hT = $('.main-content').offset().top,
                            hH = $('.main-content').outerHeight(),
                            wH = $(window).height(),
                            wS = $(this).scrollTop();
                        if (wS > (hT+hH-wH) && !$( '.engage-suggestion' ).hasClass('shown')){
                            $( '.engage-suggestion' ).addClass('shown');
                            $( '.engage-suggestion' ).html(html).slideDown();
                            setTimeout(function() { $( '.engage-suggestion' ).slideUp() }, 10000);
                        }
                     });
                    
                }
            },
            dataType: 'json'
        });
    }
}

jQuery(document).ready(function($) {
    eZEngage.run();
    injectItemsRightMenu();
    eZEngage.createEngageLinksWithinContent( jQuery('.navbar.extra-navi ul'), 'menu-link', {test:1, test2:3} );
    eZEngage.createEngageLinksWithinContent( jQuery('header .span8'), 'brand-link', {tes232t:1, test2:3} );
    EngageUtils.addEvent(document, "mouseout", function engagemouseleave(e) {
        e = e ? e : window.event;
        var from = e.relatedTarget || e.toElement;
        var to = e.target || e.srcElement;
        if ( ( !from || from.nodeName == "HTML" ) && jQuery(to).closest( 'header' ).length ) {
            //console.log("left window");
            e.currentTarget.removeEventListener(e.type, engagemouseleave);
        }
    });
});

jQuery(window).load(function() {
    eZEngage.user.sendMessage( onEngageMessageSent );
});