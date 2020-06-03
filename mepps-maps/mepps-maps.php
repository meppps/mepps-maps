<?php 
/** 
 * @package meppsmaps
*/
/*
Plugin Name: Mepps Maps - Store Locator
Plugin URI: http://github.com/meppps
Description: Integrate Google Maps into your site with store locator functionality.
Version: 1.0.0
Author: Mikey Epps
Author URI: http://github.com/meppps
License: GPLv2 or later
*/

// security precaution
if(!defined('ABSPATH')){
    exit;
}

// Load scripts
// require_once(plugin_dir_path(__FILE__).'/includes/mps-maps-scripts.php');

// Load class
// require_once(plugin_dir_path(__FILE__).'/includes/mepps-maps-class.php');



// add_shortcode('mpsmaps','mps_store_locator');

// add_action('admin_menu', 'mps_maps_admin_menu');


// ===================================================== // 
// ============ CLIENT SIDE LOCATOR ==================== // 
// ===================================================== // 


function mps_store_locator(){

    wp_enqueue_script('mps-main-script', plugins_url(). '/mepps-maps/js/main.js');

    $jsonPath = plugin_dir_path( __FILE__ ) . '/data.json';
    $data = file_get_contents(plugin_dir_path( __FILE__ ) . '/data.json');
    $json = json_encode($data);
    $apiKey = file_get_contents(plugin_dir_path( __FILE__ ) . '/data/key.txt');
    // $indexJs = plugin_dir_path( __FILE__ ) . '/index.js';
    // $tempJs =  plugin_dir_path( __FILE__ ) . '/temp.js';
    
  
    
    ?>
    <style>
        /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
            height: 70%;
            /* width: 70%; */
            /* width: 800px !important; */
        }


        /* Optional: Makes the sample page fill the window. */
        html,
        body {
            height: 75%;
            margin: 0;
            padding: 0;
        }

        #floating-panel {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
            font-family: 'Roboto', 'sans-serif';
            line-height: 30px;
            /* width: 70%; */
        }

        .storeTable{
            margin: auto;
            margin-top: 5px;
        }

        .storeTable > th{
            font-size: 20px;
            font-weight: bold;
        }

        td.store, td.cat, td.address, td.phone {
            padding: 10px;
            font-size: 18px;
        }
        
        #nearby{
            text-align: center;
            margin: auto;
            padding: 10px;
            font-size: 30px;
        }

        input#address ,input#submit, select#radiusSelect, select.catFilter, #showAll{
            height: 27px;
            border-radius: 3px;
            border: 1.5px solid black;
            padding: auto;
        }

    </style>
    </head>

    <body>

    <div id="floating-panel">
            
            <label for="address">Search location:</label>
            <input type="text" id="address" size="15"/>
            <input id="submit" type="button" value="Search">

            <button id="showAll">Show All Locations</button>

        <label for="radiusSelect">Radius:</label>
        <select id="radiusSelect" label="Radius">
                <option value="5" selected>5 Miles</option>
                <option value="25" selected>25 Miles</option>
                <option value="50" selected>50 Miles</option>
                <option value="100" selected>100 Miles</option>
                <option value="200">200 Miles</option>
                <option value="300">300 Miles</option>
                <option value="400">400 Miles</option>
                <option value="500">500 Miles</option>
        </select>

        <label for="filter">Filter:</label>
        <select class="catFilter"></select>

        </div>

        <div id="map"></div>



        <h2 id="nearby">Nearby Locations</h2>
        <div class="storeList"></div>


    </body>

    <script>
    // Load GEO JSON
    const points = [];
    const categories = [];
    var markers = [];

    var json = JSON.parse(<?php print($json) ?>);

    points.push(json);        
        points[0].features.forEach((feature)=>{
        var cat = feature.properties.category;
        if(! categories.includes(cat)){
            categories.push(cat);
        }
        
    });
        


    </script>

    <script>
    // Haversine formula to calculate distance
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
        return d; // returns the distance in meters
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
    

    // Initialize
    

    function initFilter(){
        var select = document.querySelector('select.catFilter')
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

    var map;

    // Init
    function initMap() {

        
    
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: -34.397, lng: 150.644},
            zoom: 8
        });

        // Set to user location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
            initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            map.setCenter(initialLocation);
        });
        }

        function showAll(markers){
            if(document.querySelector('.storeTbody')){
                    document.querySelector('.storeTbody').innerHTML = '';
            };
            markers.forEach((marker)=>{
                marker.setVisible(true);
                var el = document.createElement('tr');
                el.classList.add('storeResult');
                el.innerHTML = `<td class="store">${marker.name}</td><td class="cat">${marker.category}</td><td class="address">${marker.address}</td><td class="phone">${marker.phone}</td>`;
                document.querySelector('.storeTbody').appendChild(el);
            })
        }       
        
        function createTable(){
            var table = document.createElement('table');
            table.classList.add('storeTable');
            table.innerHTML = `<thead><th>Store</th><th>Type</th><th>Address</th><th>Phone</th><thead><tbody class="storeTbody"></tbody>`;
            document.querySelector('.storeList').appendChild(table);
        }

        createTable();

   
        // Load data
        //   map.data.loadGeoJson('list.json');
       

        points[0].features.forEach((feature)=>{
            var latLng = {lat: feature.geometry.coordinates[1], lng: feature.geometry.coordinates[0]};

            var marker = new google.maps.Marker({
                position: latLng,
                name: feature.properties.name,
                address: feature.properties.address,
                phone: feature.properties.phone,
                category: feature.properties.category,
                storeId: feature.properties.storeid

            });

            markers.push(marker);


            var contentString = `
            <strong>Store: </strong>${feature.properties.name}<br/>
            <strong>Address: </strong>${feature.properties.address}<br/>
            <strong>Phone: </strong>${feature.properties.phone}<br/>
            <strong>Type: </strong>${feature.properties.category}<br/>
            `;
            var infowindow = new google.maps.InfoWindow({
             content: contentString
            });

            marker.setMap(map);
            marker.addListener('click',()=>{
                infowindow.open(map, marker);
            });

        })

        
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
                    
                    
       
                    markers.forEach((marker)=>{
                        marker.setVisible(true);
                    });
      

                    
                    document.querySelector('.storeList').innerHTML = '';
    
                    var mylat = results[0].geometry.location.lat();
                    var mylng = results[0].geometry.location.lng(); 
                    var radius = document.getElementById('radiusSelect').value * 1609.34;
                    shape = createCircle(mylat,mylng,radius);
                    var center = [shape.center.lng(), shape.center.lat()];
                    

                    var returnPoints = [];
                    var returnAdds = [];

                    // Apply filters
                    var filter = document.querySelector('.catFilter').value !== 'All';
                    function filterType(){
                        if(filter) {
                            var selCat = document.querySelector('.catFilter').value;
                            var selection = points[0].features.filter(point => point.properties.category == selCat);
                            returnPoints.push(selection);
                            
                        }
                        return selCat;
                    }           


                    // Loop through points, return all within radius
                    // TODO: Create reusable table function
                    createTable();
                    points[0].features.forEach((point)=>{
                        var storeCoords = point.geometry.coordinates;
                        if(getDistance(center,storeCoords) < radius){
                            // console.log(document.querySelector('.storeTbody'))
                            if(filter){
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

                    // TODO: Show when clicked in table DONE 
                    // TODO: Turn into function avoid repeat code

                      document.querySelectorAll('tr.storeResult').forEach((res)=>{


                          res.addEventListener('click',()=>{
                              var address = res.childNodes[2].innerText;

                              var match = markers.filter(m => m.address == address)[0];
                              

                              var contentString = `
                                <strong>Store: </strong>${match.name}<br/>
                                <strong>Address: </strong>${match.address}<br/>
                                <strong>Phone: </strong>${match.phone}<br/>
                                <strong>Type: </strong>${match.category}<br/>
                                `;
                                var infowindow = new google.maps.InfoWindow({
                                content: contentString
                                });

                              infowindow.open(map, match);

                          })
                      })
                     
                    // TODO:  Hide features not in results
                    markers.forEach((marker)=>{
                        if(! returnAdds.includes(marker.address)){
                            marker.setVisible(false);
                        }
                    })

             

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
                    alert('Geocode was not successful');
            }

        

        
            
        })};
    
        
    
        document.querySelector('#showAll').addEventListener('click',()=>{
                showAll(markers);
            });

        document.getElementById('address').onkeydown = function (e) {
            if (e.keyCode == 13) {
                document.getElementById('submit').click();
            }
        }
            
        

            
        }

    </script>




