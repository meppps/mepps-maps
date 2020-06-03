
// Admin page script

    // Auto submit form on click
    document.addEventListener('DOMContentLoaded',()=>{

        // Init remover buttons
        document.querySelectorAll('button.remove').forEach((button)=>{
            
            button.addEventListener('click',()=>{
            var data = button.parentElement.parentElement.childNodes;
            var lng = data[0].innerText;
            var lat = data[1].innerText;
            var name = data[3].innerText;
            var add = data[5].innerText;
            console.log(lat,lng,name,add);

            document.getElementById('removeLat').value = lat;
            document.getElementById('removeLng').value = lng;
            document.getElementById('storeName').value = name;
            document.getElementById('removeAdd').value = add;

            var conf = confirm(`Are you sure you want to remove ${name}?`);
            if(conf){
            document.getElementById('submitRm').click();
            };
        });

    });

    // New store form validation
    document.querySelector('button#newStore').addEventListener('click',(e)=>{
        var stop = 1;
        if(stop){
            // e.preventDefault();
        }
    })

    // API KEY
    document.querySelector('#showKey').addEventListener('click',(e)=>{
        e.preventDefault();
        if(e.target.innerText == 'Show'){
        document.querySelector('input#key').type = 'text';
        }
        else{
            document.querySelector('input#key').type = 'password';
        };
        if(document.querySelector('input#key').type == 'text'){
            e.target.innerText = 'Hide';
        }else{
            e.target.innerText = 'Show';
        }
        });

// setTimeout(() => {
//     document.querySelectorAll('button.remove')[5].click()
// }, 3000);
});