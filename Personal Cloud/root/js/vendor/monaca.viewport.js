(function() {
    window.monaca = window.monaca || {};

    var IS_DEV = false;
    var d = IS_DEV ? alert : function(line) { console.log(line); };

    var isIos = function() {
        return !!navigator.userAgent.match(/iPhone|iPod|webmate|iPad/);
    };

    var isAndroid = function() {
        return !!navigator.userAgent.match(/Android|dream|CUPCAKE/);
    };

    var defaultParams = {
        width : 640,
        onAdjustment : function(scale) { }
    };

    var merge = function(base, right) {
        var result = {};
        for (var key in base) {
            result[key] = base[key];
            if (key in right) {
                result[key] = right[key];
            }
        }
        return result;
    };

    var zoom = function(ratio) {
        if (document.body) {
            if ("OTransform" in document.body.style) {
                document.body.style.OTransform = "scale(" + ratio + ")";
                document.body.style.OTransformOrigin = "top left";
                document.body.style.width = Math.round(window.innerWidth / ratio) + "px";
            } else if ("MozTransform" in document.body.style) {
                document.body.style.MozTransform = "scale(" + ratio + ")";
                document.body.style.MozTransformOrigin = "top left";
                document.body.style.width = Math.round(window.innerWidth / ratio) + "px";
            } else {
                document.body.style.zoom = ratio;
            }
        }
    };

    if (isIos()) {
        monaca.viewport = function(params) {
            d("iOS is detected");
            params = merge(defaultParams, params);
            document.write('<meta name="viewport" content="width=' + params.width + '" />');
d(params.width);
            monaca.viewport.adjust = function() {};
        };
    } else if (isAndroid()) {
        monaca.viewport = function(params) {
            d("Android is detected");
            params = merge(defaultParams, params);

            document.write('<meta name="viewport" content="width=device-width,target-densitydpi=device-dpi" />');

            monaca.viewport.adjust = function() {
                var scale = window.innerWidth / params.width;
                monaca.viewport.scale = scale;
                zoom(scale);
                params.onAdjustment(scale);
            };

            var orientationChanged = (function() {
                var wasPortrait = window.innerWidth < window.innerHeight;
                return function() {
                    var isPortrait = window.innerWidth < window.innerHeight;
                    var result = isPortrait != wasPortrait;
                    wasPortrait = isPortrait;
                    return result;
                };
            })();

            var aspectRatioChanged = (function() {
                var oldAspect = window.innerWidth / window.innerHeight;
                return function() {
                    var aspect = window.innerWidth / window.innerHeight;
                    var changed = Math.abs(aspect - oldAspect) > 0.0001;
                    oldAspect = aspect;

                    d("aspect ratio changed");
                    return changed;
                };
            });

            if (params.width !== 'device-width') {
                window.addEventListener("resize", function() {
                    var left = orientationChanged();
                    var right = aspectRatioChanged();

                    if (left || right) {
                        monaca.viewport.adjust();
                    }
                }, false);
                document.addEventListener('DOMContentLoaded', function() {
                    monaca.viewport.adjust();
                });
            }
        };
    } else {
        monaca.viewport = function(params) {
            params = merge(defaultParams, params);
            d("PC browser is detected");

            monaca.viewport.adjust = function() {
                var width = window.innerWidth || document.body.clientWidth || document.documentElement.clientWidth;
                var scale = width / params.width;
                zoom(width / params.width);
                params.onAdjustment(scale);
            };

            if (params.width !== 'device-width') {
                window.addEventListener("resize", function() {
                    monaca.viewport.adjust();
                }, false);
                document.addEventListener("DOMContentLoaded", function() {
                    monaca.viewport.adjust();
                });
            }
        };
    }

    monaca.viewport.isIos = isIos;
    monaca.viewport.isAndroid = isAndroid;
    monaca.viewport.isPCBrowser = function() {
        return !isIos() && !isAndroid();
    };
    monaca.viewport.adjust = function() { };
})();


// Sample code moved here -CB

function update(scale) {
    document.getElementById("useragent").textContent = navigator.userAgent;
    document.getElementById("scale").textContent = scale || "none";

    if (monaca.viewport.isIos()) {
        document.getElementById("device").textContent = "iOS";
    } else if (monaca.viewport.isAndroid()) {
        document.getElementById("device").textContent = "Android";
    } else if (monaca.viewport.isPCBrowser()) {
        document.getElementById("device").textContent = "PC";
    }
}

monaca.viewport({
    width : 460,
    onAdjustment : update
});

window.addEventListener("load", function() {
    prettyPrint();
});

if (monaca.viewport.isIos()) {
    window.addEventListener("load", function() {
        update(null);
    });
}