<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo($apiKey) ?>&callback=initMap"
    async defer></script>

    <script>
    var stuff = <?php print($json); ?>
    </script>
    <?php
    
    
    
    // print_r($data);
    return 'it works';
}





// ===================================================== // 
// ==================== ADMIN AREA ===================== // 
// ===================================================== // 

// Admin Menu

// page title
// menu title
// capability
// unique identifier slug       
// function name
// icon url

// Submenu 
// parent  slug
// page title
// menu title
// capability
// meu slug
// function
// position


$mpsIcon = plugin_dir_path( __FILE__ ) . 'img/mpsicon.png';
// function mps_maps_admin_menu(){
//     $mpsMenu = add_menu_page('Mepps Maps','Mepps Maps','manage_options','plugin-page','mps_admin_page');   
// }



function mps_admin_page(){

    

    // security precaution
    if(current_user_can('edit_users')){

    wp_enqueue_script( 'mps-main-script', plugins_url(). '/mepps-maps/js/admin.js');

    wp_enqueue_style( 'mps-main-style', plugins_url(). '/mepps-maps/css/style.css');

    // Load API key
    $apiKey = file_get_contents(plugin_dir_path( __FILE__ ) . '/data/key.txt');
        

        
    ?>

        <style>
        #editStores{
            display: inline-flex;
        }

        form{
            margin: 15px;
        }
        
        h3{
            text-align:center;
        }

        table{
            background: #f4f4f4;
        }

        table,section{
            width: 90%;
            margin: auto;
        }

        tr{
            border: .5px solid black;
        }
        section{
            margin-left: 100px;
        }

        td{
            text-align: center;
            color: #000;
            padding: 8px;
        }

        button.remove{
            background: #ff0022;
            color: #fff;
            height: 30px;
            width: 30px;
            border-radius: 6px;
            border: 0;
        }

        button.remove:hover{
            background: #000;
            color: #fff;
        }

        tr:nth-child(even){
            background: #bababa;
        }

        #submitRm{
            background-color: #ff0022;
            color: #fff;
            border: 0;
        }

        #newStore{
            background-color: #2ce6a8;
            color: #fff;
            border: 0;
        }

        #keyBtn,#showKey{
            background: #0ecaf9;
            color: #fff;
            border: 0;
            margin-top: 5px;
        }


        </style>

        <section id="editStores">

            <div style="width:500px">
            <h3>Add a store</h3>
            <form action="" method="POST">

            <label for="lat">Lat</label>
                <input class="widefat" name="lat" id="lat" type="text">

            <label for="lng">Lng</label>
                <input class="widefat" name="lng" id="lng" type="text">

            <label for="category">Category</label>
                <input class="widefat" name="category" id="category" type="text">

            <label for="addStore">Store</label>
                <input class="widefat" name="addStore" id="addStore" type="text">

            <label for="phone">Phone</label>
                <input class="widefat" name="phone" id="phone" type="text">

            <label for="address">Address</label>
                <input class="widefat" name="address" id="address" type="text">

            <label for="storeid">Store ID</label>
                <input class="widefat" name="storeid" id="storeid" type="text">

            <button type="submit" class="button submit" style="margin-top:5px" id="newStore">Add Store</button>
            </form>
                
            </div>

            <div id="testingArea" style="width:500px">
            <h3>Remove a store</h3>
                <form action="" method="POST">
                    <label for="removeName">Store</label>
                    <input class="widefat" type="text" name="removeName" id="storeName" value="">
                    <label for="lat">Lat</label>
                    <input class="widefat" type="text" id="removeLat" name="lat" value="">
                    <label for="lng">Lng</label>
                    <input class="widefat" type="text" id="removeLng" name="lng" value="">
                    <label for="add">Address</label>
                    <input class="widefat" type="text" id="removeAdd" name="add" value="">

                    <button type="submit" class="button submit" value="submit" id="submitRm" style="margin-top:5px">Remove Store</button>
                </form>
            </div>

            <div id="apiConfig" style="width:400px">
            <h3>Add your Google Maps API Key</h3>
                <form action="" method="POST">
                    <input type="password" name="key" class="widefat" id="key" value="<?php echo esc_attr( $apiKey ) ?>"> 
                    <button type="submit" class="button submit" id="keyBtn" value="submit">Add Key</button>
                    <button type="" class="button" id="showKey" value="">Show</button>
                </form>
            <h4><strong>Notice: </strong>Google maps requires an API key to operate. As a safety precaution you should restrict your API key.<br>
            <span><a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blanks">Get Your API Key</a></span><br>
            <span><a href="https://cloud.google.com/blog/products/maps-platform/google-maps-platform-best-practices-restricting-api-keys" target="_blank">Learn to restrict your key<a></span></h4>
            
            </div>
        </section>
        



      <?php

    //   echo $key;
      


      
        // Load JSON file
        $data = file_get_contents(plugin_dir_path( __FILE__ ) . '/data.json');
        $data = stripslashes($data);


        
        // Check for JSON err's 
        $json = json_decode($data,true);
        if (json_last_error()) {
            die('Invalid JSON provided!');
        }
        if(json_last_error()){
            echo'<h1>JSON ERR</h1>';
            print_r(json_last_error());
        }
        



        if(isset($_POST)){


            // ================================ // 

            // Add a store
            if(isset($_POST['addStore'])){

                $randIndex = 1;

                $store = $_POST['addStore'];
                $cat = $_POST['category'];
                $lat = floatval($_POST['lat']);
                $lng = floatval($_POST['lng']);
                $add = $_POST['address'];
                $storeID = $_POST['storeid'];
                $phone = $_POST['phone'];

                print_r($store);
                print_r($cat);
                print_r($lat);
                print_r($lng);
                print_r($add);
                print_r($storeID);

                echo'<br>';

                // Create geojson obj
                $insert = array(
                    'geometry' => array( 
                        'type'=>'Point',
                        'coordinates' => array(
                            $lng,
                            $lat
                        )
                    ),
                    'type' => 'Feature',
                    'properties' => array(
                        'category' => $cat,
                        'name' => $store,
                        'phone' => $phone,
                        'address' => $add,
                        'storeid' => $storeID
                    )   
                    );
        
               
                array_push($json['features'], $insert);
            
            
                $updatedCont = json_encode($json, JSON_PRETTY_PRINT);
               
                file_put_contents(plugin_dir_path( __FILE__ ) . '/data.json', $updatedCont);

                $output = '<table>';
                $output .= '<tr><th>Lng</th><th>Lat</th><th>Category</th><th>Name</th><th>Phone</th><th>Address</th><th>StoreID</th><th>Remove</th><tr><tbody>';
                
                foreach($json['features'] as $store){
            
                    $output .= '<tr>';
                    $output .= '<td class="lng">'.$store['geometry']['coordinates'][0].'</td>';
                    $output .= '<td class="lat">'.$store['geometry']['coordinates'][1].'</td>';
                    $output .= '<td class="cat">'.$store['properties']['category'].'</td>';
                    $output .= '<td class="name">'.$store['properties']['name'].'</td>';
                    $output .= '<td class="phone">'.$store['properties']['phone'].'</td>';
                    $output .= '<td class="address">'.$store['properties']['address'].'</td>';
                    $output .= '<td class="storeid">'.$store['properties']['storeid'].'</td>';
                    $output .= '<td class="remove"><button class="remove">X</button></td>';
                    $output .= '</tr>';
            
                }
                $output .= '</tbody></table>';
                echo $output;

                return;
                

            
            }


            // ================================ // 
            
     
            // Remove a store
            if(isset($_POST['removeName'])){
               
                $store = $_POST['removeName'];
                $lat = floatval($_POST['lat']);
                $lng = floatval($_POST['lng']);
                $add = $_POST['add'];
               
 
          
                // Loop thru json
                foreach($json['features'] as $key => $feature){
                 
                  
                    $jsonLng = $feature['geometry']['coordinates'][0]; 
                    $jsonLat = $feature['geometry']['coordinates'][1];
                    $selLat = $lat;
                    $selLng = $lng;
                    

                    // If match, delete and update JSON file
                    if(trim($jsonLat) == trim($selLat) && trim($jsonLng) == trim($selLng)){

                        // print_r($json['features'][$key]);
                        echo'<br><br><br>';

                        unset($json['features'][$key]);
                        $json['features'] = array_values($json['features']);
                        echo('success');

                        echo'<br>';

                        // print_r(array_values($json));
                        $updatedCont = json_encode($json ,JSON_PRETTY_PRINT);
                        
                        file_put_contents(plugin_dir_path( __FILE__ ) . '/data.json', $updatedCont);
                        $output = '<table>';
                        $output .= '<tr><th>Lng</th><th>Lat</th><th>Category</th><th>Name</th><th>Phone</th><th>Address</th><th>StoreID</th><th>Remove</th><tr><tbody>';
                        
                        foreach($json['features'] as $store){
                    
                            $output .= '<tr>';
                            $output .= '<td class="lng">'.$store['geometry']['coordinates'][0].'</td>';
                            $output .= '<td class="lat">'.$store['geometry']['coordinates'][1].'</td>';
                            $output .= '<td class="cat">'.$store['properties']['category'].'</td>';
                            $output .= '<td class="name">'.$store['properties']['name'].'</td>';
                            $output .= '<td class="phone">'.$store['properties']['phone'].'</td>';
                            $output .= '<td class="address">'.$store['properties']['address'].'</td>';
                            $output .= '<td class="storeid">'.$store['properties']['storeid'].'</td>';
                            $output .= '<td class="remove"><button class="remove">X</button></td>';
                            $output .= '</tr>';
                    
                        }
                        $output .= '</tbody></table>';
                        echo $output;
                        return;

                    }

                }
               
            }             
            echo'<br><br>';
            
            // Change API key
            if(isset($_POST['key'])){
                $newKey = $_POST['key'];

                file_put_contents(plugin_dir_path( __FILE__ ) . '/data/key.txt',$newKey);
            }
        
        }

        $output = '<table>';
        $output .= '<tr><th>Lng</th><th>Lat</th><th>Category</th><th>Name</th><th>Phone</th><th>Address</th><th>StoreID</th><th>Remove</th><tr><tbody>';
        
        foreach($json['features'] as $store){
    
            $output .= '<tr>';
            $output .= '<td class="lng">'.$store['geometry']['coordinates'][0].'</td>';
            $output .= '<td class="lat">'.$store['geometry']['coordinates'][1].'</td>';
            $output .= '<td class="cat">'.$store['properties']['category'].'</td>';
            $output .= '<td class="name">'.$store['properties']['name'].'</td>';
            $output .= '<td class="phone">'.$store['properties']['phone'].'</td>';
            $output .= '<td class="address">'.$store['properties']['address'].'</td>';
            $output .= '<td class="storeid">'.$store['properties']['storeid'].'</td>';
            $output .= '<td class="remove"><button class="remove">X</button></td>';
            $output .= '</tr>';
    
        }
        $output .= '</tbody></table>';
        echo $output;


        

  
}  }

add_shortcode('mpsmaps','mps_store_locator');

add_action('admin_menu', 'mps_maps_admin_menu');

function mps_maps_admin_menu(){
    $mpsMenu = add_menu_page('Mepps Maps','Mepps Maps','manage_options','mps-maps-admin','mps_admin_page');   
}

require_once(plugin_dir_path(__FILE__).'/includes/mps-maps-scripts.php');


?>

<div class="formArea">

</div>

<?



    // }  }
    


