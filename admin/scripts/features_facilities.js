

    let feature_s_form = document.getElementById('feature_s_form');
    let facility_s_form = document.getElementById('facility_s_form');

    
    feature_s_form.addEventListener('submit', function(e){
        e.preventDefault();
        add_feature();
    });

    function alert(type,msg)
    {
        let bs_class = (type == "success") ? "alert-success" : "alert-danger";
        let element = document.createElement('div');
        element.innerHTML =`
            <div class="alert ${bs_class} alert-dismissible fade show custom-alert" role="alert">
            <strong class="me-3">${msg}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        document.body.append(element);
        setTimeout(remAlert,2000);
    }

    function remAlert()
    {
        document.getElementsByClassName('alert')[0].remove();
    }

    function add_feature()
    {
        let data = new FormData();
        data.append('name',feature_s_form.elements['feature_name'].value);
        data.append('add_feature', '');

        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/features_facilities.php",true);

        //to view data in dashboard settings
        xhr.onload = function(){
            var myModal = document.getElementById('feature-s');
            var modal = bootstrap.Modal.getInstance(myModal);
            modal.hide();
        
            if(this.responseText == 1)
            {
                alert('success', 'New feature added');     
                feature_s_form.elements['feature_name'].value='';                    
                get_features();
            }
            else{
                alert('error', 'Server down!');
            }
        }

        xhr.send(data);
    }

    function get_features()
    {
        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/features_facilities.php",true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        //to view data in dashboard settings
        xhr.onload = function(){
        document.getElementById('features-data').innerHTML = this.responseText;
        }

        xhr.send('get_features');
    }

    function rem_feature(val)
    {
        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/features_facilities.php",true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        //to view data in dashboard settings
        xhr.onload = function(){
            if(this.responseText==1){
                alert('success', 'Feature removed!');
                get_features();
            }
            else if(this.responseText == 'room_added'){
                alert('error', 'Feature is added in unit!');
            }
            else{
            alert('error', 'Server Down!');
            }
            
        }
        
        xhr.send('rem_feature='+val);
    }

    facility_s_form.addEventListener('submit', function(e){
        e.preventDefault();
        add_facility();
    });

    function add_facility()
    {
        let data = new FormData();
        data.append('name',facility_s_form.elements['facility_name'].value);
        data.append('icon',facility_s_form.elements['facility_icon'].files[0]);
        data.append('desc',facility_s_form.elements['facility_desc'].value); //check append
        data.append('add_facility', '');

        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/features_facilities.php",true);

        //to view data in dashboard settings
        xhr.onload = function(){
            var myModal = document.getElementById('facility-s');
            var modal = bootstrap.Modal.getInstance(myModal);
            modal.hide();
        
            if(this.responseText == 'inv_img'){
                alert('error', 'Only SVG images are allowed!');
            }
            else if(this.responseText == 'inv_size'){
                alert('error', 'Image should be less than 1MB!');
            }
            else if(this.responseText == 'upd_failed'){
                alert('error', 'Image upload failed. Server down!');
            }else{
                alert('success', 'New facility added!');
                facility_s_form.reset();
                get_facility();

            }
        }

        xhr.send(data);
    }

    function get_facility()
    {
        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/features_facilities.php",true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        //to view data in dashboard settings
        xhr.onload = function(){
        document.getElementById('facilities-data').innerHTML = this.responseText;
        }

        xhr.send('get_facility');
    }

    function rem_facility(val)
    {
        let xhr = new XMLHttpRequest();
        xhr.open("POST","ajax/features_facilities.php",true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        //to view data in dashboard settings
        xhr.onload = function(){
            if(this.responseText==1){
                alert('error', 'Facility removed!');
                get_facility();
            }else if(this.responseText == 'room_added'){
                alert('success', 'Facility is added in unit!');
            }
            else{
            alert('error', 'Server Down!');
            }
            
        }
        
        xhr.send('rem_facility='+val);
    }


    window.onload = function(){
        get_features();
        get_facility();
    }

