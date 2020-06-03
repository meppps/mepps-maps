
// Load JSON
const points = [];
const categories = [];
function loadJSON(callback) {   
    var xobj = new XMLHttpRequest();
    xobj.overrideMimeType("application/json");
    xobj.open('GET', 'list.json', true);
    xobj.onreadystatechange = function () {
      if (xobj.readyState == 4 && xobj.status == "200") {
        callback(JSON.parse(xobj.responseText));
      }
    };
    xobj.send(null);  
  }

  loadJSON(function(json) {
    console.log(json); // this will log out the json object

    points.push(json);

    points[0].features.forEach((feature)=>{
      // console.log(feature.properties.category)
      var cat = feature.properties.category;
      if(! categories.includes(cat)){
        categories.push(cat);
      }
    });
    
  });
