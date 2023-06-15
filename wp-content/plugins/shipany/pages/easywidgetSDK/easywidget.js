//init variables
let _mapBoxClassName,
  _defaultLocation,
  _mapType,
  _locale,
  _mode,
  _filterCriteria,
  _key,
  _onSelect,
  _userAuthObject,
  _withSearchBar;

//let completeResultArray = [];
let word_press_path = scriptParams.path;
let courier_id = scriptParams.courier_id;

let lang = scriptParams.lang.includes('zh')?'zh':'en';


//initialise widget
var easyWidget =
  easyWidget ||
  (function () {
    return {
      init: async function (obj) {
        let {
          mapType,
          locale,
          defaultLocation,
          mapBoxClassName,
          mode,
          filter,
          apiKey,
          onSelect,
          userAuthObject,
          searchBar,
        } = obj;
        //set variable names
        _mapBoxClassName = mapBoxClassName || "mapBox";
        _defaultLocation = defaultLocation || "HK";
        _mapType = mapType || "osm";
        _locale = lang || "en";
        _mode = mode || "basic";
        _filterCriteria = filter || null;
        _key = apiKey || "";
        _onSelect = onSelect;
        // _onSelectionCloseModal = onSelectionCloseModal
        _userAuthObject = userAuthObject || null;
        _withSearchBar = searchBar || false;
        // load courier config
        const data = await jQuery.getJSON('https://apps.shipany.io/woocommerce/locationList.json')
        window.couriers = data.couriers
        onLoadComplete();
        //console.log("hello inside here obj is ", obj);
      },
      changeLanguage: function (lang) {
        localStorage.setItem("language", lang);
        let script = document.getElementsByTagName("script");
        console.log(script, typeof script);
      },
      reset: function (obj) {
        let { filter } = obj;
        //set variable names
        console.log("inside filter", filter);
        _filterCriteria = filter || null;

        //onLoadComplete();
      },
    };
  })();

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
//loadscripts
async function loadScripts(scriptURL, obj) {
  await new Promise(function (resolve, reject) {
    var link = document.createElement("script");

    link.src = scriptURL;
    link.id = scriptURL;
    Object.assign(link, obj);
    document.body.appendChild(link);

    link.onload = function () {
      resolve();
    };
  });
}

async function LoadCSS(cssURL) {
  // 'cssURL' is the stylesheet's URL, i.e. /css/styles.css

  await new Promise(function (resolve, reject) {
    var link = document.createElement("link");

    link.rel = "stylesheet";

    link.href = cssURL;

    document.head.appendChild(link);

    link.onload = function () {
      resolve();
    };
  });
}

function removejscssfile(filename, filetype) {
  var targetelement =
    filetype == "js" ? "script" : filetype == "css" ? "link" : "none"; //determine element type to create nodelist from
  var targetattr =
    filetype == "js" ? "src" : filetype == "css" ? "href" : "none"; //determine corresponding attribute to test for
  var allsuspects = document.getElementsByTagName(targetelement);
  for (var i = allsuspects.length; i >= 0; i--) {
    //search backwards within nodelist for matching elements to remove
    if (
      allsuspects[i] &&
      allsuspects[i].getAttribute(targetattr) != null &&
      allsuspects[i].getAttribute(targetattr).indexOf(filename) != -1
    )
      allsuspects[i].parentNode.removeChild(allsuspects[i]); //remove element by calling parentNode.removeChild()
  }
}

async function onLoadComplete() {
  //add search bar
  let mapBarWrapper = document.createElement("div");
  mapBarWrapper.className = "mapBarWrapper";
  mapBarWrapper.id = "mapBarWrapper";
  document.body.appendChild(mapBarWrapper);

  await loadScripts(
    "//unpkg.com/string-similarity/umd/string-similarity.min.js"
  );
  await loadScripts("https://cdnjs.cloudflare.com/ajax/libs/rxjs/6.6.7/rxjs.umd.min.js");
  await loadScripts(
    "https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.15/lodash.core.min.js"
  );
  await loadScripts("https://api.mapbox.com/mapbox-gl-js/v1.10.1/mapbox-gl.js");
  await LoadCSS("https://api.mapbox.com/mapbox-gl-js/v1.10.1/mapbox-gl.css");

  // //internal scripts
  // await loadScripts(
  //   word_press_path +
  //     "pages/easywidgetSDK/lib/constants.js?" +
  //     Date.now().toString()
  // );

  await loadScripts(
    word_press_path +
      "pages/easywidgetSDK/lib/ol_v5.2.0.js?" +
      Date.now().toString()
  ); //load first
  await loadScripts(
    word_press_path + "pages/easywidgetSDK/lib/olms.js?" + Date.now().toString()
  );
  await loadScripts(
    word_press_path +
      "pages/easywidgetSDK/lib/ol-popup.js?" +
      Date.now().toString()
  );
  await loadScripts(
    word_press_path +
      "pages/easywidgetSDK/lib/stringBuilder.js?" +
      Date.now().toString()
  );
  await loadScripts(
    word_press_path +
      "pages/easywidgetSDK/lib/createHTMLElement.js?" +
      Date.now().toString()
  );
  await loadScripts(
    word_press_path +
      "pages/easywidgetSDK/service/paths.js?" +
      Date.now().toString()
  );
  await loadScripts(
    word_press_path +
      "pages/easywidgetSDK/service/apiservice.js?" +
      Date.now().toString()
  );

  //load stylesheet
  await LoadCSS(
    word_press_path +
      "pages/easywidgetSDK/styles/styles.css?" +
      Date.now().toString()
  );

  //_mapType = "osm";
  switch (_mapType) {
    case "osm":
      await loadScripts(
        word_press_path +
          "pages/easywidgetSDK/components/osm-map-merge.js?" +
          Date.now().toString()
      );
      break;
    case "gmap":
      await loadScripts(
        word_press_path + "pages/easywidgetSDK/components/google-map-merge.js"
      );
      await loadScripts(
        "https://polyfill.io/v3/polyfill.min.js?features=default"
      );
      await loadScripts(
        "https://maps.googleapis.com/maps/api/js?key=AIzaSyDClncaGv1LsWxOUd6JJQ4ZOhQcFLnsK4k&callback=initMap&libraries=&v=weekly"
      );
      // await loadScripts('./easywidgetSDK/components/google-map.js')
      break;
  }
}

