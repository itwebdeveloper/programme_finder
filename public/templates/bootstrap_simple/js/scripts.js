var programme_finder = {
    resetResult: function () {
        $('#search-result').html('');
    },

    showResult: function (title, short_synopsis, image) {
        var html = '<div class="row bottom-spacing search-result">';
        html += '<div class="col-xs-3 col-sm-3 col-md-3">';
        html += '<img src="'+ image +'" class="img-responsive" alt="'+ title +'" title="'+ title +'" width="160" height="90" />';
        html += '</div>';
        html += '<div class="col-xs-9 col-sm-9 col-md-9">';
        html += '<h4 class="result-title">'+ title +'</h4>';
        html += '<div class="result-short-synopsis">'+ short_synopsis +'</div>';
        html += '</div>';
        html += '</div>';
        html += '<hr />';

        $('#search-result').append(html);
    },

    sendRequest: function (url, search_query) {
        $.ajax({
            url: url,
            data: {
                title: search_query
            },
            dataType: 'json',
            error: function() {},
            success: function(data) {
                var status = data.status;
                var results = data.message.results;
                if(data.message.no_of_results > 0){
                    programme_finder.resetResult();
                    $.each(results, function (index, result) {
                        programme_finder.showResult(result.title, result.short_synopsis, result.image);
                    });
                }
            },
            type: 'GET'
        });
    },
    checkText: function (needle, replace) {
        if ($('#search-result').text() == needle) {
            $('#search-result').html(replace);
        }
    }
}

$( document ).ready(function() {
    var external_proxy_endpoint = $('#programme-finder').data('external-proxy-endpoint');

    $('#search-input').on('input', function(){
        $('#search-result').html('Loading...');
        programme_finder.sendRequest(external_proxy_endpoint, $('#search-input').val());
        setTimeout(function() {
            programme_finder.checkText('Loading...', 'There are no results')
        }, 5000);
    });
});