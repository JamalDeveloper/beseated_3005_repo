var Eventgallery = Eventgallery || {};

Eventgallery.jQuery = eventgallery.jQuery;

(function(Eventgallery){

Eventgallery.Tools = {};

Eventgallery.Tools.mergeObjects = function(defaults, options) {
	if (options === null || defaults === null) {
    	return defaults;
    }
    
    for (var key in options) {
	  defaults[key] = options[key];
    }
    
	return defaults;
};

/**
* calculates the border of the given elements with the given properties
*/
Eventgallery.Tools.calcBorderWidth = function(elements, properties) {
    var sum = 0;

    for (var i=0; i<elements.length; i++) {
        for (var j=0; j<properties.length; j++) {
            var value = parseFloat( elements[i].css(properties[j]) );
            if (!isNaN(value)) {
                sum += value;
            }
        }
    }
    
    return sum;    
};

Eventgallery.Tools.addUrlParameter = function(initialUrl, key, value) {
    var url = Eventgallery.Tools.removeUrlParameter(initialUrl, key),
        fragments = url.split('#'),
        urlparts= fragments[0].split('?'),
        result;

    if (urlparts.length === 1) {
        result = urlparts[0] + '?' + encodeURIComponent(key) + "=" + encodeURIComponent(value);
    } else {
        result = urlparts.join('?') + '&' + encodeURIComponent(key) + "=" + encodeURIComponent(value);
    }

    if (fragments.length>1) {
        return result + '#' + fragments[1];
    }

    return result;
};

Eventgallery.Tools.removeUrlParameter = function(url, key) {
    var fragments=url.split('#'),
        urlparts= fragments[0].split('?'),
        result;

    if (urlparts.length>1)
    {
        var prefix= encodeURIComponent(key)+'=';
        var pars= urlparts[1].split('&');

        for (var i=0; i<pars.length; i++) {
            if (pars[i].indexOf(prefix, 0) === 0) {
                pars.splice(i, 1);
            }
        }
        if (pars.length > 0) {
            result = urlparts[0] + '?' + pars.join('&');
        }
        else {
            result = urlparts[0];
        }
    }
    else {
        result =  urlparts[0];
    }

    if (fragments.length>1) {
        return result + '#' + fragments[1];
    }

    return result;
};

})(Eventgallery);   