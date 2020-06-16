
// Admin page script

    // TODO:  Form validation
    // function validForm(form){

    //     var valid = true;
    //     var children = form.children;

    //     for(i=0;i<children.length;i++){
    //         if(children[i].tagName == 'INPUT'){
    //             if(children[i].value == ''){
    //                 valid = false;
    //                 return;
    //             }
    //         }
    //     }

    //     return valid;
    // }



    // Remover buttons form submission
    document.addEventListener('DOMContentLoaded',()=>{

        function removeStore(button){
            var data = button.parentElement.parentElement.childNodes;
            var lng = data[0].innerText;
            var lat = data[1].innerText;
            var name = data[3].innerText;
            var add = data[5].innerText;
    
            document.getElementById('removeLat').value = lat;
            document.getElementById('removeLng').value = lng;
            document.getElementById('storeName').value = name;
            document.getElementById('removeAdd').value = add;
    
            var conf = confirm(`Are you sure you want to remove ${name}?`);
            if(conf){
            document.getElementById('submitRm').click(); 
             }
        }

        // Init remover buttons
        document.querySelectorAll('button.remove').forEach((button)=>{
            
            button.addEventListener('click', ()=>{
                removeStore(button)
            });

     });


    // ======== UPDATE STORES ========


    // Switch from edit to save button
    function switchEditSave(button){
        button.classList.remove('editStore');
        button.innerHTML = 'Save';
        button.classList.add('saveStore');
    }




    // Init edit/save button
    function initEditSave(button){
          
        // Edit store functionality
        if(button.classList.contains('editStore')){

            button.onclick = function editStoreData(){
                var data = button.parentElement.parentElement.childNodes;
                // Loop through and create inputs
                data.forEach((cell)=>{
                        var value = cell.innerText;
                        var className = cell.classList[0];
                        
                        if(! cell.classList.contains('remove') && ! cell.classList.contains('editStore') && ! cell.classList.contains('storeid')){
                            cell.innerHTML = `<input class="edit${className}" value="${value}"></input>`;
                            
                        }else if(cell.classList.contains('storeid')){
                            cell.classList.add('editstoreid');
                        }
                });

                // Convert to save btn
                switchEditSave(button);


                // Convert Remove to cancel btn
                var cancelButtonParent = button.parentElement.previousElementSibling;
                cancelButtonParent.innerHTML = `<button class="cancelEdit">Cancel</button>`;
                var cancelButton = cancelButtonParent.childNodes[0];
                
                // Cancel function
                cancelButton.onclick = function cancelEdit(){

                    var children = cancelButtonParent.parentElement.children;
                    
                    // Remove Inputs , put back edit button
                    for(i=0;i<children.length;i++){
                        if(children[i].innerHTML.includes('input')){
                            var value = children[i].childNodes[0].value;
                            children[i].innerHTML = `${value}`;
                        }else if(children[i].innerHTML.includes('button')){
                            if(children[i].childNodes[0].classList.contains('saveStore')){
                                var button = children[i].childNodes[0]; 
                                button.classList.remove('saveStore');
                                button.innerHTML = 'E';
                                button.classList.add('editStore');
                                initEditSave(button);
                            }
                        }
                    }
                    // Put back remove button
                    cancelButtonParent.innerHTML = '<button class="remove">X</button>';
                    cancelButtonParent.childNodes[0].addEventListener('click',()=>{
                        removeStore(cancelButtonParent.childNodes[0]);
                    })
                }
                // return;
            }

        }

        




        // Save changes functionality
        if(button.classList.contains('saveStore')){

            button.onclick = function saveStoreChanges(){
                
    
                var form = document.getElementById('editStoreForm');
                var validForm = true;
          
                var formList = [
                document.querySelector('input#editLat'),
                document.querySelector('input#editLng'),
                document.querySelector('input#editCat'),
                document.querySelector('input#editName'),
                document.querySelector('input#editPhone'),
                document.querySelector('input#editAddress'),
                document.querySelector('input#editStoreId')
                ];
                
                // Matching class list to ID's ex: .editStore #editStore
                formList.forEach((input)=>{
                    // Edit values
                    var element = document.querySelector(`.${input.id.toLowerCase()}`);
                    var editValue = element.value || element.innerText;
                    input.value = editValue;
    
                    // Form and Data validation
                    input.value = input.value.trim();
    
                  
                    if(input.value == ''){
                        validForm = false;
                        return;
                    }
                  
                });
    
                validForm ? form.submit():
                alert('You cannot leave empty fields!');
                return;

              };


        }
     

    }



      // Init edit buttons
      document.querySelectorAll('button.editStore').forEach((button)=>{
            initEditSave(button);
            button.addEventListener('click',()=>{
                initEditSave(button);
            })
            
        });

    // New store form validation
    document.querySelector('button#newStore').addEventListener('click',(e)=>{
        var stop = 1;
        function validStoreId(){
            var ids = [];
            document.querySelectorAll('td.storeid').forEach((store)=>{
                var storeid = store.innerText.trim();
                ids.push(storeid);
            });
            var newID = document.querySelector('input#storeid').value.trim()
            return ! ids.includes(newID);
        }
        if(! validStoreId()){
            e.preventDefault();
            alert('You must enter a unique store id!');
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

    // Auto generate store id when adding store
    if(document.querySelector('td.storeid')){

        function generateId(){

            var storeids = [];
            document.querySelectorAll('td.storeid').forEach((id)=>{
                storeids.push(id.innerText);
            });

            storeInts = storeids.map(x => parseInt(x));
            storeIntMax = Math.max(...storeInts);
            console.log(storeIntMax);

            // No nums
            if(isNaN(storeIntMax)){
                return '';
            }

            // Generate suggested id
            if(storeids[0].startsWith('0')){
                // 0123
                return `0${storeIntMax + 1}`;

            }else{
                // 123
                return `${storeIntMax + 1}`;
            }

        }

        // Add id unless user specifies otherwise
        var changedId = false;

        document.querySelector('input#storeid').addEventListener('input',()=>{
            changedId = true;
        });

        document.getElementById('addStoreForm').addEventListener('input',()=>{
            var suggestedID = generateId();

            if(document.querySelector('input#storeid').value == '' && changedId == false){
                document.querySelector('input#storeid').value = suggestedID;
            }else{
                return; 
            }
        
        });
    }else{
        console.log('No Stores Yet');
    }


});