var rad = function(x) {
    return x * Math.PI / 180;
  };
  // 0: lng 1: lat
  var getDistance = function(p1, p2) {
    var R = 6378137; // Earth’s mean radius in meter
    var dLat = rad(p2[1] - p1[1]);
    var dLong = rad(p2[0] - p1[0]);
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(rad(p1[[1]])) * Math.cos(rad(p2[1])) *
      Math.sin(dLong / 2) * Math.sin(dLong / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c;
    return d; // returns the distance in meter
  };
  
  // Create Circle
  function createCircle(lat,lng,radius){
      var circle = new google.maps.Circle({
      strokeColor: '#00a4e4',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: '#00a4e4',
      fillOpacity: 0.35,
      map: map,
      center: {lat:lat, lng:lng},
      radius: radius
      })
      return circle
      };
  
  // Init
  var map;

  function initFilter(){
      var select = document.createElement('select');
      select.classList.add('catFilter');
      var defaultFilter = document.createElement('option');
      defaultFilter.value = 'All';
      defaultFilter.text = 'All';
      select.appendChild(defaultFilter);
      categories.forEach((cat)=>{
          var option = document.createElement('option');
          option.value = cat;
          option.text = cat;
          select.appendChild(option);
      });
      document.getElementById('floating-panel').appendChild(select);
  }
  initFilter();

  // Init
  function initMap() {

      console.log(points)
  
      map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: -34.397, lng: 150.644},
          zoom: 8
      });
  
      // Load data
    //   map.data.loadGeoJson('list.json');
    map.data.loadGeoJson('list.json');

    

    // console.log(map.data.setStyle({visible:false}));
    // map.data.setStyle(function(feature){
    //     console.log(feature.j);
    // })

  
      document.getElementById('submit').addEventListener('click', function() {
              geocodeAddress(geocoder, map);
          });
  
  
  
      // Geocode user location
      var geocoder = new google.maps.Geocoder();
      var shape;
  
  
      function geocodeAddress(geocoder, resultsMap) {

          var address = document.getElementById('address').value;
  
          geocoder.geocode({'address': address}, function(results, status) {
              if (status === 'OK') {
                  resultsMap.setCenter(results[0].geometry.location);
  
                  
                  var marker = new google.maps.Marker({
                      map: resultsMap,
                      position: results[0].geometry.location  
                  });
  
                  if(shape){
                      shape.setMap(null);
                  };
                  
                  document.querySelector('.storeList').innerHTML = '';
  
                  var mylat = results[0].geometry.location.lat();
                  var mylng = results[0].geometry.location.lng(); 
                  var radius = document.getElementById('radiusSelect').value * 1609.34;
                  shape = createCircle(mylat,mylng,radius);
                  var center = [shape.center.lng(), shape.center.lat()];
                  
                  var table = document.createElement('table');
                  table.classList.add('storeTable');
                  table.innerHTML = `<thead><th>Store</th><th>Type</th><th>Address</th><th>Phone</th><thead><tbody class="storeTbody"></tbody>`;
                  document.querySelector('.storeList').appendChild(table);

                  var returnPoints = [];
                  var returnAdds = [];

                  // Apply filters
                  var filter = document.querySelector('.catFilter').value !== 'All';
                  console.log(filter);
                  function filterType(){
                    if(filter) {
                        var selCat = document.querySelector('.catFilter').value;
                        var selection = points[0].features.filter(point => point.properties.category == selCat);
                        console.log(selection);
                        returnPoints.push(selection);
                        
                    }
                    return selCat;
                }
                  
                console.log(returnPoints);                


                  // Loop through points, return all within radius
                  points[0].features.forEach((point)=>{
                      var storeCoords = point.geometry.coordinates;
                      if(getDistance(center,storeCoords) < radius){

                        if(filter){
                            console.log(filterType());
                            console.log(point.properties.category);
                            if(filterType() == point.properties.category){
                                returnAdds.push(point.properties.address);
                                var el = document.createElement('tr');
                                el.classList.add('storeResult');
                                el.innerHTML = `<td class="store">${point.properties.name}</td><td class="cat">${point.properties.category}</td><td class="address">${point.properties.address}</td><td class="phone">${point.properties.phone}</td>`;
                                document.querySelector('.storeTbody').appendChild(el);
                            }
                        }else{
                          returnAdds.push(point.properties.address);
                          var el = document.createElement('tr');
                          el.classList.add('storeResult');
                          el.innerHTML = `<td class="store">${point.properties.name}</td><td class="cat">${point.properties.category}</td><td class="address">${point.properties.address}</td><td class="phone">${point.properties.phone}</td>`;
                          document.querySelector('.storeTbody').appendChild(el);
                        }
                      }
                  });

                //   document.querySelectorAll('tr.storeResult').forEach((res)=>{
                //       res.addEventListener('click',()=>{
                //           var address = res.childNodes[2].innerText;
                //           console.log(address);
                //           console.log(map.Data)
                //       })
                //   })

                // TODO:  Hide features not in results

                // map.data.setStyle(function(feature){
                //     var markerAdd = feature.j.address;
                //     // console.log(returnAdds);
                //     if(! returnAdds.includes(markerAdd)){
                //         feature.setStyle({visible:false})

                //     }else{
                //         console.log(feature.j.address);
                //     }

                    
                // })

  
  
                
              
              } else {
              alert('Geocode was not successful for the following reason: ' + status);
          }

      
         
      })};
  
    
  
        // Popups
        map.data.addListener('click', event => {
  
          let category = event.feature.getProperty('category');
          let name = event.feature.getProperty('name');
          let phone = event.feature.getProperty('phone');
          let address = event.feature.getProperty('address');
          let position = event.feature.getGeometry().get();
          let content = `
              <img style="float:left; width:200px; margin-top:30px"">
              <div style="margin-left:220px; margin-bottom:20px;">
              <h2>${name}</h2><p>${address}</p>
              <br/><b>Phone:</b> ${phone}</p>
              
              </div>
          `;
  
          var infoWindow = new google.maps.InfoWindow({
          content: name
          });
  
          infoWindow.setContent(content);
          infoWindow.setPosition(position);
          infoWindow.setOptions({pixelOffset: new google.maps.Size(0, -30)});
          infoWindow.open(map);
          });
   
         

          
      }

  

  