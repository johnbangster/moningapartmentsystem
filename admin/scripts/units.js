    
    // let add_unit_form = document.getElementById('add_unit_form');

    // add_unit_form.addEventListener('submit', function(e){
    //     e.preventDefault();
    //     add_unit();
    // });

    // function add_unit()
    // {   
    //     let data = new FormData();
        
    //     data.append('add_unit','');
    //     data.append('name', add_unit_form.elements['name'].value);
    //     data.append('area', add_unit_form.elements['area'].value);
    //     data.append('price', add_unit_form.elements['price'].value);
    //     data.append('adult', add_unit_form.elements['adult'].value);
    //     data.append('children', add_unit_form.elements['children'].value);
    //     data.append('desc', add_unit_form.elements['desc'].value);
    //     data.append('unit_type_id', add_unit_form.elements['unit_type_id'].value);

    //     // collect features checkboxes
    //     let features = [];
    //     add_unit_form.querySelectorAll("input[name='features[]']").forEach(el => {
    //         if (el.checked) {
    //             features.push(el.value);
    //         }
    //     });

    //     //Collect facilities checkboxes
    //     let facilities = [];
    //     add_unit_form.querySelectorAll("input[name='facilities[]']").forEach(el => {
    //         if (el.checked) {
    //             facilities.push(el.value);
    //         }
    //     });

    //     data.append('features', JSON.stringify(features));
    //     data.append('facilities', JSON.stringify(facilities));

    //     let xhr = new XMLHttpRequest();
    //     xhr.open("POST", "ajax/units.php", true);

    //     xhr.onload = function(){
    //         var myModal = document.getElementById('add-unit');
    //         var modal = bootstrap.Modal.getInstance(myModal);
    //         modal.hide();

    //         if(this.responseText == 1){
    //             alert('success','Unit added successfully');
    //             add_unit_form.reset();  
    //         } else {
    //             alert('error','Failed to add unit');
    //         }
    //     }
    //     xhr.send(data);
    // }

    function get_all_unit()
    {
        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/units.php",true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');


        // //to view data in dashboard settings
        xhr.onload = function(){
            document.getElementById('unit-data').innerHTML = this.responseText;
        }

        xhr.send('get_all_unit');
    }

    let edit_unit_form = document.getElementById('edit_unit_form');

    function edit_details(id)
    {
        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/units.php",true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');


        // //to view data in dashboard settings
        xhr.onload = function(){
            let data = JSON.parse(this.responseText);
            
            edit_unit_form.elements['name'].value = data.unitData.name;
            edit_unit_form.elements['area'].value = data.unitData.area;
            edit_unit_form.elements['price'].value = data.unitData.price;
            // edit_unit_form.elements['qty'].value = data.unitData.qty;
            edit_unit_form.elements['adult'].value = data.unitData.adult;
            edit_unit_form.elements['children'].value = data.unitData.children;
            // edit_unit_form.elements['address'].value = data.unitData.address;
            edit_unit_form.elements['desc'].value = data.unitData.description;
            // edit_unit_form.elements['status'].value = data.unitData.status;
            edit_unit_form.elements['unit_id'].value = data.unitData.id;

            edit_unit_form.elements['features'].forEach(el =>{
                if(data.features.includes(Number(el.value))){
                    el.checked = true;
                }
            });

            edit_unit_form.elements['facilities'].forEach(el =>{
                if(data.facilities.includes(Number(el.value))){
                    el.checked = true;
                }
            });

        }

        xhr.send('get_unit='+id);
    }

    edit_unit_form.addEventListener('submit', function(e){
        e.preventDefault();
        submit_edit_unit();
    });

    function submit_edit_unit()
    {
        let data = new FormData();
        data.append('edit_unit','');
        data.append('unit_id',edit_unit_form.elements['unit_id'].value);
        data.append('name',edit_unit_form.elements['name'].value);
        data.append('area',edit_unit_form.elements['area'].value);
        data.append('price',edit_unit_form.elements['price'].value);
        // data.append('qty',edit_unit_form.elements['qty'].value);
        data.append('adult',edit_unit_form.elements['adult'].value);
        data.append('children',edit_unit_form.elements['children'].value);
        data.append('desc',edit_unit_form.elements['desc'].value);


        let features = [];
        edit_unit_form.elements['features'].forEach(el =>{
            if(el.checked){
              features.push(el.value);
            }
        });

        let facilities = [];
        edit_unit_form.elements['facilities'].forEach(el =>{
            if(el.checked){
              facilities.push(el.value);
            }
        });

        data.append('features', JSON.stringify(features));
        data.append('facilities', JSON.stringify(facilities));

        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/units.php",true);

        // //to view data in dashboard settings
        xhr.onload = function(){
            var myModal = document.getElementById('edit-unit');
            var modal = bootstrap.Modal.getInstance(myModal);
            modal.hide();
        
            if(this.responseText == 1){
                alert('success', 'Unit data updated!');   
                edit_unit_form.reset();  
                get_all_unit();  
            }
            else{
                alert('error', 'Server down!');
            }
        }

        xhr.send(data);
    }

    function toggleStatus(id,val)
    {
        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/units.php",true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');


        // //to view data in dashboard settings
        xhr.onload = function(){
            if(this.responseText==1){
                alert('success', 'Status toggled!');
                get_all_unit();
            }
            else{
                alert('error', 'Server Down!');
            }
        }

        xhr.send('toggleStatus='+id+'&value='+val);
    }

    function alert(type,msg,position='body')
    {
        let bs_class = (type == "success") ? "alert-success" : "alert-danger";
        let element = document.createElement('div');
        element.innerHTML =`
            <div class="alert ${bs_class} alert-dismissible fade show" role="alert">
                <strong class="me-3">${msg}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        if(position=='body'){
            document.body.append(element);
            element.classList.add('custom-alert');//this alert will promp inside add image modal
        }else{
            document.getElementById(position).appendChild(element); //this alert will pop-up beside modal once tge images is added
        }
        setTimeout(remAlert,2000);
    }

    function remAlert()
    {
        document.getElementsByClassName('alert')[0].remove();
    }

    let add_image_form = document.getElementById('add_image_form');

    add_image_form.addEventListener('submit',function(e){
        e.preventDefault();
        add_image();
    });

    function add_image()
    {
        let data = new FormData();
        data.append('image',add_image_form.elements['image'].files[0]);
        data.append('unit_id',add_image_form.elements['unit_id'].value);
        data.append('add_image', '');

        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/units.php",true);

        //to view data in dashboard settings
        xhr.onload = function()
        {
            
            if(this.responseText == 'inv_img'){
                alert('error', 'Only JPG and PNG images are allowed!', 'image-alert');
            }else if(this.responseText == 'upd_failed'){
                alert('error', 'Image upload failed. Server down!', 'image-alert');
            }else if(this.responseText == 'inv_size'){
             alert('error', 'Image should be less than MB!', 'image-alert');
            }else{
                alert('success', 'New image added!','image-alert');
                unit_images(add_image_form.elements['unit_id'].value,document.querySelector("#unit-images .modal-title").innerText);//this code will add image automatically w/o refresh the page
                add_image_form.reset();
            }  
            
        }

        xhr.send(data);
    }

    function unit_images(id,rname)
    {   
        document.querySelector("#unit-images .modal-title").innerText = rname;
        add_image_form.elements['unit_id'].value = id;
        add_image_form.elements['image'].value = '';//to prevent or refresh image if not added

        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/units.php",true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');


        // //to view data in dashboard settings
        xhr.onload = function(){
            document.getElementById('unit-image-data').innerHTML = this.responseText;
        }

        xhr.send('get_unit_images='+id);
    }

    function rem_image(img_id,unit_id)
    {
        let data = new FormData();
        data.append('image_id',img_id);
        data.append('unit_id',unit_id);
        data.append('rem_image', '');

        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/units.php",true);

        //to view data in dashboard settings
        xhr.onload = function()
        {
            
            if(this.responseText == 1){
                alert('success', 'Image Removed!', 'image-alert');
                unit_images(unit_id,document.querySelector("#unit-images .modal-title").innerText);
            }else {
                alert('error', 'Image removal failed!','image-alert'); 
            }  
        }

        xhr.send(data);
    }

    function thumb_image(img_id,unit_id)
    {
        let data = new FormData();
        data.append('image_id',img_id);
        data.append('unit_id',unit_id);
        data.append('thumb_image', '');

        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/units.php",true);

        //to view data in dashboard settings
        xhr.onload = function()
        {
            
            if(this.responseText == 1){
                alert('success', 'Thumbnail Updated!', 'image-alert');
                unit_images(unit_id,document.querySelector("#unit-images .modal-title").innerText);
            }else {
                alert('error', 'Thumbnail removal failed!','image-alert'); 
            }  
        }

        xhr.send(data);
    }

   
    window.onload = function () {
        get_all_unit();
    };

