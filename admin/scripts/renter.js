
let add_image_form = document.getElementById('add_image_form');
add_image_form.addEventListener('submit',function(e){
    e.preventDefault();
    add_image();
});

function add_image()
{
  let data = new FormData();
  data.append('image',add_image_form.elements['image'].files[0]);
  data.append('renter_id',add_image_form.elements['renter_id'].value);
  data.append('add_image', '');

  let xhr = new XMLHttpRequest();
  xhr.open("POST","ajax/renters.php",true);

  //to view data in dashboard settings
  xhr.onload = function()
  {
      
      if(this.responseText == 'inv_img'){
          alert('error', 'Only JPG and PNG images are allowed!', 'image-alert');
      }else if(this.responseText == 'inv_size'){
          alert('error', 'Image should be less than 2MB!', 'image-alert');
      }else if(this.responseText == 'upd_failed'){
          alert('error', 'Image upload failed. Server down!', 'image-alert');
      }else{
          alert('success', 'New image added!','image-alert');
          renter_images(add_image_form.elements['renter_id'].value,document.querySelector("#renter-images .modal-title").innerText);//this code will add image automatically w/o refresh the page
          add_image_form.reset();
      }  
  }

  xhr.send(data);
}

function renter_images(id,rname)
{   
    document.querySelector("#renter-images .modal-title").innerText = rname;
    add_image_form.elements['renter_id'].value = id;
    add_image_form.elements['image'].value = '';//to prevent or refresh image if not added

    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/renters.php",true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');


    // //to view data in dashboard settings
    xhr.onload = function(){
        document.getElementById('renter-image-data').innerHTML = this.responseText;
    }

    xhr.send('get_renter_images='+id);
}

function rem_image(img_id,renter_id)
{
    let data = new FormData();
    data.append('image_id',img_id);
    data.append('renter_id',renter_id);
    data.append('rem_image', '');

    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/renters.php",true);

    //to view data in dashboard settings
    xhr.onload = function()
    {
        
        if(this.responseText == 1){
            alert('success', 'Image Removed!', 'image-alert');
            renter_images(renter_id,document.querySelector("#renter-images .modal-title").innerText);
        }else {
            alert('error', 'Image removal failed!','image-alert'); 
        }  
    }

    xhr.send(data);
}

function thumb_image(img_id,renter_id)
{
    let data = new FormData();
    data.append('image_id',img_id);
    data.append('renter_id',renter_id);
    data.append('thumb_image', '');

    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/renters.php",true);

    //to view data in dashboard settings
    xhr.onload = function()
    {
        
        if(this.responseText == 1){
            alert('success', 'Thumbnail Updated!', 'image-alert');
            renter_images(renter_id,document.querySelector("#renter-images .modal-title").innerText);
        }else {
            alert('error', 'Thumbnail removal failed!','image-alert'); 
        }  
    }

    xhr.send(data);
}

// function remove_renter(renter_id)
// {
//     if(confirm("Are you sure, you want to delete this renter?"))
//     {
//         let data = new FormData();
//         data.append('renter_id',renter_id);
//         data.append('remove_renter', '');
    
//         let xhr = new XMLHttpRequest();
//         xhr.open("POST","ajax/units.php",true);

//         //to view data in dashboard settings
//         xhr.onload = function()
//         {
//             if(this.responseText == 1){
//                 alert('success', 'Renter Deleted');
//                 get_all_unit();
//             }
//             else{
//                 alert('error', 'Failed to delete Renter!'); 
//             }  
//         }
//         xhr.send(data);
//     }

// }

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