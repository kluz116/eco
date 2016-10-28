<aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- Sidebar user panel -->
          <div class="user-panel">
            <div class="pull-left image">
              <img src="dist/img/avatar3.png" class="img-circle" alt="User Image" />
            </div>
            <div class="pull-left info">
              <p><?php $api->getSessionRole();?></p>

              <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
          </div>
          <!-- search form -->
          <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
              <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
              </span>
            </div>
          </form>
          <!-- /.search form -->
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
            <li class="header text-center active"><i class="fa fa-dashboard"></i> <span>Dashboard</span></li>
            <li class="treeview">
              <a href="index.php">
                <i class="fa fa-home"></i>
                <span>Home</span>
                <i class="fa fa-angle-right pull-right"></i>
              </a>
            </li>
             <li class="treeview">
              <a href="#">
                <i class="fa fa-user "></i>
                <span>Clients</span>
                <i class="fa fa-angle-right pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li><a href="addCustomer.php"><i class="fa fa-angle-right"></i> Add Clients</a></li>
                <li><a href="clients.php"><i class="fa fa-angle-right"></i> Registered Clients</a></li>
              </ul>
            </li>
              <li class="treeview">
              <a href="#">
                <i class="fa fa-shopping-cart"></i>
                <span>Orders</span>
                <i class="fa fa-angle-right pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li><a href="addorder.php"><i class="fa fa-angle-right"></i> Add New Order</a></li>
                <li><a href="orders.php"><i class="fa fa-angle-right"></i> Orders</a></li>
              </ul>
            </li>
            <li class="treeview">
              <a href="#">
                <i class="fa fa-dollar "></i>
                <span>Payments</span>
                <i class="fa fa-angle-right pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li><a href="add_cash_payment.php"><i class="fa fa-angle-right"></i> Add Cash Payments</a></li>
                <li><a href="payments.php"><i class="fa fa-angle-right"></i> Payments</a></li>
              </ul>
            </li>
              <li class="treeview">
              <a href="viewInventory.php">
                <i class="fa fa-list"></i>
                <span>My Inventory</span>
                <i class="fa fa-angle-right pull-right"></i>
              </a>
              </li>
              <li class="treeview">
              <a href="items.php">
                <i class="fa fa-shopping-cart"></i>
                <span>Items</span>
                <i class="fa fa-angle-right pull-right"></i>
              </a>
              </li>
              
              <li class="treeview">
              <a href="system_users.php">
                <i class="fa fa-users"></i>
                <span>Users</span>
                <i class="fa fa-angle-right pull-right"></i>
              </a>
              </li>
              <li class="treeview">
              <a href="system_users.php">
                <i class="fa fa-users"></i>
                <span>Call Center</span>
                <i class="fa fa-angle-right pull-right"></i>
              </a>
              </li>
          </ul>  
        </section>
        <!-- /.sidebar -->
      </aside>
