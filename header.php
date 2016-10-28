      <header class="main-header">
        <!-- Logo -->
        <a href="" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <img src="images/eco_log.png" width="100" height="50" alt=""/>
          <span class="logo-mini"><b>Eco Pre </b> Pay</span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b><i class="fa fa-home"></i>  Eco Pre Pay</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-fixed-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="dist/img/avatar3.png" class="user-image" alt="User Image"/>
                  <span class="hidden-xs"><?php $api->getSessionRole();?></span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="dist/img/avatar3.png" class="img-circle" alt="<?php $api->getSessionRole();?>" />
                    <p>
                      <?php $api->getSessionRole();?>
                      
                    </p>
                  </li>
                  <!-- Menu Body -->
                
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <a data-toggle="modal" href="#profile" class="btn btn-default btn-flat">Profile</a>
                    </div>
                    <div class="pull-right">
                      <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>

                </ul>
              </li>
             
            </ul>
          </div>
        </nav>


      </header>