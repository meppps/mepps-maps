# Mepps Maps # 

![](png/client.png)

## About 
Mepps Maps is a store locator plugin for Wordpress. Functionality allows for as many locations as needed. The plugin takes a client location and maps them out to the nearest store.

The plugin also includes options to add store info and filter by type. Since it saves coordinates in geojson format, the Google Maps API doesn't have to repeatedly geocode each store address.
This in turn, will help save costs on API calls for businesses with a high amount of locations or a high volume of site users. 

## Installation 
Just upload the zip file via the plugins page on Wordpress. That's it!

## Usage

To add the map to any page add the shortcode:
```bash
[mpsmaps]
```

To add locations you should use the administrator page on Wordpress. 

You will need to provide Latitude/Longitude coordinates for each location.
 
You can find these on Google Maps by searching a location and looking in the address bar.
Location data can now be easily updated using the editor on the admin page. 

![](png/admin.png)



## New in v 1.0.2
- Added update functionality to make changing store info easier
- Store ID's are now unique and cannot conflict with eachother
- Fixed bug conflict with WooCommerce
- Client side now displays distance for each store in miles
- Marker popups now close when a new one is opened
- Refactored code for performance

![](png/admin.gif)

## Other Notes
Avoid leaving any fields blank. Use 'n/a' instead.
