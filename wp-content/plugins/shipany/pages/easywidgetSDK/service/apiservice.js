var { fromEvent, EMPTY, forkJoin } = rxjs;
var { fromFetch } = rxjs.fetch;
var { ajax } = rxjs.ajax;
var {
  catchError,
  debounceTime,
  distinctUntilChanged,
  filter,
  map,
  mergeMap,
  switchMap,
  tap,
  delay,
} = rxjs.operators;

var completeResultArray = [];
function getResponse(url, authUserObject) {
  for (const courier of window.couriers) { // the couriers here is get from paths.js
    if (courier.courier_id == courier_id) {
      locationListEndpoint = courier.location_url
      break
    } 
  }

  const locationData$ = fromFetch(locationListEndpoint, {
    method: "get",
    headers: {
      "Content-Type": "application/json",
      //Authorization: "Bearer " + localStorage.getItem("token"),
    },
  }).pipe(
    switchMap((response) => {
      //console.log("restoken value is "+localStorage.getItem('token'))
      if (response.ok) {
        return response.json();
      } else {
        // Server is returning a status requiring the client to try something else.
        return of({ error: true, message: `Error ${response.status}` });
      }
    })
  );

  const data$ = fromFetch(sysparam, {
    method: "get",
    //body: JSON.stringify(authUserObject),
    headers: {
      "Content-Type": "application/json",
    },
  }).pipe(
    switchMap((response) => {
      if (response.ok) {
        // OK return data

        return response.json();
      } else {
        // Server is returning a status requiring the client to try something else.
        return of({ error: true, message: `Error ${response.status}` });
      }
    }),
    /*
    map((response) => {
      localStorage.setItem("token", response.token);
      //return response.token
    }),
    */
    switchMap(() => {
      return locationData$;
    }),
    /*
    map((response) => {
      return response.docs;
    }),
    */
    mergeMap((locationObject) => locationObject),
    /*
    filter((locationObject) => {
      let lat = locationObject.latitude;
      let long = locationObject.longitude;

      //filtered out locations that have empty/missing values for lat/long

      if (
        lat % 1 != 0 &&
        long % 1 != 0 &&
        lat !== null &&
        long !== null &&
        !isNaN(lat) &&
        !isNaN(long)
      ) {
        let coordinates = ol.proj.fromLonLat([long, lat]);

        if (!isNaN(coordinates[0]) && !isNaN(coordinates[1])) {
          return locationObject;
        }
      }
    }),
    filter((locationObject) => {
      if (locationObject.city !== null) {
        return locationObject;
      }
    }),
    filter((locationObject) => {
      if (locationObject.address1En !== null) {
        return locationObject;
      }
    }),
    filter((locationObject) => {
      if (locationObject.locationType !== "THIRD_PARTY_LOCKER") {
        return locationObject;
      }
    }),
    */
    filter((locationObject) => {
      if (_filterCriteria !== null) {
        //let matchFound;
        for (var prop in _filterCriteria) {
          for (var i = 0; i < _filterCriteria[prop].length; i++) {
            if (_filterCriteria[prop][i] === "SHIPANY_LOCKER")
            {
              if (locationObject.addr.reg === '澳門') locationObject.addr.reg = 'Macau';
              else if (locationObject.addr.reg === '澳門半島') locationObject.addr.reg = 'Macau Peninsula';
              else if (locationObject.addr.reg === '氹仔島') locationObject.addr.reg = 'Taipa';
              else if (locationObject.addr.reg === '路環島') locationObject.addr.reg = 'Ilha de Coloane';
              return locationObject;
            }
          }
        }
      } else {
        return locationObject;
      }
    }),
    filter((locationObject) => {
      if (shipany_setting.shipany_locker_include_macuo != null && shipany_setting.shipany_locker_include_macuo == 'no') {
        if (['澳門', '澳門半島', '氹仔島', '路環島', 'Macao', 'Macau', 'Macau Peninsula', 'Taipa', 'Ilha de Coloane'].includes(locationObject.addr.reg)) {
          return ''
        }
      }
      if (_locale === "zh") {
        locationObject.addr.reg = translateRegion(locationObject.addr.reg)
        locationObject.addr.state = translateDistrict(locationObject.addr.state)
        locationObject.addr.distr = translateArea(locationObject.addr.distr)
      }

      //NEW CODE
      if (_locale === "en") {
        //districtfilterdata
        districtFilterData.push("All Districts");
      } else if (_locale === "zh") {
        districtFilterData.push("全部地區");
      }

      //NEW CODE
      if (_locale === "en") {
        districtFilterData.push("All Districts");
      } else if (_locale === "zh") {
        districtFilterData.push("全部地區");
      }
      districtFilterData.push(locationObject.addr.state);

      //NEW CODE
      if (_locale === "en") {
        regionFilterData.push("All Regions");
      } else if (_locale === "zh") {
        regionFilterData.push("全部區域");
      }
      regionFilterData.push(locationObject.addr.reg);

      if (_locale === "en") {
        areaFilterData.push("All Areas");
      } else if (_locale === "zh") {
        areaFilterData.push("全部範圍");
      }
      areaFilterData.push(locationObject.addr.distr);
      //NEW CODE

      return locationObject;
    }),
    filter((locationObject) => {
      completeResultArray = Array.from(new Set(completeResultArray));
      completeResultArray.push(locationObject);
      return locationObject;
    }),
    catchError((err) => {
      // Network or other error, handle appropriately
      //console.error(err);
      return of({ error: true, message: err.message });
    })
  );

  return data$;
}

function capitaliseString(str) {
  var res = str.split("_");
  var first = res[0][0].toUpperCase() + res[0].slice(1).toLowerCase();
  var last = res[1][0].toUpperCase() + res[1].slice(1).toLowerCase();

  return first + " " + last;
}
