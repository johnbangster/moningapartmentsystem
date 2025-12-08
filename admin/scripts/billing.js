

function get_all_bills()
{
    let xhr = new XMLHttpRequest();
    xhr.open("POST","ajax/bill.php",true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');


    // //to view data in dashboard settings
    xhr.onload = function(){
        document.getElementById('billing-data').innerHTML = this.responseText;
    }

    xhr.send('get_all_bills');
}

function toggleStatus(id, currentStatus)
{

    let newStatus = (currentStatus == 0) ? 'paid' : 'unpaid';

    // if(confirm("Change status to " + newStatus.toUpperCase() + "?")){
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/bill.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function(){
            if(this.responseText.trim() == '1'){
                alert('success', 'Status update successfully');
                get_all_bills();
            } else {
                alert('error','Failed to update status.');
            }
        }
        xhr.send("toggleStatus=" + id + "&value=" + newStatus);
    // }
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

//  function toggleStatus(id,val)
//     {
//         let xhr = new XMLHttpRequest();
//         xhr.open("POST","ajax/bill.php",true);
//         xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');


//         // //to view data in dashboard settings
//         xhr.onload = function(){
//             if(this.responseText==1){
//                 alert('success', 'Status toggled!');
//                 get_all_unit();
//             }
//             else{
//                 alert('error', 'Server Down!');
//             }
//         }

//         xhr.send('toggleStatus='+id+'&value='+val);
//     }



    window.onload = function(){
    get_all_bills();
}