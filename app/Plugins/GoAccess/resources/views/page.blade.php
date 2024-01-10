<div class="panel-heading">

   <ul class="nav nav-tabs"  style="margin-top: 10px;">
      <li class="">
         <a aria-disable="true""> <span style="font-size: 25px; font-weight: bold;"> {{ $plugin_name }} </span></a></li>

      <li class="active">
         <a aria-expanded="true"  data-toggle="tab"  href="#nginx_access"> Nginx Access</a></li>

      <li class="">
         <a aria-expanded="false" data-toggle="tab"  href="#nginx_error"> Nginx Error</a></li>

   </ul>


   <div class="panel with-nav-tabs panel-default">
         <div class="tab-content">


            <div class="tab-pane fade active in" id="nginx_access">
                  <iframe style="width:100%; height:100%" frameborder="0" src="{{ url('/GoAccess/nginx-access.html') }}">
                  </iframe>
            </div>

            <div class="tab-pane fade"          id="nginx_error">
                  <iframe style="width:100%; height:100%" frameborder="0" src="{{ url('/GoAccess/nginx-error.html') }}">
                  </iframe>
            </div>


         </div>
      </div>

</div>
